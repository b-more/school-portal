<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_type',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'issue_date',
        'expiry_date',
        'notes',
        'uploaded_by',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public const DOCUMENT_TYPES = [
        'contract' => 'Employment Contract',
        'nrc' => 'NRC (National Registration Card)',
        'passport' => 'Passport',
        'qualification' => 'Educational Certificate',
        'professional_cert' => 'Professional Certification',
        'medical' => 'Medical Certificate',
        'police_clearance' => 'Police Clearance',
        'reference' => 'Reference Letter',
        'cv' => 'Curriculum Vitae',
        'photo' => 'Passport Photo',
        'other' => 'Other Document',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
