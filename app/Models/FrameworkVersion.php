<?php

namespace App\Models;

use App\Diagnostics\Framework;
use Illuminate\Database\Eloquent\Model;

class FrameworkVersion extends Model
{
    protected $fillable = [
        'version',
        'name',
        'definition',
        'is_active',
        'change_type',
        'change_notes',
        'published_by',
        'published_at',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'framework_version_id');
    }

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function toFramework(): Framework
    {
        return new Framework($this->definition ?? [], $this->id);
    }

    public static function active(): Framework
    {
        $record = static::query()->where('is_active', true)->latest('id')->first()
            ?? static::query()->latest('id')->first();

        if ($record) {
            return $record->toFramework();
        }

        $fallback = require base_path('resources/framework/bridge-tailor-v1.php');

        return new Framework($fallback, null);
    }
}
