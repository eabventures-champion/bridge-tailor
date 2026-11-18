<?php

namespace App\Diagnostics;

/**
 * Deterministic scoring engine.
 *
 * Every number shown to a client comes from here — never from the AI — so
 * scores are consistent, transparent and repeatable across clients.
 *
 * Method (Bridge-Tailor methodology):
 *  - "How it feels": pain ratings (1–5) on raised symptoms, weighted by how
 *    strongly each symptom points at a module, converted to 0–100 health.
 *  - "What the evidence shows": answers to evidence questions (in place /
 *    partly / missing → 100 / 50 / 0), averaged per criterion then module.
 *  - Module score = feel_weight × feel + (1 − feel_weight) × evidence when
 *    both exist; otherwise whichever exists. Areas with neither are "not
 *    assessed" — no data is not a clean bill of health.
 *  - Business Health Index = weighted mean of assessed module scores.
 */
class ScoringEngine
{
    public function __construct(private Framework $fw)
    {
    }

    /**
     * @param  array  $profile  ['stage' => ?, 'sector' => ?]
     * @param  string[]  $modules  modules in scope
     * @param  array<string,int>  $symptoms  symptom id => pain 1..5
     * @param  array<string,int>  $answers  question code => option index 0..2
     */
    public function score(array $profile, array $modules, array $symptoms, array $answers): array
    {
        $stage = $profile['stage'] ?? null;
        $sector = $profile['sector'] ?? null;
        $feelWeight = (float) $this->fw->get('scoring.feel_weight', 0.5);
        $gapThreshold = (int) $this->fw->get('scoring.perception_gap', 15);

        $moduleResults = [];
        $evidence = [];
        $risks = [];

        foreach ($this->fw->modules() as $mKey => $module) {
            $inScope = in_array($mKey, $modules, true);
            $painAvg = $this->painAverage($mKey, $symptoms);
            $feel = $painAvg === null ? null : $this->healthFromPain($painAvg);
            $raised = $this->raisedCount($mKey, $symptoms);

            $criteria = [];
            $dist = [0, 0, 0];
            $answered = 0;
            $evidWeighted = 0.0;
            $evidWeights = 0.0;

            foreach ($module['criteria'] ?? [] as $cKey => $criterion) {
                $sum = 0;
                $n = 0;
                foreach ($criterion['questions'] ?? [] as $q) {
                    if (! $inScope || ! $this->fw->appliesToProfile($q, $stage, $sector)) {
                        continue;
                    }
                    if (! $this->fw->followUpVisible($q, $answers)) {
                        continue;
                    }
                    if (! array_key_exists($q['code'], $answers)) {
                        continue;
                    }
                    $idx = max(0, min(2, (int) $answers[$q['code']]));
                    $sum += $this->fw->answerScore($idx);
                    $n++;
                    $dist[$idx]++;
                    $answered++;

                    if ($idx === 2) {
                        $evidence[] = [
                            'module' => $mKey, 'criterion' => $cKey, 'code' => $q['code'],
                            'question' => $q['text'], 'answer' => $q['options'][$idx],
                        ];
                    }
                    if (! empty($q['risk']) && $idx >= 1) {
                        if ($idx === 2) {
                            $risks[] = $this->risk($q, $mKey, $cKey, $idx, $q['risk'], 'current');
                        } elseif ($q['risk'] === 'critical') {
                            $risks[] = $this->risk($q, $mKey, $cKey, $idx, 'medium', 'emerging');
                        }
                    }
                }
                $cScore = $n ? (int) round($sum / $n) : null;
                $criteria[$cKey] = [
                    'key' => $cKey,
                    'name' => $criterion['name'],
                    'programme' => $criterion['programme'] ?? null,
                    'score' => $cScore,
                    'severity' => $this->fw->severityFor($cScore),
                    'answered' => $n,
                ];
                if ($cScore !== null) {
                    $w = (float) ($criterion['weight'] ?? 1);
                    $evidWeighted += $cScore * $w;
                    $evidWeights += $w;
                }
            }

            $evid = $evidWeights > 0 ? (int) round($evidWeighted / $evidWeights) : null;
            if (! $inScope) {
                $feel = null; // out-of-scope modules are not scored, even if symptoms point at them
            }

            if ($feel !== null && $evid !== null) {
                $score = (int) round($feelWeight * $feel + (1 - $feelWeight) * $evid);
            } else {
                $score = $evid ?? $feel;
            }

            $moduleResults[$mKey] = [
                'key' => $mKey,
                'name' => $module['name'],
                'short' => $module['short'] ?? $module['name'],
                'in_scope' => $inScope,
                'score' => $score,
                'feel' => $feel,
                'evidence' => $evid,
                'severity' => $this->fw->severityFor($score),
                'label' => $this->fw->areaLabel($score),
                'perception' => $this->perception($feel, $evid, $gapThreshold),
                'raised' => $raised,
                'answered' => $answered,
                'distribution' => $dist,
                'criteria' => $criteria,
            ];
        }

        $assessed = array_filter($moduleResults, fn ($m) => $m['score'] !== null);
        $index = null;
        if ($assessed) {
            $wSum = 0.0;
            $wTot = 0.0;
            foreach ($assessed as $m) {
                $w = (float) ($this->fw->module($m['key'])['weight'] ?? 1);
                $wSum += $m['score'] * $w;
                $wTot += $w;
            }
            $index = (int) round($wSum / $wTot);
        }

        $programmes = $this->programmeScores($moduleResults, $feelWeight);
        $sequence = $this->sequence($programmes);
        $insights = $this->insights($moduleResults, $symptoms, $stage);

        $allCriteria = [];
        foreach ($assessed as $m) {
            foreach ($m['criteria'] as $c) {
                if ($c['score'] !== null) {
                    $allCriteria[] = $c + ['module' => $m['key'], 'module_name' => $m['name']];
                }
            }
        }
        usort($allCriteria, fn ($a, $b) => $b['score'] <=> $a['score']);
        $strengths = array_values(array_filter($allCriteria, fn ($c) => $c['score'] >= 75));
        $gaps = array_values(array_reverse(array_filter($allCriteria, fn ($c) => $c['score'] < 50)));

        $sevOrder = ['critical' => 0, 'high' => 1, 'medium' => 2];
        usort($risks, fn ($a, $b) => $sevOrder[$a['severity']] <=> $sevOrder[$b['severity']]);

        $totals = array_reduce($assessed, fn ($c, $m) => [
            $c[0] + $m['distribution'][0], $c[1] + $m['distribution'][1], $c[2] + $m['distribution'][2],
        ], [0, 0, 0]);
        $qTotal = array_sum($totals);

        return [
            'framework_version' => $this->fw->version(),
            'scored_at' => now()->toIso8601String(),
            'health_index' => $index,
            'band' => $this->fw->indexBand($index),
            'coverage' => [
                'assessed' => count($assessed),
                'total' => count($moduleResults),
                'not_assessed' => array_values(array_keys(array_diff_key($moduleResults, $assessed))),
            ],
            'kpis' => [
                'issues_raised' => count($symptoms),
                'answered' => $qTotal,
                'in_place_pct' => $qTotal ? (int) round($totals[0] / $qTotal * 100) : 0,
                'partial_pct' => $qTotal ? (int) round($totals[1] / $qTotal * 100) : 0,
                'missing_pct' => $qTotal ? (int) round($totals[2] / $qTotal * 100) : 0,
            ],
            'modules' => $moduleResults,
            'readings' => $this->readings($moduleResults, $gapThreshold),
            'strengths' => array_slice($strengths, 0, 8),
            'gaps' => array_slice($gaps, 0, 10),
            'evidence' => $evidence,
            'risks' => $risks,
            'programmes' => $programmes,
            'sequence' => $sequence,
            'insights' => $insights,
            'symptoms' => $this->symptomList($symptoms),
        ];
    }

