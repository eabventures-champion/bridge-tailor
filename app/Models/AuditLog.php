<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'assessment_id',
        'event',
        'actor_label',
        'ip_address',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public static function record(
        string $event,
        ?int $companyId = null,
        ?int $assessmentId = null,
        array $metadata = [],
        ?string $actorLabel = null
    ): self {
        $user = Auth::user();

        return static::create([
            'user_id' => $user?->id,
            'company_id' => $companyId,
            'assessment_id' => $assessmentId,
            'event' => $event,
            'actor_label' => $actorLabel ?? ($user ? "{$user->name} ({$user->role})" : 'Client / Session'),
            'ip_address' => Request::ip(),
            'metadata' => $metadata,
        ]);
    }
}
