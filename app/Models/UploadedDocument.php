<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedDocument extends Model
{
    protected $fillable = [
        'company_id',
        'assessment_id',
        'uploaded_by',
        'category',
        'original_name',
        'storage_path',
        'mime_type',
        'size_bytes',
        'sha256',
        'extracted_text',
        'ai_extraction',
        'extraction_status',
        'retention_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_extraction' => 'array',
            'retention_expires_at' => 'datetime',
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

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