    private function painAverage(string $mKey, array $symptoms): ?float
    {
        $wSum = 0.0;
        $wTot = 0.0;
        foreach ($symptoms as $id => $pain) {
            $s = $this->fw->symptom((string) $id);
            if (! $s || ! isset($s['modules'][$mKey])) {
                continue;
            }
            $w = (float) $s['modules'][$mKey];
            $wSum += max(1, min(5, (int) $pain)) * $w;
            $wTot += $w;
        }

        return $wTot > 0 ? $wSum / $wTot : null;
    }

    private function raisedCount(string $mKey, array $symptoms): int
    {
        $n = 0;
        foreach (array_keys($symptoms) as $id) {
            $s = $this->fw->symptom((string) $id);
            if ($s && isset($s['modules'][$mKey])) {
                $n++;
            }
        }

        return $n;
    }

    /** Pain 1 (fine) → 100, pain 5 (serious) → 0. */
    public function healthFromPain(float $pain): int
    {
        return (int) round(100 - (($pain - 1) / 4) * 100);
    }

    private function perception(?int $feel, ?int $evid, int $threshold): array
    {
        if ($evid === null || $feel === null) {
            return ['key' => 'none', 'label' => $evid === null ? 'No evidence yet' : 'Evidence only', 'gap' => null];
        }
        $d = $evid - $feel;
        if ($d <= -$threshold) {
            return ['key' => 'weaker', 'label' => 'Evidence weaker than it feels', 'gap' => $d];
        }
        if ($d >= $threshold) {
            return ['key' => 'better', 'label' => 'Evidence better than it feels', 'gap' => $d];
        }

        return ['key' => 'match', 'label' => 'Consistent', 'gap' => $d];
    }

