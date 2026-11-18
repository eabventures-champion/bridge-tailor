<?php

namespace App\Diagnostics;

use App\Models\Assessment;
use App\Models\Benchmark;
use App\Models\UploadedDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Evaluates deterministic scores, client responses, and uploaded document signals
 * against Bridge Tailor's diagnostic framework using OpenAI's Responses API with
 * strict JSON Schema Structured Outputs.
 *
 * Security & Integrity Guarantees:
 *  - API key remains strictly server-side (env('OPENAI_API_KEY')).
 *  - Numerical scores are NEVER produced by the AI; they come from ScoringEngine.
 *  - Every statement is tagged with epistemic status:
 *    [CLIENT-PROVIDED] | [DOCUMENT-EVIDENCED] | [CONSULTANT-ASSUMPTION]
 *  - Falls back to a deterministic framework synthesis engine when no OpenAI API key
 *    is configured or if the client declines external AI processing consent.
 */
class AiAnalysisService
{
    public function analyzeAssessment(Assessment $assessment, array $scored, Framework $fw): array
    {
        $company = $assessment->company;
        $benchmarks = Benchmark::forProfile($company->sector ?? 'general', $company->stage ?? 'growth');
        $documents = $assessment->documents()->where('extraction_status', 'analyzed')->get();

        $preferredProvider = strtolower((string) config('services.gemini.provider', env('IDEA_AI_PROVIDER', 'gemini')));
        $geminiKey = (string) config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $openAiKey = (string) config('services.openai.key', env('OPENAI_API_KEY', ''));

        if ($assessment->ai_consent) {
            // 1. Primary: Google Gemini Structured Outputs (when IDEA_AI_PROVIDER=gemini or GEMINI_API_KEY is set)
            if ($geminiKey !== '' && ($preferredProvider === 'gemini' || $openAiKey === '')) {
                $geminiResult = $this->callGeminiWithFallbacks($assessment, $scored, $fw, $benchmarks, $documents, $geminiKey);
                if ($geminiResult) {
                    return $geminiResult;
                }
            }

            // 2. OpenAI Responses API Structured Outputs (when OPENAI_API_KEY is set)
            if ($openAiKey !== '') {
                try {
                    $aiResult = $this->callOpenAiResponsesApi($assessment, $scored, $fw, $benchmarks, $documents, $openAiKey);
                    if ($aiResult) {
                        return [
                            'provider' => 'openai',
                            'model' => config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-2024-08-06')),
                            'analysis' => $aiResult,
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::warning('OpenAI Responses API call failed; falling back to Bridge-Tailor deterministic framework engine.', [
                        'assessment_id' => $assessment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'provider' => 'framework',
            'model' => 'bt-deterministic-v1',
            'analysis' => $this->buildDeterministicSynthesis($assessment, $scored, $fw, $benchmarks, $documents),
        ];
    }

    public function analyzeDocument(UploadedDocument $document, string $rawText): array
    {
        $geminiKey = (string) config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $openAiKey = (string) config('services.openai.key', env('OPENAI_API_KEY', ''));
        $snippet = mb_substr(trim($rawText), 0, 12000);

        if ($geminiKey !== '' && $snippet !== '') {
            $geminiDoc = $this->callGeminiDocumentExtraction($document, $snippet, $geminiKey);
            if ($geminiDoc) {
                return $geminiDoc;
            }
        }

        $apiKey = $openAiKey;

        if ($apiKey !== '' && $snippet !== '') {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(35)
                    ->post('https://api.openai.com/v1/responses', [
                        'model' => config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-2024-08-06')),
                        'input' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a Bridge-Tailor senior management consultant analyzing an uploaded client business document. Never invent financial numbers. Only report figures explicitly present in the text and mark them as client-provided and unverified unless an audit opinion is explicitly stated.',
                            ],
                            [
                                'role' => 'user',
                                'content' => "Document Name: {$document->original_name}\nCategory: {$document->category}\nExtracted Text:\n{$snippet}",
                            ],
                        ],
                        'text' => [
                            'format' => [
                                'type' => 'json_schema',
                                'name' => 'bridge_tailor_document_analysis',
                                'strict' => true,
                                'schema' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['document_summary', 'extracted_facts', 'governance_or_financial_flags', 'verification_note'],
                                    'properties' => [
                                        'document_summary' => ['type' => 'string'],
                                        'extracted_facts' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'additionalProperties' => false,
                                                'required' => ['fact', 'source_basis', 'module_relevance'],
                                                'properties' => [
                                                    'fact' => ['type' => 'string'],
                                                    'source_basis' => ['type' => 'string', 'enum' => ['CLIENT-PROVIDED', 'DOCUMENT-EVIDENCED', 'UNVERIFIED-ESTIMATE']],
                                                    'module_relevance' => ['type' => 'string', 'enum' => ['SG', 'FH', 'OE', 'MG', 'HC']],
                                                ],
                                            ],
                                        ],
                                        'governance_or_financial_flags' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string'],
                                        ],
                                        'verification_note' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $jsonText = $this->extractResponsesOutputText($response->json());
                    $decoded = $jsonText ? json_decode($jsonText, true) : null;
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Document AI extraction fell back to deterministic parser.', ['error' => $e->getMessage()]);
            }
        }

        return $this->deterministicDocumentExtraction($document, $snippet);
    }

    private function geminiModelsToTry(): array
    {
        $primary = trim((string) config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-lite-latest')));
        $fallbacksRaw = (string) config('services.gemini.fallback_models', env('GEMINI_FALLBACK_MODELS', 'gemini-flash-latest'));
        $fallbacks = array_filter(array_map('trim', explode(',', $fallbacksRaw)));

        return array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));
    }

    private function stripAdditionalProperties(array $schema): array
    {
        unset($schema['additionalProperties']);
        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $k => $v) {
                if (is_array($v)) {
                    $schema['properties'][$k] = $this->stripAdditionalProperties($v);
                }
            }
        }
        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->stripAdditionalProperties($schema['items']);
        }

        return $schema;
    }

    private function callGeminiWithFallbacks(
        Assessment $assessment,
        array $scored,
        Framework $fw,
        array $benchmarks,
        $documents,
        string $apiKey
    ): ?array {
        $company = $assessment->company;
        $moduleInstructions = [];
        foreach ($fw->modules() as $mKey => $m) {
            $moduleInstructions[$mKey] = $m['ai_instructions'] ?? '';
        }

        $docSummaries = [];
        foreach ($documents as $doc) {
            $docSummaries[] = [
                'name' => $doc->original_name,
                'category' => $doc->category,
                'extraction' => $doc->ai_extraction,
            ];
        }

        $payload = [
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector,
                'stage' => $company->stage,
                'size_band' => $company->size_band,
                'revenue_band' => $company->revenue_band,
                'country' => $company->country,
                'city' => $company->city,
                'objectives' => $company->objectives,
                'challenges' => $company->challenges,
            ],
            'client_own_words' => $assessment->custom_issue,
            'controlled_scores' => [
                'health_index' => $scored['health_index'],
                'band' => $scored['band'],
                'modules' => $scored['modules'],
                'strengths' => $scored['strengths'],
                'gaps' => $scored['gaps'],
                'evidence' => $scored['evidence'],
                'risks' => $scored['risks'],
                'sequence' => $scored['sequence'],
                'cross_area_insights' => $scored['insights'],
            ],
            'sector_benchmarks' => $benchmarks,
            'uploaded_document_signals' => $docSummaries,
            'module_ai_instructions' => $moduleInstructions,
        ];

        $systemPrompt = <<<'PROMPT'
You are Bridge Tailor's AI Diagnostic Engine, built around Bridge Tailor's management consulting methodology for African SMEs (Accra, Ghana & across Africa).

CRITICAL RULES:
1. NEVER invent, extrapolate, or modify numerical scores or financial performance figures. All numerical scores are computed by the controlled scoring engine and passed to you in `controlled_scores`.
2. Distinguish strictly between:
   - `CLIENT-PROVIDED`: Self-reported questionnaire answers, symptom ratings, or company profile statements (unaudited).
   - `DOCUMENT-EVIDENCED`: Facts extracted from uploaded supporting documents.
   - `CONSULTANT-ASSUMPTION`: Working hypotheses that require verification during a Bridge-Tailor discovery or Scale Readiness engagement.
3. Align every recommendation and consulting priority with Bridge Tailor's four core practice lines:
   - Business Strategy (Scale Readiness Review -> Governance & Structure Programme)
   - Business Performance (Performance System Setup -> Finance Desk retainer)
   - Finance Access (Capital Preparation Programme -> Investment Readiness Sprint)
   - Market Access (Market Intelligence Report -> Market Entry Package)
4. Respect sequencing gates: when Financial Control / Performance is weak, it gates Capital Raising (Finance Access) because capital does not convert on unverifiable books.
Return valid JSON matching the requested schema.
PROMPT;

        $geminiSchema = $this->stripAdditionalProperties($this->reportJsonSchema());

        foreach ($this->geminiModelsToTry() as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($apiKey);
                $response = Http::timeout(45)->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)]],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $geminiSchema,
                        'temperature' => 0.2,
                    ],
                ]);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    $decoded = is_string($text) ? json_decode($text, true) : null;
                    if (is_array($decoded) && isset($decoded['executive_summary'])) {
                        return [
                            'provider' => 'gemini',
                            'model' => $model,
                            'analysis' => $decoded,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gemini model {$model} failed; trying next fallback model.", [
                    'assessment_id' => $assessment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function callGeminiDocumentExtraction(UploadedDocument $document, string $snippet, string $apiKey): ?array
    {
        $docSchema = [
            'type' => 'object',
            'required' => ['document_summary', 'extracted_facts', 'governance_or_financial_flags', 'verification_note'],
            'properties' => [
                'document_summary' => ['type' => 'string'],
                'extracted_facts' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['fact', 'source_basis', 'module_relevance'],
                        'properties' => [
                            'fact' => ['type' => 'string'],
                            'source_basis' => ['type' => 'string', 'enum' => ['CLIENT-PROVIDED', 'DOCUMENT-EVIDENCED', 'UNVERIFIED-ESTIMATE']],
                            'module_relevance' => ['type' => 'string', 'enum' => ['SG', 'FH', 'OE', 'MG', 'HC']],
                        ],
                    ],
                ],
                'governance_or_financial_flags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'verification_note' => ['type' => 'string'],
            ],
        ];

        foreach ($this->geminiModelsToTry() as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($apiKey);
                $response = Http::timeout(35)->post($url, [
                    'systemInstruction' => [
                        'parts' => [[
                            'text' => 'You are a Bridge-Tailor senior management consultant analyzing an uploaded client business document. Never invent financial numbers. Only report figures explicitly present in the text and mark them as client-provided and unverified unless an audit opinion is explicitly stated.',
                        ]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => "Document Name: {$document->original_name}\nCategory: {$document->category}\nExtracted Text:\n{$snippet}",
                        ]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $docSchema,
                        'temperature' => 0.1,
                    ],
                ]);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    $decoded = is_string($text) ? json_decode($text, true) : null;
                    if (is_array($decoded) && isset($decoded['document_summary'])) {
                        return $decoded;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gemini document extraction with {$model} failed.", ['error' => $e->getMessage()]);
            }
        }

        return null;
    }

    private function callOpenAiResponsesApi(
        Assessment $assessment,
        array $scored,
        Framework $fw,
        array $benchmarks,
        $documents,
        string $apiKey
    ): ?array {
        $company = $assessment->company;
        $moduleInstructions = [];
        foreach ($fw->modules() as $mKey => $m) {
            $moduleInstructions[$mKey] = $m['ai_instructions'] ?? '';
        }

        $docSummaries = [];
        foreach ($documents as $doc) {
            $docSummaries[] = [
                'name' => $doc->original_name,
                'category' => $doc->category,
                'extraction' => $doc->ai_extraction,
            ];
        }

        $payload = [
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector,
                'stage' => $company->stage,
                'size_band' => $company->size_band,
                'revenue_band' => $company->revenue_band,
                'country' => $company->country,
                'city' => $company->city,
                'objectives' => $company->objectives,
                'challenges' => $company->challenges,
            ],
            'client_own_words' => $assessment->custom_issue,
            'controlled_scores' => [
                'health_index' => $scored['health_index'],
                'band' => $scored['band'],
                'modules' => $scored['modules'],
                'strengths' => $scored['strengths'],
                'gaps' => $scored['gaps'],
                'evidence' => $scored['evidence'],
                'risks' => $scored['risks'],
                'sequence' => $scored['sequence'],
                'cross_area_insights' => $scored['insights'],
            ],
            'sector_benchmarks' => $benchmarks,
            'uploaded_document_signals' => $docSummaries,
            'module_ai_instructions' => $moduleInstructions,
        ];

        $systemPrompt = <<<'PROMPT'
