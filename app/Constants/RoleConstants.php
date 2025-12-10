<?php

namespace App\Constants;

class RoleConstants
{
    public const ADMIN = 1;
    public const TEACHER = 2;
    public const STUDENT = 3;
    public const PARENT = 4;
    public const ACCOUNTANT = 5;
    public const NURSE = 6;
    public const LIBRARIAN = 7;
    public const SECURITY = 8;
    public const SUPPORT = 9;
    public const CLINICIAN = 10;
    public const DIRECTOR = 11;
    public const DEAN_OF_PRIMARY = 12;
    public const DEAN_OF_SECONDARY = 13;
    public const DRIVER = 14;

    /**
     * Get all role IDs
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::TEACHER,
            self::STUDENT,
            self::PARENT,
            self::ACCOUNTANT,
            self::NURSE,
            self::LIBRARIAN,
            self::SECURITY,
            self::SUPPORT,
            self::CLINICIAN,
            self::DIRECTOR,
            self::DEAN_OF_PRIMARY,
            self::DEAN_OF_SECONDARY,
            self::DRIVER,
        ];
    }

    /**
     * Get staff role IDs
     */
    public static function staff(): array
    {
        return [
            self::ADMIN,
            self::TEACHER,
            self::ACCOUNTANT,
            self::NURSE,
            self::LIBRARIAN,
            self::SECURITY,
            self::SUPPORT,
            self::CLINICIAN,
            self::DIRECTOR,
            self::DEAN_OF_PRIMARY,
            self::DEAN_OF_SECONDARY,
            self::DRIVER,
        ];
    }

    /**
     * Get teaching role IDs (excluding Admin)
     */
    public static function teaching(): array
    {
        return [
            self::TEACHER,
            self::DEAN_OF_PRIMARY,
            self::DEAN_OF_SECONDARY,
        ];
    }

    /**
     * Get teaching role IDs including Admin
     */
    public static function teachingWithAdmin(): array
    {
        return [
            self::ADMIN,
            self::TEACHER,
            self::DEAN_OF_PRIMARY,
            self::DEAN_OF_SECONDARY,
        ];
    }

    /**
     * Get financial role IDs
     */
    public static function financial(): array
    {
        return [
            self::ADMIN,
            self::ACCOUNTANT,
        ];
    }

    /**
     * Get management role IDs
     */
    public static function management(): array
    {
        return [
            self::ADMIN,
            self::DIRECTOR,
            self::DEAN_OF_PRIMARY,
            self::DEAN_OF_SECONDARY,
        ];
    }

    /**
     * Get medical role IDs
     */
    public static function medical(): array
    {
        return [
            self::NURSE,
            self::CLINICIAN,
        ];
    }
}
