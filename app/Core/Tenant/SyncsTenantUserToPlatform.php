<?php

namespace App\Core\Tenant;

use App\Models\User as PlatformUser;
use Illuminate\Support\Facades\DB;

trait SyncsTenantUserToPlatform
{
    protected static function bootSyncsTenantUserToPlatform(): void
    {
        static::saved(function ($tenantUser) {
            $tenantId = static::resolveTenantIdForPlatformSync();

            if (! $tenantId) {
                return;
            }

            $email = $tenantUser->email;
            $originalEmail = $tenantUser->getOriginal('email') ?: $email;
            $status = ($tenantUser->status == 1 || $tenantUser->status === 'active')
                ? PlatformUser::STATUS_ACTIVE
                : PlatformUser::STATUS_INACTIVE;

            PlatformUser::updateOrCreate(
                ['email' => $originalEmail],
                [
                    'name' => $tenantUser->name,
                    'phone' => $tenantUser->phone,
                    'avatar' => $tenantUser->avatar,
                    'email' => $email,
                    'password' => $tenantUser->password,
                    'status' => $status,
                    'tenant_id' => $tenantId,
                    'role_id' => null,
                    'is_platform_owner' => false,
                ]
            );
        });

        static::deleted(function ($tenantUser) {
            $tenantId = static::resolveTenantIdForPlatformSync();

            PlatformUser::query()
                ->where('email', $tenantUser->email)
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('is_platform_owner', false)
                ->delete();
        });
    }

    protected static function resolveTenantIdForPlatformSync(): ?int
    {
        $tenantId = auth('web')->user()?->tenant_id;

        if ($tenantId) {
            return (int) $tenantId;
        }

        $databaseName = config('database.connections.tenant.database');

        if (! $databaseName) {
            return null;
        }

        return DB::connection('mysql')
            ->table('tenant_databases')
            ->where('database_name', $databaseName)
            ->value('tenant_id');
    }
}