    private function risk(array $q, string $m, string $c, int $idx, string $severity, string $type): array
    {
        return [
            'code' => $q['code'], 'module' => $m, 'criterion' => $c,
            'severity' => $severity, 'type' => $type,
            'question' => $q['text'], 'answer' => $q['options'][$idx],
        ];
    }

    private function programmeScores(array $modules, float $feelWeight): array
    {
        $acc = [];
        foreach ($modules as $m) {
            foreach ($m['criteria'] as $c) {
                if ($c['score'] === null || ! $c['programme']) {
                    continue;
                }
                $s = $m['feel'] !== null ? $feelWeight * $m['feel'] + (1 - $feelWeight) * $c['score'] : $c['score'];
                $acc[$c['programme']][] = $s;
            }
        }
        $out = [];
        foreach ($this->fw->programmes() as $pKey => $p) {
            if (empty($acc[$pKey])) {
                continue;
            }
            $score = (int) round(array_sum($acc[$pKey]) / count($acc[$pKey]));
            $sev = $this->fw->severityFor($score);
            $effort = $p['effort'][$sev] ?? [0, 0];
            $out[$pKey] = [
                'key' => $pKey,
                'line' => $p['line'],
                'entry' => $p['entry'],
                'score' => $score,
                'severity' => $sev,
                'effort_weeks' => $effort,
                'mode' => $this->fw->get("delivery_modes.{$sev}"),
            ];
        }

        return $out;
    }

    private function sequence(array $programmes): array
    {
        $list = array_filter($programmes, fn ($p) => $p['severity'] !== 'Low');
        if (! $list) {
            $list = $programmes;
        }
        uasort($list, fn ($a, $b) => $a['score'] <=> $b['score']);
        $order = array_keys($list);
        $gatedReason = null;

        foreach ($this->fw->get('sequencing.gates', []) as $gate) {
            $first = $gate['first'];
            $before = $gate['before'];
            if (! isset($list[$first], $list[$before])) {
                continue;
            }
            if (($list[$first]['severity'] ?? null) === ($gate['unless_severity'] ?? null)) {
                continue;
            }
            $fi = array_search($first, $order, true);
            $bi = array_search($before, $order, true);
            if ($fi > $bi) {
                array_splice($order, $fi, 1);
                array_splice($order, $bi, 0, [$first]);
            }
            if ($order[0] === $first) {
                $gatedReason = $gate['reason'] ?? null;
            }
        }

        $out = [];
        foreach ($order as $i => $pKey) {
            $out[] = [
                'programme' => $pKey,
                'position' => $i + 1,
                'reason' => $i === 0
                    ? ($gatedReason ?? 'Of everything assessed, this is where the business is under the most pressure right now — and fixing it makes the rest easier.')
                    : null,
            ];
        }

        return $out;
    }

