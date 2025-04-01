<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable; //HasRoles; //HasApiTokens,  spatie/laravel-permission trait

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

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the student record associated with the user.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the parent/guardian record associated with the user.
     */
    public function parentGuardian()
    {
        return $this->hasOne(ParentGuardian::class);
    }
}
