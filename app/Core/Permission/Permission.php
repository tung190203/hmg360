<?php

namespace App\Core\Permission;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'module',
        'permission',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    public function key(): string
    {
        return $this->module . '.' . $this->permission;
    }
}
