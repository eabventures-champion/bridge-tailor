<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Benchmark extends Model
{
    protected $fillable = [
        'sector',
        'stage',
        'module_key',
        'p25_score',
        'median_score',
        'p75_score',
        'top_decile_score',
        'sample_note',
    ];

    /**
     * Returns module benchmarks for a sector & stage, falling back to 'general' / 'growth'.
     *
     * @return array<string, array>
     */
    public static function forProfile(string $sector, string $stage): array
    {
        $rows = static::query()
            ->whereIn('sector', [$sector, 'general'])
            ->whereIn('stage', [$stage, 'growth'])
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $specificity = ($row->sector === $sector ? 2 : 0) + ($row->stage === $stage ? 1 : 0);
            if (! isset($out[$row->module_key]) || $specificity > $out[$row->module_key]['_spec']) {
                $out[$row->module_key] = [
                    'module_key' => $row->module_key,
                    'sector' => $row->sector,
                    'stage' => $row->stage,
                    'p25' => $row->p25_score,
                    'median' => $row->median_score,
                    'p75' => $row->p75_score,
                    'top_decile' => $row->top_decile_score,
                    'sample_note' => $row->sample_note,
                    '_spec' => $specificity,
                ];
            }
        }

        foreach ($out as &$v) {
            unset($v['_spec']);
        }

        return $out;
    }
}
