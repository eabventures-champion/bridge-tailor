<?php

namespace App\Diagnostics;

use Illuminate\Support\Arr;

/**
 * Read-only wrapper around a framework definition array.
 * Applies adaptive rules (stage, sector, follow-ups) when listing questions.
 */
class Framework
{
    public function __construct(public readonly array $definition, public readonly ?int $versionId = null)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->definition, $key, $default);
    }

    public function version(): string
    {
        return (string) $this->get('version', '1.0.0');
    }

    /** @return array<string, array> */
    public function modules(): array
    {
        $raw = $this->get('modules', []);
        $order = $this->get('module_order', ['SG', 'FH', 'OE', 'MG', 'HC']);
        $ordered = [];
        foreach ($order as $k) {
            if (isset($raw[$k])) {
                $ordered[$k] = $raw[$k];
            }
        }
        foreach ($raw as $k => $v) {
            if (! isset($ordered[$k])) {
                $ordered[$k] = $v;
            }
        }

        return $ordered;
    }

    public function moduleKeys(): array
    {
        return array_keys($this->modules());
    }

    public function module(string $key): ?array
    {
        return $this->modules()[$key] ?? null;
    }

    public function moduleName(string $key): string
    {
        return $this->module($key)['name'] ?? $key;
    }

    /** Flat list of criteria keyed by criterion key, each with its module key. */
    public function criteria(): array
    {
        $out = [];
        foreach ($this->modules() as $mKey => $module) {
            foreach ($module['criteria'] ?? [] as $cKey => $criterion) {
                $out[$cKey] = $criterion + ['module' => $mKey];
            }
        }

        return $out;
    }

    /** All questions keyed by code, enriched with module and criterion keys. */
    public function allQuestions(): array
    {
        $out = [];
        foreach ($this->modules() as $mKey => $module) {
            foreach ($module['criteria'] ?? [] as $cKey => $criterion) {
                foreach ($criterion['questions'] ?? [] as $q) {
                    $out[$q['code']] = $q + ['module' => $mKey, 'criterion' => $cKey];
                }
            }
        }

        return $out;
    }

    public function question(string $code): ?array
    {
        return $this->allQuestions()[$code] ?? null;
    }

    /**
     * Questions that apply to a company profile for one module, ignoring follow-up
     * visibility (the browser reveals follow-ups; the scorer re-checks them).
     */
    public function questionsForModule(string $moduleKey, ?string $stage, ?string $sector): array
    {
        return array_filter(
            $this->allQuestions(),
            fn ($q) => $q['module'] === $moduleKey && $this->appliesToProfile($q, $stage, $sector)
        );
    }

    public function appliesToProfile(array $q, ?string $stage, ?string $sector): bool
    {
        if (! empty($q['skip_stages']) && $stage && in_array($stage, $q['skip_stages'], true)) {
            return false;
        }
        if (! empty($q['sectors']) && ! in_array($sector, $q['sectors'], true)) {
            return false;
        }

        return true;
    }

    /** Whether a follow-up question is visible given the answers so far. */
    public function followUpVisible(array $q, array $answers): bool
    {
        if (empty($q['show_if'])) {
            return true;
        }
        $parent = $answers[$q['show_if']['q']] ?? null;

        return $parent !== null && (int) $parent >= (int) ($q['show_if']['min'] ?? 1);
    }

    public function symptoms(): array
    {
        return $this->get('symptoms', []);
    }

    public function symptom(string $id): ?array
    {
        foreach ($this->symptoms() as $s) {
            if ($s['id'] === $id) {
                return $s;
            }
        }

        return null;
    }

    public function programmes(): array
    {
        return $this->get('programmes', []);
    }

    public function programme(string $key): ?array
    {
        return $this->programmes()[$key] ?? null;
    }

    public function answerScore(int $index): int
    {
        $scores = $this->get('scoring.answer_scores', [100, 50, 0]);

        return (int) ($scores[$index] ?? 0);
    }

    public function severityFor(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }
        if ($score >= $this->get('scoring.severity.low_from', 67)) {
            return 'Low';
        }
        if ($score >= $this->get('scoring.severity.medium_from', 33)) {
            return 'Medium';
        }

        return 'High';
    }

    public function indexBand(?int $score): ?array
    {
        if ($score === null) {
            return null;
        }
        foreach ($this->get('scoring.index_bands', []) as $band) {
            if ($score >= $band['min'] && $score <= $band['max']) {
                return $band;
            }
        }

        return null;
    }

    public function areaLabel(?int $score): ?string
    {
        if ($score === null) {
            return null;
        }
        foreach ($this->get('scoring.area_labels', []) as $band) {
            if ($score >= $band['min'] && $score <= $band['max']) {
                return $band['label'];
            }
        }

        return null;
    }

    public function stages(): array
    {
        return $this->get('stages', []);
    }

    public function sectors(): array
    {
        return $this->get('sectors', []);
    }

    /**
     * Structural validation used when administrators publish a new version.
     *
     * @return string[] list of problems (empty when valid)
     */
    public static function validate(array $d): array
    {
        $errors = [];
        foreach (['version', 'name', 'scoring', 'modules', 'symptoms', 'programmes'] as $k) {
            if (! array_key_exists($k, $d)) {
                $errors[] = "Missing top-level key: {$k}";
            }
        }
        if ($errors) {
            return $errors;
        }
        $codes = [];
        foreach ($d['modules'] as $mKey => $m) {
            if (empty($m['name'])) {
                $errors[] = "Module {$mKey} has no name";
            }
            foreach ($m['criteria'] ?? [] as $cKey => $c) {
                if (! empty($c['programme']) && ! isset($d['programmes'][$c['programme']])) {
                    $errors[] = "Criterion {$cKey} routes to unknown programme {$c['programme']}";
                }
                foreach ($c['questions'] ?? [] as $q) {
                    if (empty($q['code']) || empty($q['text'])) {
                        $errors[] = "A question in {$cKey} is missing code or text";
                        continue;
                    }
                    if (isset($codes[$q['code']])) {
                        $errors[] = "Duplicate question code {$q['code']}";
                    }
                    $codes[$q['code']] = true;
                    if (count($q['options'] ?? []) !== 3) {
                        $errors[] = "Question {$q['code']} must have exactly 3 options (best → worst)";
                    }
                }
            }
        }
        foreach ($d['symptoms'] as $s) {
            foreach (array_keys($s['modules'] ?? []) as $mk) {
                if (! isset($d['modules'][$mk])) {
                    $errors[] = "Symptom {$s['id']} points at unknown module {$mk}";
                }
            }
        }

        return $errors;
    }
}
