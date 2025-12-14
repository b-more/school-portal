<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SchoolSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        // School Identity
        'school_name',
        'school_code',
        'registration_number',
        'tax_pin',
        'school_motto',
        'school_vision',
        'school_mission',
        'school_logo',
        'favicon',
        'header_logo',
        'footer_logo',
        'report_card_logo',
        'primary_color',
        'secondary_color',
        'accent_color',

        // Contact Information
        'address',
        'city',
        'state_province',
        'country',
        'postal_code',
        'phone',
        'alternate_phone',
        'email',
        'website',
        'social_media_links',

        // Academic Settings
        'academic_year_format',
        'terms_per_year',
        'grading_system',
        'passing_mark',
        'max_mark',
        'show_position_in_class',
        'show_position_in_grade',
        'show_grade_average',
        'enable_continuous_assessment',
        'ca_weight_percentage',
        'exam_weight_percentage',

        // Grading Scale (Zambian ECZ Standard)
        'grade_a_min',
        'grade_b_min',
        'grade_c_min',
        'grade_d_min',
        'grade_e_min',
        'grade_a_remark',
        'grade_b_remark',
        'grade_c_remark',
        'grade_d_remark',
        'grade_e_remark',

        // Attendance Settings
        'school_start_time',
        'school_end_time',
        'late_arrival_minutes',
        'notify_parent_on_absence',
        'notify_parent_on_late',
        'absence_notification_threshold',
        'school_days',

        // Fee Settings
        'enable_online_payments',
        'enable_partial_payments',
        'minimum_partial_payment',
        'enable_late_fees',
        'late_fee_percentage',
        'grace_period_days',
        'invoice_prefix',
        'receipt_prefix',
        'payment_instructions',
        'payment_methods',
        'bank_details',
        'mobile_money_details',

        // SMS & Communication
        'sms_sender_id',
        'enable_sms_notifications',
        'enable_email_notifications',
        'enable_whatsapp_notifications',
        'sms_on_fee_payment',
        'sms_on_result_release',
        'sms_on_attendance',
        'sms_on_homework',
        'sms_balance_alert_threshold',

        // Report Card Settings
        'report_card_format',
        'show_teacher_comments',
        'show_headteacher_comments',
        'show_principal_signature',
        'show_class_teacher_signature',
        'show_parent_signature_line',
        'show_attendance_summary',
        'show_conduct_grade',
        'principal_name',
        'principal_title',
        'principal_signature',
        'report_card_footer_text',
        'next_term_starts',
        'next_term_ends',

        // System Settings
        'currency_code',
        'timezone',
        'date_format',
        'time_format',
        'datetime_format',
        'session_timeout_minutes',
        'enable_maintenance_mode',
        'maintenance_message',
        'enable_student_portal',
        'enable_parent_portal',
        'enable_teacher_portal',
        'require_password_change_on_first_login',
        'password_expiry_days',
        'max_login_attempts',
        'lockout_duration_minutes',

        // Backup Settings
        'enable_auto_backup',
        'backup_frequency',
        'backup_time',
        'backup_retention_days',

        // Section-specific Settings
        'school_head_name',
        'school_head_title',
        'primary_head_name',
        'primary_head_title',
        'primary_head_signature',
        'secondary_head_name',
        'secondary_head_title',
        'secondary_head_signature',

        // Metadata
        'custom_settings',
        'settings_last_updated_at',
        'settings_updated_by',
    ];

    protected $casts = [
        'social_media_links' => 'array',
        'school_days' => 'array',
        'payment_methods' => 'array',
        'bank_details' => 'array',
        'mobile_money_details' => 'array',
        'custom_settings' => 'array',

        // Booleans
        'show_position_in_class' => 'boolean',
        'show_position_in_grade' => 'boolean',
        'show_grade_average' => 'boolean',
        'enable_continuous_assessment' => 'boolean',
        'notify_parent_on_absence' => 'boolean',
        'notify_parent_on_late' => 'boolean',
        'enable_online_payments' => 'boolean',
        'enable_partial_payments' => 'boolean',
        'enable_late_fees' => 'boolean',
        'enable_sms_notifications' => 'boolean',
        'enable_email_notifications' => 'boolean',
        'enable_whatsapp_notifications' => 'boolean',
        'sms_on_fee_payment' => 'boolean',
        'sms_on_result_release' => 'boolean',
        'sms_on_attendance' => 'boolean',
        'sms_on_homework' => 'boolean',
        'show_teacher_comments' => 'boolean',
        'show_headteacher_comments' => 'boolean',
        'show_principal_signature' => 'boolean',
        'show_class_teacher_signature' => 'boolean',
        'show_parent_signature_line' => 'boolean',
        'show_attendance_summary' => 'boolean',
        'show_conduct_grade' => 'boolean',
        'enable_maintenance_mode' => 'boolean',
        'enable_student_portal' => 'boolean',
        'enable_parent_portal' => 'boolean',
        'enable_teacher_portal' => 'boolean',
        'require_password_change_on_first_login' => 'boolean',
        'enable_auto_backup' => 'boolean',

        // Dates and Times
        'school_start_time' => 'datetime:H:i',
        'school_end_time' => 'datetime:H:i',
        'backup_time' => 'datetime:H:i',
        'next_term_starts' => 'date',
        'next_term_ends' => 'date',
        'settings_last_updated_at' => 'datetime',

        // Numbers
        'terms_per_year' => 'integer',
        'passing_mark' => 'integer',
        'max_mark' => 'integer',
        'ca_weight_percentage' => 'integer',
        'exam_weight_percentage' => 'integer',
        'late_arrival_minutes' => 'integer',
        'absence_notification_threshold' => 'integer',
        'grace_period_days' => 'integer',
        'sms_balance_alert_threshold' => 'integer',
        'session_timeout_minutes' => 'integer',
        'password_expiry_days' => 'integer',
        'max_login_attempts' => 'integer',
        'lockout_duration_minutes' => 'integer',
        'backup_retention_days' => 'integer',
        'minimum_partial_payment' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',

        // Grading scale
        'grade_a_min' => 'integer',
        'grade_b_min' => 'integer',
        'grade_c_min' => 'integer',
        'grade_d_min' => 'integer',
        'grade_e_min' => 'integer',
    ];

    // Cache key for settings
    const CACHE_KEY = 'school_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Singleton pattern - there should only be one school settings record
     */
    public static function getInstance(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $settings = self::first();

            if (!$settings) {
                $settings = self::create([
                    'school_name' => 'School Name',
                    'currency_code' => 'ZMW',
                    'timezone' => 'Africa/Lusaka',
                    'school_days' => [1, 2, 3, 4, 5], // Monday to Friday
                    'payment_methods' => ['cash', 'bank_transfer', 'mobile_money'],
                ]);
            }

            return $settings;
        });
    }

    /**
     * Clear the settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Boot method to clear cache on save
     */
    protected static function booted(): void
    {
        static::saved(function ($settings) {
            self::clearCache();
        });
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Get a specific setting value with fallback
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getInstance();
        return $settings->{$key} ?? $default;
    }

    /**
     * Get the full school address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state_province,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the formatted phone numbers
     */
    public function getContactPhonesAttribute(): string
    {
        $phones = array_filter([$this->phone, $this->alternate_phone]);
        return implode(' / ', $phones);
    }

    /**
     * Check if a specific day is a school day
     */
    public function isSchoolDay(int $dayOfWeek): bool
    {
        $schoolDays = $this->school_days ?? [1, 2, 3, 4, 5];
        return in_array($dayOfWeek, $schoolDays);
    }

    /**
     * Get late arrival threshold time
     */
    public function getLateArrivalTimeAttribute(): string
    {
        $startTime = Carbon::parse($this->school_start_time ?? '07:30');
        return $startTime->addMinutes($this->late_arrival_minutes ?? 15)->format('H:i');
    }

    /**
     * Get formatted currency
     */
    public function formatCurrency($amount): string
    {
        $symbol = match ($this->currency_code) {
            'ZMW' => 'K',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            default => $this->currency_code . ' ',
        };

        return $symbol . number_format($amount, 2);
    }

    /**
     * Get next invoice number
     */
    public function getNextInvoiceNumber(): string
    {
        $prefix = $this->invoice_prefix ?? 'INV';
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        // This would typically come from a separate invoice table
        $lastNumber = 1; // Default

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $lastNumber);
    }

    /**
     * Get next receipt number
     */
    public function getNextReceiptNumber(): string
    {
        $prefix = $this->receipt_prefix ?? 'RCP';
        $year = date('Y');
        $month = date('m');

        $lastNumber = 1; // Default

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $lastNumber);
    }

    /**
     * Check if SMS notifications are enabled for a specific event
     */
    public function shouldSendSms(string $event): bool
    {
        if (!$this->enable_sms_notifications) {
            return false;
        }

        return match ($event) {
            'fee_payment' => $this->sms_on_fee_payment ?? true,
            'result_release' => $this->sms_on_result_release ?? true,
            'attendance' => $this->sms_on_attendance ?? false,
            'homework' => $this->sms_on_homework ?? false,
            default => false,
        };
    }

    /**
     * Get the grading scale based on settings
     */
    public function getGradeForMark(int $mark): array
    {
        $percentage = ($mark / $this->max_mark) * 100;

        if ($this->grading_system === 'percentage') {
            return [
                'grade' => round($percentage) . '%',
                'passed' => $percentage >= $this->passing_mark,
                'letter' => $this->getGradeLetter($percentage),
                'remark' => $this->getGradeRemark($percentage),
            ];
        }

        // Letter grade system using configurable boundaries
        return [
            'grade' => $this->getGradeLetter($percentage),
            'passed' => $percentage >= ($this->grade_d_min ?? 40),
            'letter' => $this->getGradeLetter($percentage),
            'remark' => $this->getGradeRemark($percentage),
        ];
    }

    /**
     * Get grade letter based on percentage using Zambian ECZ standard
     */
    public function getGradeLetter(float $percentage): string
    {
        $gradeA = $this->grade_a_min ?? 80;
        $gradeB = $this->grade_b_min ?? 65;
        $gradeC = $this->grade_c_min ?? 50;
        $gradeD = $this->grade_d_min ?? 40;

        return match (true) {
            $percentage >= $gradeA => 'A',
            $percentage >= $gradeB => 'B',
            $percentage >= $gradeC => 'C',
            $percentage >= $gradeD => 'D',
            default => 'E',
        };
    }

    /**
     * Get grade remark based on percentage using Zambian ECZ standard
     */
    public function getGradeRemark(float $percentage): string
    {
        $gradeA = $this->grade_a_min ?? 80;
        $gradeB = $this->grade_b_min ?? 65;
        $gradeC = $this->grade_c_min ?? 50;
        $gradeD = $this->grade_d_min ?? 40;

        return match (true) {
            $percentage >= $gradeA => $this->grade_a_remark ?? 'Distinction',
            $percentage >= $gradeB => $this->grade_b_remark ?? 'Merit',
            $percentage >= $gradeC => $this->grade_c_remark ?? 'Credit',
            $percentage >= $gradeD => $this->grade_d_remark ?? 'Pass',
            default => $this->grade_e_remark ?? 'Fail',
        };
    }

    /**
     * Get full grading scale as array
     */
    public function getGradingScale(): array
    {
        return [
            'A' => [
                'min' => $this->grade_a_min ?? 80,
                'max' => 100,
                'remark' => $this->grade_a_remark ?? 'Distinction',
                'description' => 'Excellent performance',
            ],
            'B' => [
                'min' => $this->grade_b_min ?? 65,
                'max' => ($this->grade_a_min ?? 80) - 1,
                'remark' => $this->grade_b_remark ?? 'Merit',
                'description' => 'Very good performance',
            ],
            'C' => [
                'min' => $this->grade_c_min ?? 50,
                'max' => ($this->grade_b_min ?? 65) - 1,
                'remark' => $this->grade_c_remark ?? 'Credit',
                'description' => 'Good performance',
            ],
            'D' => [
                'min' => $this->grade_d_min ?? 40,
                'max' => ($this->grade_c_min ?? 50) - 1,
                'remark' => $this->grade_d_remark ?? 'Pass',
                'description' => 'Satisfactory performance',
            ],
            'E' => [
                'min' => $this->grade_e_min ?? 0,
                'max' => ($this->grade_d_min ?? 40) - 1,
                'remark' => $this->grade_e_remark ?? 'Fail',
                'description' => 'Below passing mark',
            ],
        ];
    }

    /**
     * Check if a mark is passing
     */
    public function isPassing(float $percentage): bool
    {
        return $percentage >= ($this->grade_d_min ?? 40);
    }

    /**
     * Get calculated CA and Exam weights
     */
    public function getAssessmentWeights(): array
    {
        if (!$this->enable_continuous_assessment) {
            return ['ca' => 0, 'exam' => 100];
        }

        return [
            'ca' => $this->ca_weight_percentage ?? 40,
            'exam' => $this->exam_weight_percentage ?? 60,
        ];
    }

    /**
     * Get the logo URL for different contexts
     */
    public function getLogoUrl(string $type = 'default'): ?string
    {
        $logo = match ($type) {
            'header' => $this->header_logo ?? $this->school_logo,
            'footer' => $this->footer_logo ?? $this->school_logo,
            'report_card' => $this->report_card_logo ?? $this->school_logo,
            'favicon' => $this->favicon,
            default => $this->school_logo,
        };

        if (!$logo) {
            return null;
        }

        return asset('storage/' . $logo);
    }

    /**
     * Get bank account details for display
     */
    public function getBankDetailsFormatted(): string
    {
        if (!$this->bank_details) {
            return 'No bank details configured';
        }

        $details = $this->bank_details;

        return implode("\n", array_filter([
            $details['bank_name'] ?? null,
            $details['account_name'] ?? null,
            'Account: ' . ($details['account_number'] ?? 'N/A'),
            'Branch: ' . ($details['branch'] ?? 'N/A'),
            $details['swift_code'] ?? null ? 'SWIFT: ' . $details['swift_code'] : null,
        ]));
    }

    /**
     * Get mobile money details for display
     */
    public function getMobileMoneyFormatted(): string
    {
        if (!$this->mobile_money_details) {
            return 'No mobile money details configured';
        }

        $details = $this->mobile_money_details;

        return implode("\n", array_filter([
            $details['provider'] ?? null,
            $details['name'] ?? null,
            $details['number'] ?? null,
        ]));
    }

    /**
     * Check if the school is in maintenance mode
     */
    public function isInMaintenance(): bool
    {
        return $this->enable_maintenance_mode ?? false;
    }

    /**
     * Get portal status
     */
    public function getPortalStatus(): array
    {
        return [
            'student' => $this->enable_student_portal ?? true,
            'parent' => $this->enable_parent_portal ?? true,
            'teacher' => $this->enable_teacher_portal ?? true,
        ];
    }

    /**
     * Format a date according to settings
     */
    public function formatDate($date): string
    {
        if (!$date) {
            return '';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format($this->date_format ?? 'd/m/Y');
    }

    /**
     * Format a datetime according to settings
     */
    public function formatDateTime($datetime): string
    {
        if (!$datetime) {
            return '';
        }

        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->format($this->datetime_format ?? 'd/m/Y H:i');
    }

    /**
     * Get CSS variables for theming
     */
    public function getCssVariables(): string
    {
        return ":root {
            --primary-color: {$this->primary_color};
            --secondary-color: {$this->secondary_color};
            --accent-color: {$this->accent_color};
        }";
    }
}
