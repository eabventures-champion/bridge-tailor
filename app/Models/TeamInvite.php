<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeamInvite extends Model
{
    protected $fillable = [
        'assessment_id',
        'token',
        'respondent_name',
        'respondent_email',
        'respondent_role',
        'symptoms',
        'answers',
        'notes',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'symptoms' => 'array',
            'answers' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TeamInvite $invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(40);
            }
        });
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
