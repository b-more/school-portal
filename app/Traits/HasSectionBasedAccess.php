<?php

namespace App\Traits;

use App\Constants\RoleConstants;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

trait HasSectionBasedAccess
{
    /**
     * Get the user's school section based on their role or teacher record
     */
    public function getUserSection(?User $user = null): ?SchoolSection
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        // Check if user has a section-specific role
        $sectionCode = RoleConstants::getSectionForRole($user->role_id);

        if ($sectionCode) {
            return SchoolSection::where('code', $sectionCode)->first();
        }

        // Check if user has a teacher record with a section
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher && $teacher->school_section_id) {
            return $teacher->schoolSection;
        }

        return null;
    }

    /**
     * Get the section code for the current user
     */
    public function getUserSectionCode(?User $user = null): ?string
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        // Check role-based section first
        $sectionCode = RoleConstants::getSectionForRole($user->role_id);

        if ($sectionCode) {
            return $sectionCode;
        }

        // Check teacher record
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->getSectionCode();
        }

        return null;
    }

    /**
     * Check if user should bypass teacher filter (has section-wide or school-wide access)
     */
    public function shouldBypassTeacherFilter(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Admin always bypasses
        if ($user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Section heads bypass for their section
        if (RoleConstants::isSectionHead($user->role_id)) {
            return true;
        }

        // Deans bypass for their section
        if (in_array($user->role_id, [RoleConstants::DEAN_OF_PRIMARY, RoleConstants::DEAN_OF_SECONDARY])) {
            return true;
        }

        // Director bypasses all
        if ($user->role_id === RoleConstants::DIRECTOR) {
            return true;
        }

        // Check if teacher has a designation that grants access
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            // Senior teachers and deans have extended access
            if ($teacher->isSeniorTeacher() || $teacher->isDeanOfTeachers()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get access level for user
     * Returns: full, section_full, section_no_settings, extended, assigned_only
     */
    public function getAccessLevel(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return 'none';
        }

        // Admin has full access
        if ($user->role_id === RoleConstants::ADMIN) {
            return 'full';
        }

        // Director has full view access
        if ($user->role_id === RoleConstants::DIRECTOR) {
            return 'full';
        }

        // Head Teachers have full section access with settings
        if (RoleConstants::isHeadTeacher($user->role_id)) {
            return 'section_full';
        }

        // Deputy Head Teachers have full section access without settings
        if (RoleConstants::isDeputyHeadTeacher($user->role_id)) {
            return 'section_no_settings';
        }

        // Deans have extended access within their section
        if (in_array($user->role_id, [RoleConstants::DEAN_OF_PRIMARY, RoleConstants::DEAN_OF_SECONDARY])) {
            return 'extended';
        }

        // Check teacher designations
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            if ($teacher->isDeanOfTeachers()) {
                return 'extended';
            }

            if ($teacher->isSeniorTeacher()) {
                return 'extended';
            }
        }

        // Default: only assigned classes
        return 'assigned_only';
    }

    /**
     * Filter students by user's section
     */
    public function filterStudentsBySection($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        $accessLevel = $this->getAccessLevel($user);

        // Full access - no filter
        if ($accessLevel === 'full') {
            return $query;
        }

        $section = $this->getUserSection($user);

        // Section-based access (head teachers, deputies, deans)
        if (in_array($accessLevel, ['section_full', 'section_no_settings', 'extended'])) {
            if ($section) {
                // Get grades in the section
                $gradeIds = Grade::where('school_section_id', $section->id)->pluck('id');
                // Get class sections in those grades
                $classSectionIds = ClassSection::whereIn('grade_id', $gradeIds)->pluck('id');

                return $query->whereIn('class_section_id', $classSectionIds);
            }
        }

        // Assigned only - filter by teacher's assigned classes
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            $classSectionIds = $teacher->subjectTeachings()
                ->pluck('class_section_id')
                ->unique()
                ->toArray();

            // Include class teacher assignment
            if ($teacher->is_class_teacher && $teacher->class_section_id) {
                $classSectionIds[] = $teacher->class_section_id;
            }

            return $query->whereIn('class_section_id', array_unique($classSectionIds));
        }

        // No access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Filter teachers by user's section
     */
    public function filterTeachersBySection($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        $accessLevel = $this->getAccessLevel($user);

        // Full access - no filter
        if ($accessLevel === 'full') {
            return $query;
        }

        $section = $this->getUserSection($user);

        // Section-based access
        if (in_array($accessLevel, ['section_full', 'section_no_settings', 'extended']) && $section) {
            return $query->where('school_section_id', $section->id);
        }

        // Assigned only - only show own record
        return $query->where('user_id', $user->id);
    }

    /**
     * Filter class sections by user's section
     */
    public function filterClassSectionsBySection($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        $accessLevel = $this->getAccessLevel($user);

        // Full access - no filter
        if ($accessLevel === 'full') {
            return $query;
        }

        $section = $this->getUserSection($user);

        // Section-based access
        if (in_array($accessLevel, ['section_full', 'section_no_settings', 'extended']) && $section) {
            $gradeIds = Grade::where('school_section_id', $section->id)->pluck('id');
            return $query->whereIn('grade_id', $gradeIds);
        }

        // Assigned only - filter by teacher's assigned classes
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            $classSectionIds = $teacher->subjectTeachings()
                ->pluck('class_section_id')
                ->unique()
                ->toArray();

            if ($teacher->is_class_teacher && $teacher->class_section_id) {
                $classSectionIds[] = $teacher->class_section_id;
            }

            return $query->whereIn('id', array_unique($classSectionIds));
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Filter grades by user's section
     */
    public function filterGradesBySection($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        $accessLevel = $this->getAccessLevel($user);

        // Full access - no filter
        if ($accessLevel === 'full') {
            return $query;
        }

        $section = $this->getUserSection($user);

        // Section-based access
        if (in_array($accessLevel, ['section_full', 'section_no_settings', 'extended']) && $section) {
            return $query->where('school_section_id', $section->id);
        }

        // For teachers with assigned only access, get grades of their assigned classes
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            $classSectionIds = $teacher->subjectTeachings()
                ->pluck('class_section_id')
                ->unique()
                ->toArray();

            if ($teacher->is_class_teacher && $teacher->class_section_id) {
                $classSectionIds[] = $teacher->class_section_id;
            }

            $gradeIds = ClassSection::whereIn('id', $classSectionIds)->pluck('grade_id');

            return $query->whereIn('id', $gradeIds);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if user belongs to the same section as a record
     */
    public function belongsToUserSection(?User $user, $record): bool
    {
        $userSection = $this->getUserSection($user);

        if (!$userSection) {
            return false;
        }

        // For students, check their class section's grade's section
        if ($record instanceof Student) {
            if (!$record->classSection || !$record->classSection->grade) {
                return false;
            }

            return $record->classSection->grade->school_section_id === $userSection->id;
        }

        // For teachers, check their school_section_id
        if ($record instanceof Teacher) {
            return $record->school_section_id === $userSection->id;
        }

        // For class sections, check grade's section
        if ($record instanceof ClassSection) {
            if (!$record->grade) {
                return false;
            }

            return $record->grade->school_section_id === $userSection->id;
        }

        // For grades, check directly
        if ($record instanceof Grade) {
            return $record->school_section_id === $userSection->id;
        }

        return false;
    }

    /**
     * Check if current user can access a specific record based on section
     */
    public function canAccessRecord(?User $user, $record): bool
    {
        $accessLevel = $this->getAccessLevel($user);

        // Full access can view everything
        if ($accessLevel === 'full') {
            return true;
        }

        // Section-based access
        if (in_array($accessLevel, ['section_full', 'section_no_settings', 'extended'])) {
            return $this->belongsToUserSection($user, $record);
        }

        // Assigned only - check if teacher has direct access
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return false;
        }

        // For students
        if ($record instanceof Student) {
            return $teacher->canAccessStudent($record);
        }

        // For class sections
        if ($record instanceof ClassSection) {
            return $teacher->teachesInClassSection($record->id) ||
                   ($teacher->is_class_teacher && $teacher->class_section_id === $record->id);
        }

        return false;
    }
}
