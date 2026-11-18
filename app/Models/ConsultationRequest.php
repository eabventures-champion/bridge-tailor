<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationRequest extends Model
{
    protected $fillable = [
        'company_id',
        'assessment_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'recommended_programme',
        'business_line',
        'estimated_value_ghs',
        'preferred_channel',
        'preferred_window',
        'client_message',
        'status',
        'assigned_consultant_id',
        'internal_notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'assigned_consultant_id');
    }
}
