<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'custom_permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_permissions' => 'array',
    ];

    /**
     * Get all users that have this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
    public function parents(): HasMany
    {
        return $this->hasMany(Parent::class);
    }
    public function accountants(): HasMany
    {
        return $this->hasMany(Accountant::class);
    }
    public function nurses(): HasMany
    {
        return $this->hasMany(Nurse::class);
    }
    public function librarians(): HasMany
    {
        return $this->hasMany(Librarian::class);
    }
    public function security(): HasMany
    {
        return $this->hasMany(Security::class);
    }
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }
    public function employee(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Check if the role has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->custom_permissions) {
            return false;
        }

        return isset($this->custom_permissions[$permission]) && $this->custom_permissions[$permission] === true;
    }

    /**
     * Get all permissions for this role
     *
     * @return array
     */
    public function getPermissions(): array
    {
        $defaultPermissions = $this->getDefaultPermissions();
        $customPermissions = $this->custom_permissions ?? [];

        return array_merge($defaultPermissions, $customPermissions);
    }

    /**
     * Get default permissions based on role name
     *
     * @return array
     */
    protected function getDefaultPermissions(): array
    {
        // Define default permissions per role
        $defaultPermissions = [
            'Admin' => [
                'manage_users' => true,
                'manage_roles' => true,
                'manage_school_settings' => true,
                'manage_grades' => true,
                'manage_subjects' => true,
                'manage_fees' => true,
                'view_all_students' => true,
                'view_all_reports' => true,
            ],
            'Teacher' => [
                'manage_homework' => true,
                'manage_results' => true,
                'view_assigned_students' => true,
                'create_class_content' => true,
            ],
            'Student' => [
                'view_homework' => true,
                'submit_homework' => true,
                'view_results' => true,
                'view_fees' => true,
            ],
            'Parent' => [
                'view_child_homework' => true,
                'view_child_results' => true,
                'view_child_fees' => true,
                'pay_fees' => true,
            ],
            'Accountant' => [
                'manage_fees' => true,
                'manage_payments' => true,
                'view_financial_reports' => true,
            ],
            'Nurse' => [
                'manage_medical_records' => true,
                'view_student_health' => true,
            ],
            'Librarian' => [
                'manage_library' => true,
                'manage_books' => true,
            ],
            'Security' => [
                'manage_access' => true,
                'view_gates' => true,
            ],
        ];

        return $defaultPermissions[$this->name] ?? [];
    }
}
