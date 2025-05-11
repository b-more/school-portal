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
        ];
    }

    /**
     * Get teaching role IDs
     */
    public static function teaching(): array
    {
        return [
            self::ADMIN,
            self::TEACHER,
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
}
