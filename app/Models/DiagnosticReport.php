<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticReport extends Model
{
    protected $fillable = [
        'assessment_id',
        'ai_provider',
        'ai_model',
        'ai_analysis',
        'consultant_amendments',
        'executive_summary',
        'consultant_notes',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_analysis' => 'array',
            'consultant_amendments' => 'array',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Merges consultant amendments over the initial AI analysis so the
     * published/approved report reflects consultant judgement.
     */
    public function effectiveAnalysis(): array
    {
        $base = $this->ai_analysis ?? [];
        $amendments = $this->consultant_amendments ?? [];

        return array_replace_recursive($base, array_filter($amendments, fn ($v) => $v !== null && $v !== ''));
    }
}