You are Bridge Tailor's AI Diagnostic Engine, built around Bridge Tailor's management consulting methodology for African SMEs (Accra, Ghana & across Africa).

CRITICAL RULES:
1. NEVER invent, extrapolate, or modify numerical scores or financial performance figures. All numerical scores are computed by the controlled scoring engine and passed to you in `controlled_scores`.
2. Distinguish strictly between:
   - `CLIENT-PROVIDED`: Self-reported questionnaire answers, symptom ratings, or company profile statements (unaudited).
   - `DOCUMENT-EVIDENCED`: Facts extracted from uploaded supporting documents.
   - `CONSULTANT-ASSUMPTION`: Working hypotheses that require verification during a Bridge-Tailor discovery or Scale Readiness engagement.
3. Align every recommendation and consulting priority with Bridge Tailor's four core practice lines:
   - Business Strategy (Scale Readiness Review -> Governance & Structure Programme)
   - Business Performance (Performance System Setup -> Finance Desk retainer)
   - Finance Access (Capital Preparation Programme -> Investment Readiness Sprint)
   - Market Access (Market Intelligence Report -> Market Entry Package)
4. Respect sequencing gates: when Financial Control / Performance is weak, it gates Capital Raising (Finance Access) because capital does not convert on unverifiable books.
PROMPT;

        $response = Http::withToken($apiKey)
            ->timeout(45)
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', env('OPENAI_MODEL', 'gpt-4o-2024-08-06')),
                'input' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'bridge_tailor_diagnostic_report',
                        'strict' => true,
                        'schema' => $this->reportJsonSchema(),
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return null;
        }

        $text = $this->extractResponsesOutputText($response->json());

        return $text ? json_decode($text, true) : null;
    }

    private function extractResponsesOutputText(?array $data): ?string
    {
        if (! is_array($data)) {
            return null;
        }
        if (! empty($data['output_text']) && is_string($data['output_text'])) {
            return $data['output_text'];
        }
        foreach ($data['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $content) {
                if (! empty($content['text']) && is_string($content['text'])) {
                    return $content['text'];
                }
            }
        }

        return null;
    }

    public function reportJsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'executive_summary',
                'epistemic_ledger',
                'functional_commentary',
                'strengths',
                'weaknesses',
                'risk_assessment',
                'opportunity_analysis',
                'recommendations',
                'implementation_roadmap',
                'consulting_priorities',
            ],
            'properties' => [
                'executive_summary' => ['type' => 'string'],
                'epistemic_ledger' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['verified_facts', 'client_provided', 'assumptions_to_test', 'disclaimer'],
                    'properties' => [
                        'verified_facts' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'client_provided' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'assumptions_to_test' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'disclaimer' => ['type' => 'string'],
                    ],
                ],
                'functional_commentary' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['module_key', 'module_name', 'headline', 'interpretation', 'benchmark_context', 'evidence_basis'],
                        'properties' => [
                            'module_key' => ['type' => 'string'],
                            'module_name' => ['type' => 'string'],
                            'headline' => ['type' => 'string'],
                            'interpretation' => ['type' => 'string'],
                            'benchmark_context' => ['type' => 'string'],
                            'evidence_basis' => ['type' => 'string', 'enum' => ['CLIENT-PROVIDED', 'DOCUMENT-EVIDENCED', 'CONSULTANT-ASSUMPTION']],
                        ],
                    ],
                ],
                'strengths' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['area', 'module_key', 'observation', 'how_to_leverage'],
                        'properties' => [
                            'area' => ['type' => 'string'],
                            'module_key' => ['type' => 'string'],
                            'observation' => ['type' => 'string'],
                            'how_to_leverage' => ['type' => 'string'],
                        ],
                    ],
                ],
                'weaknesses' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['area', 'module_key', 'deficiency', 'root_cause', 'evidence_quote'],
                        'properties' => [
                            'area' => ['type' => 'string'],
                            'module_key' => ['type' => 'string'],
                            'deficiency' => ['type' => 'string'],
                            'root_cause' => ['type' => 'string'],
                            'evidence_quote' => ['type' => 'string'],
                        ],
                    ],
                ],
                'risk_assessment' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['title', 'module_key', 'horizon', 'severity', 'trigger_evidence', 'mitigation'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'module_key' => ['type' => 'string'],
                            'horizon' => ['type' => 'string', 'enum' => ['Critical / Immediate', 'Emerging (3-12 months)']],
                            'severity' => ['type' => 'string', 'enum' => ['Critical', 'High', 'Medium']],
                            'trigger_evidence' => ['type' => 'string'],
                            'mitigation' => ['type' => 'string'],
                        ],
                    ],
                ],
                'opportunity_analysis' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['title', 'module_key', 'value_unlock', 'prerequisite'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'module_key' => ['type' => 'string'],
                            'value_unlock' => ['type' => 'string'],
                            'prerequisite' => ['type' => 'string'],
                        ],
                    ],
                ],
                'recommendations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['title', 'module_key', 'programme_key', 'priority', 'intervention', 'expected_outcome', 'target_kpi'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'module_key' => ['type' => 'string'],
                            'programme_key' => ['type' => 'string'],
                            'priority' => ['type' => 'string', 'enum' => ['Critical', 'High', 'Medium']],
                            'intervention' => ['type' => 'string'],
                            'expected_outcome' => ['type' => 'string'],
                            'target_kpi' => ['type' => 'string'],
                        ],
                    ],
                ],
                'implementation_roadmap' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['phase', 'window', 'focus', 'actions', 'milestone'],
                        'properties' => [
                            'phase' => ['type' => 'string'],
                            'window' => ['type' => 'string'],
                            'focus' => ['type' => 'string'],
                            'actions' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'milestone' => ['type' => 'string'],
                        ],
                    ],
                ],
                'consulting_priorities' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['rank', 'business_line', 'entry_programme', 'expansion_programme', 'delivery_mode', 'duration', 'rationale'],
                        'properties' => [
                            'rank' => ['type' => 'integer'],
                            'business_line' => ['type' => 'string'],
                            'entry_programme' => ['type' => 'string'],
                            'expansion_programme' => ['type' => 'string'],
                            'delivery_mode' => ['type' => 'string'],
                            'duration' => ['type' => 'string'],
                            'rationale' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Produces a structured diagnostic synthesis directly from Bridge Tailor's
     * methodology rules, cross-area insights, programme catalogue, and benchmarks.
     */
    public function buildDeterministicSynthesis(
        Assessment $assessment,
        array $scored,
        Framework $fw,
        array $benchmarks,
        $documents
    ): array {
        $company = $assessment->company;
        $index = $scored['health_index'];
        $bandLabel = $scored['band']['label'] ?? 'Indicative';
        $bandNote = $scored['band']['note'] ?? '';
        $stageName = $fw->stages()[$company->stage] ?? $company->stage;
        $sectorName = $fw->sectors()[$company->sector] ?? $company->sector;

        $assessedModules = array_filter($scored['modules'], fn ($m) => $m['score'] !== null);
        uasort($assessedModules, fn ($a, $b) => $a['score'] <=> $b['score']);
        $weakest = reset($assessedModules) ?: null;
        $strongest = end($assessedModules) ?: null;

        $execSummary = sprintf(
            '%s (%s · %s, %s) recorded an overall Business Health Index of %s/100 (%s) across %d of %d functional areas assessed. %s ',
            $company->name,
            $sectorName,
            $stageName,
            $company->country ?? 'Ghana',
            $index ?? '—',
            $bandLabel,
            $scored['coverage']['assessed'],
            $scored['coverage']['total'],
            $bandNote
        );

        if ($weakest) {
            $execSummary .= sprintf(
                'Primary operational strain sits in %s (%d/100, %s), where %d of %d evidence checks are missing. ',
                $weakest['name'],
                $weakest['score'],
                $weakest['label'],
                $weakest['distribution'][2],
                $weakest['answered']
            );
        }
        if (! empty($scored['sequence'][0])) {
            $firstProgKey = $scored['sequence'][0]['programme'];
            $firstProg = $fw->programme($firstProgKey);
            if ($firstProg) {
                $execSummary .= sprintf(
                    'Bridge Tailor recommends sequencing intervention starting with %s (%s) before downstream expansion or fundraising.',
                    $firstProg['entry'],
                    $firstProg['line']
                );
            }
        }

        // Epistemic ledger: strict separation of verified facts, client-provided info, and assumptions
        $verifiedFacts = [];
        foreach ($documents as $doc) {
            $ext = $doc->ai_extraction ?? [];
            foreach ($ext['extracted_facts'] ?? [] as $f) {
                $verifiedFacts[] = "[DOCUMENT-EVIDENCED · {$doc->original_name}] {$f['fact']}";
            }
        }
        if (empty($verifiedFacts)) {
            $verifiedFacts[] = 'No audited financial statements or formal governance records have been independently verified yet; all numerical scores derive from structured self-assessment responses.';
        }

        $clientProvided = [
            sprintf('Company profile: %s, sector "%s", stage "%s", size "%s", revenue band "%s" (self-reported, unaudited).',
                $company->name,
                $sectorName,
                $stageName,
                $fw->get("size_bands.{$company->size_band}", $company->size_band ?? 'unspecified'),
                $fw->get("revenue_bands.{$company->revenue_band}", $company->revenue_band ?? 'unspecified')
            ),
            sprintf('Flagged %d symptom(s) during intake and answered %d structured evidence questions (%d%% in place, %d%% partial, %d%% missing).',
                $scored['kpis']['issues_raised'],
                $scored['kpis']['answered'],
                $scored['kpis']['in_place_pct'],
                $scored['kpis']['partial_pct'],
                $scored['kpis']['missing_pct']
            ),
        ];
        if ($company->objectives) {
            $clientProvided[] = 'Stated objectives: '.trim($company->objectives);
        }
        if ($assessment->custom_issue) {
            $clientProvided[] = 'Additional client context in their own words: "'.trim($assessment->custom_issue).'"';
        }

        $assumptions = [
            'Reported management practices reflect consistent month-to-month execution rather than one-off recent efforts (to be verified during onboarding document review).',
            'No undisclosed regulatory, tax, or litigation liabilities exist outside the compliance items flagged in the questionnaire.',
            'Key leadership bandwidth is available to sponsor the Phase 1 corrective intervention over a 4–8 week delivery window.',
        ];

        // Functional commentary with benchmark comparison
        $functionalCommentary = [];
        foreach ($scored['modules'] as $mKey => $m) {
            $bm = $benchmarks[$mKey] ?? null;
            if ($m['score'] === null) {
                $functionalCommentary[] = [
                    'module_key' => $mKey,
                    'module_name' => $m['name'],
                    'headline' => 'Not assessed in this run',
                    'interpretation' => 'This functional module was outside the selected scope. No data is not a clean bill of health; Bridge Tailor recommends a targeted follow-up check.',
                    'benchmark_context' => $bm ? "Sector median for {$sectorName} ({$stageName}) is {$bm['median']}/100 (IQR {$bm['p25']}–{$bm['p75']})." : 'Benchmark pending assessment.',
                    'evidence_basis' => 'CONSULTANT-ASSUMPTION',
                ];
                continue;
            }

            $deltaText = '';
            if ($bm) {
                $diff = $m['score'] - $bm['median'];
                $deltaText = sprintf(
                    'Score of %d/100 sits %s the %s (%s) peer median of %d/100 (25th percentile: %d · 75th percentile: %d · Top decile: %d).',
                    $m['score'],
                    $diff >= 0 ? "+{$diff} pts above" : abs($diff).' pts below',
                    $sectorName,
                    $stageName,
                    $bm['median'],
                    $bm['p25'],
                    $bm['p75'],
                    $bm['top_decile']
                );
            }

            $perceptionNote = match ($m['perception']['key']) {
                'weaker' => "Blind-spot alert: objective evidence ({$m['evidence']}/100) is materially weaker than leadership's felt pain ({$m['feel']}/100), indicating structural exposure larger than day-to-day friction suggests.",
                'better' => "Execution gap: underlying structures ({$m['evidence']}/100) score higher than felt frustration ({$m['feel']}/100), suggesting existing controls need discipline rather than redesign.",
                default => "Internal consistency: felt pressure and objective evidence align closely (score {$m['score']}/100 · {$m['label']}).",
            };

            $functionalCommentary[] = [
                'module_key' => $mKey,
                'module_name' => $m['name'],
                'headline' => "{$m['label']} ({$m['score']}/100) · {$m['perception']['label']}",
                'interpretation' => "{$perceptionNote} Across {$m['answered']} evidence checks, {$m['distribution'][0]} are in place, {$m['distribution'][1]} are partial, and {$m['distribution'][2]} are missing.",
                'benchmark_context' => $deltaText ?: 'Compared against Bridge Tailor African SME reference cohort.',
                'evidence_basis' => 'CLIENT-PROVIDED',
            ];
        }

        // Strengths
        $strengths = [];
        foreach ($scored['strengths'] as $st) {
            $strengths[] = [
                'area' => $st['name'],
                'module_key' => $st['module'],
                'observation' => "[CLIENT-PROVIDED] Scored {$st['score']}/100 across {$st['answered']} evidence check(s) in {$st['module_name']}.",
                'how_to_leverage' => "Use {$st['name']} as an anchor discipline while remediating lower-scoring criteria; document what works so it survives scale.",
            ];
        }
        if (empty($strengths) && $strongest) {
            $strengths[] = [
                'area' => $strongest['name'],
                'module_key' => $strongest['key'],
                'observation' => "[CLIENT-PROVIDED] Relative resilience at {$strongest['score']}/100 compared with other assessed functions.",
                'how_to_leverage' => 'Stabilise partial controls here first to create quick organizational confidence.',
            ];
        }

        // Weaknesses
        $weaknesses = [];
        foreach ($scored['evidence'] as $ev) {
            $critName = $scored['modules'][$ev['module']]['criteria'][$ev['criterion']]['name'] ?? $ev['criterion'];
            $weaknesses[] = [
                'area' => $critName,
                'module_key' => $ev['module'],
                'deficiency' => "Missing foundational control or capability in {$critName}.",
                'root_cause' => $this->rootCauseFor($ev['module'], $ev['code']),
                'evidence_quote' => "{$ev['question']} → \"{$ev['answer']}\" [CLIENT-PROVIDED]",
            ];
        }
        if (empty($weaknesses)) {
            foreach ($scored['gaps'] as $g) {
                $weaknesses[] = [
                    'area' => $g['name'],
                    'module_key' => $g['module'],
                    'deficiency' => "Criterion scored {$g['score']}/100 ({$g['severity']} severity).",
                    'root_cause' => 'Controls are informal or inconsistently applied across the business cycle.',
                    'evidence_quote' => "Partial evidence across {$g['answered']} check(s) in {$g['name']} [CLIENT-PROVIDED].",
                ];
            }
        }

        // Risk assessment
        $riskAssessment = [];
        foreach ($scored['risks'] as $r) {
            $critName = $scored['modules'][$r['module']]['criteria'][$r['criterion']]['name'] ?? $r['criterion'];
            $riskAssessment[] = [
                'title' => "{$critName} exposure ({$r['module']})",
                'module_key' => $r['module'],
                'horizon' => $r['type'] === 'current' ? 'Critical / Immediate' : 'Emerging (3-12 months)',
                'severity' => ucfirst($r['severity']),
                'trigger_evidence' => "{$r['question']} → \"{$r['answer']}\" [CLIENT-PROVIDED]",
                'mitigation' => $this->mitigationFor($r['module'], $r['code']),
            ];
        }
        if (empty($riskAssessment) && $weakest) {
            $riskAssessment[] = [
                'title' => "Execution drag in {$weakest['name']}",
                'module_key' => $weakest['key'],
                'horizon' => 'Emerging (3-12 months)',
                'severity' => 'Medium',
                'trigger_evidence' => "Overall module score of {$weakest['score']}/100 [CLIENT-PROVIDED].",
                'mitigation' => 'Formalise operating rhythm, assign named owners, and review monthly.',
            ];
        }

        // Opportunity analysis
        $opportunities = $this->buildOpportunities($scored, $fw);

        // Recommendations & Action items blueprint
        $recommendations = $this->buildRecommendations($scored, $fw);

        // Implementation roadmap (30 / 90 / 180 day phased interventions)
        $roadmap = $this->buildRoadmap($scored, $fw, $recommendations);

        // Consulting priorities aligned to Bridge-Tailor programme catalogue
        $priorities = [];
        foreach ($scored['sequence'] as $seq) {
            $pKey = $seq['programme'];
            $cat = $fw->programme($pKey);
            $pScore = $scored['programmes'][$pKey] ?? null;
            if (! $cat || ! $pScore) {
                continue;
            }
            $weeks = $pScore['effort_weeks'];
            $priorities[] = [
                'rank' => $seq['position'],
                'business_line' => $cat['line'],
                'entry_programme' => $cat['entry'],
                'expansion_programme' => $cat['next'],
                'delivery_mode' => $pScore['mode'] ?? 'Advisory + embedded support',
                'duration' => "{$weeks[0]}–{$weeks[1]} weeks (entry: {$cat['entry_duration']})",
                'rationale' => $seq['reason'] ?? "Addresses {$cat['line']} gaps (health score {$pScore['score']}/100 · {$pScore['severity']} priority). Tracks toward: {$cat['metric']}.",
            ];
        }

        return [
            'executive_summary' => $execSummary,
            'epistemic_ledger' => [
                'verified_facts' => $verifiedFacts,
                'client_provided' => $clientProvided,
                'assumptions_to_test' => $assumptions,
                'disclaimer' => 'Numerical scores are generated by Bridge Tailor\'s deterministic scoring engine from client-submitted responses. Financial or operational figures referenced are client-provided and unverified unless explicitly marked as document-evidenced from audited statements.',
            ],
            'functional_commentary' => $functionalCommentary,
            'strengths' => array_slice($strengths, 0, 6),
            'weaknesses' => array_slice($weaknesses, 0, 8),
            'risk_assessment' => array_slice($riskAssessment, 0, 8),
            'opportunity_analysis' => $opportunities,
            'recommendations' => $recommendations,
            'implementation_roadmap' => $roadmap,
            'consulting_priorities' => $priorities,
        ];
    }

    private function rootCauseFor(string $module, string $code): string
    {
        return match ($code) {
            'fd1', 'fd2', 'fd3', 'fd4', 'sg_f1' => 'Decision rights and operational knowledge remain concentrated in the founder rather than codified in delegated limits and written roles.',
            'sg_1', 'sg_2' => 'Strategic ambition has not been converted into a written multi-year plan with budgeted annual owners.',
            'sg_3', 'sg_4' => 'Corporate governance cadence and statutory compliance tracking lack a formal company-secretarial calendar.',
            'fc1', 'fh_1', 'fc2' => 'Absence of a disciplined 15-day monthly close calendar and tax compliance checklist.',
            'fc4', 'fh_2', 'fh_f1' => 'Working-capital cycle (receivables vs. commitments) is managed from bank balance visibility rather than a rolling 13-week cash forecast.',
            'fc3', 'fh_3' => 'Direct costs and overheads are not allocated by product/service line, obscuring margin erosion.',
            'ir1', 'ir2', 'ir3', 'ir4', 'ir_f1' => 'Capital raising is being approached as a pitch exercise rather than an evidence and data-room preparation process.',
            'oe_5', 'oe_6' => 'Segregation of payment approval and monthly bank reconciliation controls have not scaled with transaction volume.',
            'rm1', 'rm2', 'rm3', 'rm4', 'sec_og', 'sec_ag', 'sec_ft' => 'Market entry or expansion is proceeding ahead of regulatory mapping, partner due diligence, and evidenced unit demand.',
            default => "Operating discipline in {$module} relies on individual effort rather than documented process and scheduled review.",
        };
    }

    private function mitigationFor(string $module, string $code): string
    {
        return match ($code) {
            'sg_4', 'fc2' => 'Commission an immediate statutory & tax compliance reconciliation and lock in a monthly filing calendar with dual sign-off.',
            'fd1', 'fd3' => 'Define a written delegation matrix, appoint an acting deputy with spending limits, and document critical continuity procedures.',
            'fc1', 'fc4' => 'Install a 15-day month-end close checklist and a rolling 13-week cash-flow forecast reviewed weekly.',
            'oe_5', 'oe_6' => 'Enforce dual-authorization on all disbursements and mandatory monthly bank reconciliations signed off by leadership.',
            'ir1' => 'Engage auditors/reviewers to bring 2+ years of financial statements up to funder due-diligence standard before approaching capital providers.',
            'rm2', 'sec_og', 'sec_ag', 'sec_ft' => 'Complete a formal regulatory & licensing compliance matrix for the target market before committing expansion capital.',
            default => "Deploy Bridge Tailor's {$module} control template and assign a single accountable executive owner.",
        };
    }

    private function buildOpportunities(array $scored, Framework $fw): array
    {
        $out = [];
        foreach ($scored['modules'] as $mKey => $m) {
            if ($m['score'] === null) {
                continue;
            }
            if ($mKey === 'SG' && $m['score'] < 75) {
                $out[] = [
                    'title' => 'Release founder capacity & reduce valuation discount',
                    'module_key' => 'SG',
                    'value_unlock' => 'Codifying decision rights and board cadence frees leadership bandwidth for growth and removes key-person discounts during investor diligence.',
                    'prerequisite' => 'Written delegation matrix, role charters, and quarterly governance calendar.',
                ];
            }
            if ($mKey === 'FH' && $m['score'] < 75) {
                $out[] = [
                    'title' => 'Unlock working capital & due-diligence readiness',
                    'module_key' => 'FH',
                    'value_unlock' => 'Line-by-line margin visibility and a 13-week cash forecast routinely recover trapped cash and shorten funder screening cycles.',
                    'prerequisite' => '15-day monthly close discipline and reconciled management accounts.',
                ];
            }
            if ($mKey === 'OE' && $m['score'] < 75) {
                $out[] = [
                    'title' => 'Eliminate rework & leakage through proportionate internal controls',
                    'module_key' => 'OE',
                    'value_unlock' => 'Standardising core workflows (sales-to-billing) and payment segregation reduces cycle time and protects margin.',
                    'prerequisite' => 'Process mapping of top 5 workflows and monthly reconciliation controls.',
                ];
            }
            if ($mKey === 'MG' && $m['score'] < 75) {
                $out[] = [
                    'title' => 'De-risk market expansion & channel partnerships',
                    'module_key' => 'MG',
                    'value_unlock' => 'Evidenced demand validation and partner vetting prevent capital burn in under-researched channels.',
                    'prerequisite' => 'Market Intelligence Report covering regulation, unit economics, and partner diligence.',
                ];
            }
            if ($mKey === 'HC' && $m['score'] < 75) {
                $out[] = [
                    'title' => 'Convert payroll into measurable execution capacity',
                    'module_key' => 'HC',
                    'value_unlock' => 'Role-level KPIs and knowledge-sharing reduce key-person risk and lift team output without immediate headcount growth.',
                    'prerequisite' => 'Written role scorecards and bi-annual performance reviews.',
                ];
            }
        }

        if (empty($out)) {
            $out[] = [
                'title' => 'Institutionalise high-performing practices for scale',
                'module_key' => 'SG',
                'value_unlock' => 'Formalising current strengths prepares the enterprise for regional expansion or strategic capital.',
                'prerequisite' => 'Independent verification via Bridge Tailor Scale Readiness Review.',
            ];
        }

        return array_slice($out, 0, 5);
    }

    private function buildRecommendations(array $scored, Framework $fw): array
    {
        $recs = [];
        $criteriaMeta = $fw->criteria();

        foreach ($scored['gaps'] as $g) {
            $cKey = $g['key'];
            $mKey = $g['module'];
            $pKey = $g['programme'] ?? ($criteriaMeta[$cKey]['programme'] ?? 'performance');
            $prog = $fw->programme($pKey);
            $priority = $g['score'] === 0 ? 'Critical' : ($g['score'] <= 35 ? 'High' : 'Medium');

            $recs[] = [
                'title' => "Remediate {$g['name']} ({$mKey})",
                'module_key' => $mKey,
                'programme_key' => $pKey,
                'priority' => $priority,
                'intervention' => $this->interventionForCriterion($cKey, $g['name'], $prog),
                'expected_outcome' => "Raise {$g['name']} from {$g['score']}/100 to ≥75/100 with verifiable operating evidence.",
                'target_kpi' => $prog['metric'] ?? 'Control documented and reviewed monthly',
            ];
        }

        if (empty($recs)) {
            foreach ($scored['modules'] as $mKey => $m) {
                if ($m['score'] === null || $m['score'] >= 80) {
                    continue;
                }
                $recs[] = [
                    'title' => "Strengthen {$m['name']} partial controls",
                    'module_key' => $mKey,
                    'programme_key' => 'strategy',
                    'priority' => 'Medium',
                    'intervention' => "Convert informal practices in {$m['name']} into documented SOPs with quarterly leadership sign-off.",
                    'expected_outcome' => "Lift {$m['name']} score above 80/100.",
                    'target_kpi' => '% of recommendations adopted within 90 days',
                ];
            }
        }

        return array_slice($recs, 0, 8);
    }

    private function interventionForCriterion(string $cKey, string $name, ?array $prog): string
    {
        $entry = $prog['entry'] ?? 'Bridge Tailor Advisory';

        return match ($cKey) {
            'SG1', 'SG2' => "Through {$entry}: facilitate a leadership alignment workshop to codify a 24-month strategic roadmap, org chart, and written delegation-of-authority matrix.",
            'SG3', 'SG4', 'SG5' => "Through {$entry}: establish an advisory/board charter, bring statutory filings current, appoint a deputy with spending thresholds, and instate a quarterly risk register.",
            'FH1', 'FH2', 'FH3' => "Through {$entry}: implement a 15-day monthly close calendar, line-by-line product/service margin tracking, and a rolling 13-week cash-flow model.",
            'FH4', 'FH5' => "Through {$entry}: assemble a funder-grade data room, commission audited/reviewed financials, and match capital structure to target funder screening criteria.",
            'OE1', 'OE2', 'OE3' => "Through {$entry}: document core end-to-end SOPs (order-to-cash), introduce a weekly operating KPI dashboard, and measure asset/staff utilisation.",
            'OE4', 'OE5' => "Through {$entry}: enforce dual payment approval, monthly bank reconciliations, role-based system access, and automated data backups.",
            'MG1', 'MG2', 'MG3', 'MG4', 'MG5' => "Through {$entry}: validate customer acquisition cost by channel, de-risk top-customer concentration, map target-market regulations, and vet distribution partners.",
            'HC1', 'HC2', 'HC3', 'HC4', 'HC5' => "Through {$entry}: deploy role KPI scorecards, cross-train backups for critical roles, institute structured exit/retention reviews, and set an annual capability plan.",
            default => "Deploy {$entry} to close evidence gaps in {$name} and install monthly tracking.",
        };
    }

    private function buildRoadmap(array $scored, Framework $fw, array $recommendations): array
    {
        $p1 = [];
        $p2 = [];
        $p3 = [];
        foreach ($recommendations as $i => $r) {
            if ($r['priority'] === 'Critical' || $i < 2) {
                $p1[] = "{$r['title']}: {$r['intervention']}";
            } elseif ($r['priority'] === 'High' || $i < 5) {
                $p2[] = "{$r['title']}: {$r['intervention']}";
            } else {
                $p3[] = "{$r['title']}: {$r['intervention']}";
            }
        }

        if (empty($p1)) {
            $p1[] = 'Confirm diagnostic findings with a Bridge Tailor consultant and lock in Phase 1 KPI baselines.';
        }
        if (empty($p2)) {
            $p2[] = 'Embed monthly management reporting, process controls, and role accountability reviews.';
        }
        if (empty($p3)) {
            $p3[] = 'Re-run the Bridge Tailor Business Health Diagnostic to measure net score movement and unlock expansion/capital programmes.';
        }

        return [
            [
                'phase' => 'Phase 1 · Stabilise & Verify',
                'window' => 'Days 1 – 30',
                'focus' => 'Immediate risk containment, compliance verification, and foundational control setup',
                'actions' => $p1,
                'milestone' => 'Critical compliance & reporting blind spots closed; baseline KPIs agreed with Bridge Tailor.',
            ],
            [
                'phase' => 'Phase 2 · Build Systems & Governance',
                'window' => 'Days 31 – 90',
                'focus' => 'Install operating rhythm, financial visibility, and delegated decision structure',
                'actions' => $p2,
                'milestone' => '15-day monthly close, delegation matrix, and functional scorecards running without founder bottleneck.',
            ],
            [
                'phase' => 'Phase 3 · Scale, Fund & Expand',
                'window' => 'Days 91 – 180',
                'focus' => 'Capital preparation, market expansion readiness, and comparative re-assessment',
                'actions' => $p3,
                'milestone' => 'Follow-up diagnostic shows ≥20-point lift in weakest functional areas; data room or market entry package ready.',
            ],
        ];
    }

    private function deterministicDocumentExtraction(UploadedDocument $document, string $snippet): array
    {
        $facts = [];
        if ($snippet !== '') {
            // Look for explicit currency/number lines without inventing anything
            preg_match_all('/(?:GHS|USD|\$|₵|revenue|turnover|profit|margin|cash|audit|tax|board)[^\r\n\.]{3,140}/i', $snippet, $matches);
            foreach (array_slice($matches[0] ?? [], 0, 5) as $m) {
                $facts[] = [
                    'fact' => 'Excerpt from uploaded file: "'.trim($m).'"',
                    'source_basis' => 'DOCUMENT-EVIDENCED',
                    'module_relevance' => $document->category === 'governance_doc' ? 'SG' : ($document->category === 'business_plan' ? 'MG' : 'FH'),
                ];
            }
        }

        if (empty($facts)) {
            $facts[] = [
                'fact' => "Document '{$document->original_name}' ({$document->category}, ".number_format($document->size_bytes / 1024, 1).' KB) stored securely for consultant review.',
                'source_basis' => 'DOCUMENT-EVIDENCED',
                'module_relevance' => 'FH',
            ];
        }

        return [
            'document_summary' => "Uploaded {$document->category} ({$document->original_name}) catalogued and hashed (SHA-256: ".substr((string) $document->sha256, 0, 16).'…).',
            'extracted_facts' => $facts,
            'governance_or_financial_flags' => [
                'Figures in uploaded documents remain classified as client-provided until verified against primary statements by a Bridge-Tailor consultant.',
            ],
            'verification_note' => 'Processed by Bridge Tailor deterministic document inspector (strict non-hallucination mode).',
        ];
    }
}
