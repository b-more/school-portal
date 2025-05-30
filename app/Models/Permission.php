<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'module',
        'create',
        'read',
        'update',
        'delete'
    ];

    public function permission()
    {
        return $this->belongsTo(Role::class);
    }

}
