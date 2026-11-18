<?php

namespace App\Models;

use App\Diagnostics\Framework;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Assessment extends Model
{
    protected $fillable = [
        'ulid',
        'access_token',
        'company_id',
        'framework_version_id',
        'created_by',
        'mode',
        'modules_in_scope',
        'symptoms',
        'answers',
        'custom_issue',
        'score_snapshot',
        'health_index',
        'health_band',
        'ai_consent',
        'ai_consented_at',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'modules_in_scope' => 'array',
            'symptoms' => 'array',
            'answers' => 'array',
            'score_snapshot' => 'array',
            'ai_consent' => 'boolean',
            'ai_consented_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Assessment $assessment) {
            if (empty($assessment->ulid)) {
                $assessment->ulid = (string) Str::ulid();
            }
            if (empty($assessment->access_token)) {
                $assessment->access_token = Str::random(48);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function frameworkVersion()
    {
        return $this->belongsTo(FrameworkVersion::class);
    }

    public function framework(): Framework
    {
        return $this->frameworkVersion?->toFramework() ?? FrameworkVersion::active();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function report()
    {
        return $this->hasOne(DiagnosticReport::class)->latestOfMany();
    }

    public function reports()
    {
        return $this->hasMany(DiagnosticReport::class)->latest('id');
    }

    public function teamInvites()
    {
        return $this->hasMany(TeamInvite::class);
    }

    public function documents()
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }

    public function consultationRequests()
    {
        return $this->hasMany(ConsultationRequest::class);
    }
}
