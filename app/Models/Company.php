<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'ulid',
        'name',
        'sector',
        'stage',
        'size_band',
        'revenue_band',
        'country',
        'city',
        'contact_name',
        'contact_email',
        'contact_phone',
        'objectives',
        'challenges',
        'owner_user_id',
        'assigned_consultant_id',
        'retention_policy',
    ];

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->ulid)) {
                $company->ulid = (string) Str::ulid();
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'assigned_consultant_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('company_role')
            ->withTimestamps();
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class)->latest('id');
    }

    public function documents()
    {
        return $this->hasMany(UploadedDocument::class)->latest('id');
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class)->latest('id');
    }

    public function consultationRequests()
    {
        return $this->hasMany(ConsultationRequest::class)->latest('id');
    }
}
