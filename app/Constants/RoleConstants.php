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

    // Head Teacher Roles
    public const HEAD_TEACHER_PRIMARY = 15;
    public const HEAD_TEACHER_SECONDARY = 16;
    public const DEPUTY_HEAD_PRIMARY = 17;
    public const DEPUTY_HEAD_SECONDARY = 18;

    // Administrative Staff
    public const SCHOOL_SECRETARY = 19;

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
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
            self::SCHOOL_SECRETARY,
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
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
            self::SCHOOL_SECRETARY,
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
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
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
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
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
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
            self::SCHOOL_SECRETARY,
        ];
    }

    /**
     * Get administrative role IDs
     */
    public static function administrative(): array
    {
        return [
            self::ADMIN,
            self::SCHOOL_SECRETARY,
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

    /**
     * Get head teacher role IDs
     */
    public static function headTeachers(): array
    {
        return [
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
        ];
    }

    /**
     * Get deputy head teacher role IDs
     */
    public static function deputyHeadTeachers(): array
    {
        return [
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
        ];
    }

    /**
     * Get all section head role IDs (heads and deputies)
     */
    public static function sectionHeads(): array
    {
        return [
            self::HEAD_TEACHER_PRIMARY,
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEPUTY_HEAD_SECONDARY,
        ];
    }

    /**
     * Get primary section role IDs
     */
    public static function primarySectionRoles(): array
    {
        return [
            self::HEAD_TEACHER_PRIMARY,
            self::DEPUTY_HEAD_PRIMARY,
            self::DEAN_OF_PRIMARY,
        ];
    }

    /**
     * Get secondary section role IDs
     */
    public static function secondarySectionRoles(): array
    {
        return [
            self::HEAD_TEACHER_SECONDARY,
            self::DEPUTY_HEAD_SECONDARY,
            self::DEAN_OF_SECONDARY,
        ];
    }

    /**
     * Get all section management roles
     */
    public static function sectionManagement(): array
    {
        return array_merge(
            self::sectionHeads(),
            [self::DEAN_OF_PRIMARY, self::DEAN_OF_SECONDARY]
        );
    }

    /**
     * Check if role is a head teacher
     */
    public static function isHeadTeacher(int $roleId): bool
    {
        return in_array($roleId, self::headTeachers());
    }

    /**
     * Check if role is a deputy head teacher
     */
    public static function isDeputyHeadTeacher(int $roleId): bool
    {
        return in_array($roleId, self::deputyHeadTeachers());
    }

    /**
     * Check if role is a section head (head or deputy)
     */
    public static function isSectionHead(int $roleId): bool
    {
        return in_array($roleId, self::sectionHeads());
    }

    /**
     * Check if role is for primary section
     */
    public static function isPrimarySectionRole(int $roleId): bool
    {
        return in_array($roleId, self::primarySectionRoles());
    }

    /**
     * Check if role is for secondary section
     */
    public static function isSecondarySectionRole(int $roleId): bool
    {
        return in_array($roleId, self::secondarySectionRoles());
    }

    /**
     * Get section code for role
     */
    public static function getSectionForRole(int $roleId): ?string
    {
        if (self::isPrimarySectionRole($roleId)) {
            return 'PRI';
        }

        if (self::isSecondarySectionRole($roleId)) {
            return 'SEC';
        }

        return null;
    }
}
