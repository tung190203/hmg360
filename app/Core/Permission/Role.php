<?php

namespace App\Core\Permission;

use App\Core\Tenant\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