    private function insights(array $modules, array $symptoms, ?string $stage): array
    {
        $limit = (int) $this->fw->get('insight_limit', 3);
        $criteriaSev = [];
        foreach ($modules as $m) {
            foreach ($m['criteria'] as $cKey => $c) {
                $criteriaSev[$cKey] = $c['severity'];
            }
        }
        $weak = count(array_filter($modules, fn ($m) => in_array($m['severity'], ['Medium', 'High'], true)));

        $specific = [];
        $fallback = [];
        foreach ($this->fw->get('insights', []) as $ins) {
            if (! $this->conditionsHold($ins['when'] ?? [], $modules, $criteriaSev, $symptoms, $stage, $weak)) {
                continue;
            }
            $item = ['id' => $ins['id'], 'title' => $ins['title'], 'body' => $ins['body']];
            if (! empty($ins['fallback'])) {
                $fallback[] = $item;
            } else {
                $specific[] = $item;
            }
        }

        return array_slice(array_merge($specific, $fallback), 0, $limit);
    }

    private function conditionsHold(array $when, array $modules, array $critSev, array $symptoms, ?string $stage, int $weak): bool
    {
        if (! $when) {
            return false;
        }
        foreach ($when['module_severity'] ?? [] as $mk => $levels) {
            if (! in_array($modules[$mk]['severity'] ?? null, $levels, true)) {
                return false;
            }
        }
        foreach ($when['criterion_severity'] ?? [] as $ck => $levels) {
            if (! in_array($critSev[$ck] ?? null, $levels, true)) {
                return false;
            }
        }
        if (! empty($when['symptom_any']) && ! array_intersect($when['symptom_any'], array_map('strval', array_keys($symptoms)))) {
            return false;
        }
        if (! empty($when['stage']) && ! in_array($stage, $when['stage'], true)) {
            return false;
        }
        if (isset($when['min_weak_modules']) && $weak < (int) $when['min_weak_modules']) {
            return false;
        }

        return true;
    }

    /** Key readings: computed, not written. */
    private function readings(array $modules, int $threshold): array
    {
        $live = array_values(array_filter($modules, fn ($m) => $m['score'] !== null));
        if (! $live) {
            return [];
        }
        $ranked = $live;
        usort($ranked, fn ($a, $b) => $a['score'] <=> $b['score']);
        $out = [];

        $w = $ranked[0];
        $out[] = ['key' => 'Weakest area', 'text' => "{$w['name']} at {$w['score']}/100"
            .($w['answered'] ? ", with {$w['distribution'][2]} of {$w['answered']} evidence answers at the lowest level." : '.')];

        if (count($ranked) > 1) {
            $s = end($ranked);
            $out[] = ['key' => 'Strongest area', 'text' => "{$s['name']} at {$s['score']}/100 — ".($s['score'] - $w['score']).' points ahead of the weakest.'];
        }

        $withBoth = array_filter($live, fn ($m) => $m['feel'] !== null && $m['evidence'] !== null);
        if ($withBoth) {
            usort($withBoth, fn ($a, $b) => abs($b['evidence'] - $b['feel']) <=> abs($a['evidence'] - $a['feel']));
            $g = $withBoth[0];
            $d = $g['evidence'] - $g['feel'];
            if (abs($d) >= $threshold) {
                $out[] = ['key' => 'Biggest blind spot', 'text' => "{$g['name']}: the evidence scores {$g['evidence']} against {$g['feel']} for how it feels. "
                    .($d < 0 ? 'The problem is likely larger than it looks from the inside.'
                        : 'The foundations are better than the frustration suggests; the issue may be execution rather than structure.')];
            } else {
                $out[] = ['key' => 'Perception check', 'text' => 'How the business feels and what the evidence shows are within '.abs($d).' points of each other in every area answered. The picture is internally consistent.'];
            }
        }

        $un = count($modules) - count($live);
        if ($un > 0) {
            $out[] = ['key' => 'Coverage', 'text' => "{$un} of ".count($modules).' areas were not assessed, so they carry no score. That means no data, not a clean bill of health.'];
        }

        return $out;
    }

    private function symptomList(array $symptoms): array
    {
        $out = [];
        foreach ($symptoms as $id => $pain) {
            $s = $this->fw->symptom((string) $id);
            if ($s) {
                $out[] = ['id' => $s['id'], 'label' => $s['label'], 'pain' => (int) $pain, 'modules' => array_keys($s['modules'])];
            }
        }
        usort($out, fn ($a, $b) => $b['pain'] <=> $a['pain']);

        return $out;
    }
}
