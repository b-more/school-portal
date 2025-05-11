<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'username',
        'password',
        'status',              // active, inactive, suspended
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'phone_verified_at',
        'profile_photo_path',
        'settings',            // JSON field for user preferences
        'notes'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Get the role that owns the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the teacher record associated with the user
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the employee record associated with the user
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the student record associated with the user
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the parent/guardian record associated with the user
     */
    public function parentGuardian()
    {
        return $this->hasOne(ParentGuardian::class);
    }

    /**
     * Get all activity logs for this user
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        // If role is passed as array, check if user has any of those roles
        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        // If role is numeric, check role_id
        if (is_numeric($role)) {
            return $this->role_id === (int)$role;
        }

        // If no role relationship, return false
        if (!$this->role) {
            return false;
        }

        // Check role name
        return $this->role->name === $role;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('Teacher');
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->hasRole('Student');
    }

    /**
     * Check if user is a parent
     */
    public function isParent(): bool
    {
        return $this->hasRole('Parent');
    }

    /**
     * Get the primary relationship record based on user's role
     */
    public function primaryRecord()
    {
        switch ($this->role?->name) {
            case 'Teacher':
                return $this->teacher;
            case 'Student':
                return $this->student;
            case 'Parent':
                return $this->parentGuardian;
            default:
                return $this->employee;
        }
    }
}
