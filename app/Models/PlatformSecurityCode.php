<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlatformSecurityCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_code',
        'email',
        'name',
        'organization',
        'role_requested',
        'request_message',
        'delivered_to',
        'status',
        'times_used',
        'max_uses',
        'ip_address',
        'user_agent',
        'approved_by',
        'expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'times_used' => 'integer',
            'max_uses' => 'integer',
        ];
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateUniqueCode(): string
    {
        do {
            // Generate clean, high-entropy, human-friendly code: BT-SEC-XXXXXX
            $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            $segment = '';
            for ($i = 0; $i < 6; $i++) {
                $segment .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = 'BT-SEC-' . $segment;
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses > 0 && $this->times_used >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function markUsed(?string $ip = null, ?string $userAgent = null): void
    {
        $this->times_used++;
        $this->last_used_at = now();

        if ($ip) {
            $this->ip_address = $ip;
        }
        if ($userAgent) {
            $this->user_agent = Str::limit($userAgent, 500);
        }

        if ($this->max_uses > 0 && $this->times_used >= $this->max_uses) {
            $this->status = 'used';
        }

        $this->save();
    }
}
