<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_code',
        'school_motto',
        'school_logo',
        'address',
        'city',
        'state_province',
        'country',
        'postal_code',
        'phone',
        'alternate_phone',
        'email',
        'website',
        'currency_code',
        'timezone',
        'school_head_name',
        'school_head_title',
        'social_media_links',
    ];

    protected $casts = [
        'social_media_links' => 'array',
    ];

    // Singleton pattern - there should only be one school settings record
    public static function getInstance()
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'school_name' => 'School Name',
                'currency_code' => 'ZMW',
                'timezone' => 'Africa/Lusaka',
            ]);
        }

        return $settings;
    }
}
