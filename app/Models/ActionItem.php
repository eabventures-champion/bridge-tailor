<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    protected $fillable = [
        'company_id',
        'assessment_id',
        'module_key',
        'criterion_key',
        'programme_key',
        'title',
        'intervention',
        'priority',
        'horizon',
        'owner_name',
        'target_metric',
        'status',
        'net_delta',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function logs()
    {
        return $this->hasMany(ActionLog::class)->latest('id');
    }
}
