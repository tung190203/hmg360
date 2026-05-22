<?php

namespace App\Core\Tenant;

use App\Core\Module\Module;
use App\Core\Permission\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function database()
    {
        return $this->hasOne(TenantDatabase::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
