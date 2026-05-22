<?php

namespace App\Models;

use App\Core\Permission\Role;
use App\Core\Tenant\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'tenant_id',
        'role_id',
        'name',
        'email',
        'phone',
        'avatar',
        'email_verified_at',
        'password',
        'status',
        'is_platform_owner',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_owner' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isPlatformOwner(): bool
    {
        return (bool) $this->is_platform_owner;
    }

    public function isTenantOrganizer(): bool
    {
        return ! $this->isPlatformOwner()
            && $this->tenant_id !== null
            && $this->role?->slug === 'organizer';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE || $this->status == 1;
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        if ($this->isPlatformOwner() || $this->isTenantOrganizer()) {
            return true;
        }

        if ($this->tenant_id !== null) {
            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                return $tenantUser && ($tenantUser->id === 1 || !empty($tenantUser->is_super_admin));
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    public function getIsApproveAttribute(): bool
    {
        if ($this->isPlatformOwner() || $this->isTenantOrganizer()) {
            return true;
        }

        if ($this->tenant_id !== null) {
            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                return $tenantUser && !empty($tenantUser->is_approve);
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    public function getScope(string $permissionKey): ?array
    {
        if ($this->isPlatformOwner() || $this->isTenantOrganizer()) {
            return null;
        }

        if ($this->tenant_id !== null) {
            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                if (! $tenantUser || ! $tenantUser->group_id) {
                    return [];
                }

                $group = \Illuminate\Support\Facades\DB::connection('tenant')->table('groups')
                    ->where('id', $tenantUser->group_id)
                    ->first();

                if (! $group) {
                    return [];
                }

                $scopeData = json_decode($group->scope_data ?? '[]', true);
                if (! is_array($scopeData)) {
                    return [];
                }

                $resource = explode('/', str_replace('.', '/', $permissionKey))[0] ?? null;
                if (!$resource) {
                    return [];
                }

                return $scopeData[$resource] ?? [];
            } catch (\Throwable $e) {
                logger()->error('Error getting tenant scope for user ' . $this->email . ': ' . $e->getMessage());
                return [];
            }
        }

        return null;
    }

    public function getScopeData(): array
    {
        return [];
    }

    public function canDoOn(string $permissionKey, ?int $recordId = null): bool
    {
        if ($this->isPlatformOwner() || $this->isTenantOrganizer() || $this->isSuperAdmin()) {
            return true;
        }

        if ($this->tenant_id !== null) {
            if (!$this->hasPermission($permissionKey)) {
                return false;
            }

            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                if (! $tenantUser || ! $tenantUser->group_id) {
                    return false;
                }

                $group = \Illuminate\Support\Facades\DB::connection('tenant')->table('groups')
                    ->where('id', $tenantUser->group_id)
                    ->first();

                if (! $group) {
                    return false;
                }

                $scopeData = json_decode($group->scope_data ?? '[]', true);
                if (! is_array($scopeData)) {
                    return false;
                }

                $module = explode('/', str_replace('.', '/', $permissionKey))[0];

                if (!array_key_exists($module, $scopeData)) {
                    return false;
                }

                $scope = $scopeData[$module];

                if (empty($scope)) {
                    return true;
                }

                if ($recordId !== null) {
                    return in_array($recordId, $scope) || in_array((string)$recordId, $scope);
                }

                return true;
            } catch (\Throwable $e) {
                logger()->error('Error checking tenant scope for user ' . $this->email . ': ' . $e->getMessage());
                return false;
            }
        }

        return $this->hasPermission(str_replace('/', '.', $permissionKey));
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isPlatformOwner()) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->tenant_id !== null && !$this->isTenantOrganizer()) {
            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                if (! $tenantUser || ! $tenantUser->group_id) {
                    return false;
                }

                $group = \Illuminate\Support\Facades\DB::connection('tenant')->table('groups')
                    ->where('id', $tenantUser->group_id)
                    ->first();

                if (! $group) {
                    return false;
                }

                $permissionData = json_decode($group->permission_data ?? '[]', true);
                if (! is_array($permissionData)) {
                    return false;
                }

                $keysToCheck = [];
                $slashKey = str_replace('.', '/', $permissionKey);
                $dotKey = str_replace('/', '.', $permissionKey);
                $keysToCheck[] = $slashKey;
                $keysToCheck[] = $dotKey;

                $parts = explode('/', $slashKey);
                if (count($parts) > 1) {
                    $lastPart = end($parts);
                    $alternateLastPart = null;
                    if ($lastPart === 'add') {
                        $alternateLastPart = 'create';
                    } elseif ($lastPart === 'create') {
                        $alternateLastPart = 'add';
                    } elseif ($lastPart === 'edit') {
                        $alternateLastPart = 'update';
                    } elseif ($lastPart === 'update') {
                        $alternateLastPart = 'edit';
                    }

                    if ($alternateLastPart !== null) {
                        $parts[count($parts) - 1] = $alternateLastPart;
                        $keysToCheck[] = implode('/', $parts);
                        $keysToCheck[] = implode('.', $parts);
                    }
                } elseif (count($parts) === 1 && !empty($parts[0])) {
                    $keysToCheck[] = $parts[0] . '/view';
                    $keysToCheck[] = $parts[0] . '.view';
                }

                foreach ($keysToCheck as $key) {
                    if (in_array($key, $permissionData, true)) {
                        return true;
                    }
                }

                return false;
            } catch (\Throwable $e) {
                logger()->error('Error checking tenant permission for user ' . $this->email . ': ' . $e->getMessage());
                return false;
            }
        }

        if (! $this->role) {
            return false;
        }

        [$module, $permission] = array_pad(explode('.', $permissionKey, 2), 2, null);

        if (! $module || ! $permission) {
            return false;
        }

        return $this->role->permissions()
            ->where('module', $module)
            ->where('permission', $permission)
            ->exists();
    }

    public function getAllPermissionsFromGroup(): array
    {
        if ($this->isPlatformOwner()) {
            return [];
        }

        if ($this->tenant_id !== null && !$this->isTenantOrganizer()) {
            try {
                $tenantUser = \Illuminate\Support\Facades\DB::connection('tenant')->table('users')
                    ->where('email', $this->email)
                    ->first();

                if (! $tenantUser || ! $tenantUser->group_id) {
                    return [];
                }

                $group = \Illuminate\Support\Facades\DB::connection('tenant')->table('groups')
                    ->where('id', $tenantUser->group_id)
                    ->first();

                if (! $group) {
                    return [];
                }

                $permissionData = json_decode($group->permission_data ?? '[]', true);
                return is_array($permissionData) ? $permissionData : [];
            } catch (\Throwable $e) {
                logger()->error('Error getting tenant permissions from group for user ' . $this->email . ': ' . $e->getMessage());
                return [];
            }
        }

        return [];
    }
}
