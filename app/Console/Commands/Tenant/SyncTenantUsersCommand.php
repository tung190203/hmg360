<?php

namespace App\Console\Commands\Tenant;

use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantDatabaseManager;
use App\Models\User as PlatformUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTenantUsersCommand extends Command
{
    protected $signature = 'tenant:sync-users {tenant?}';

    protected $description = 'Sync existing tenant database users to the platform database for authentication.';

    public function handle(TenantDatabaseManager $databaseManager): int
    {
        $tenantSlug = $this->argument('tenant');
        $tenants = $tenantSlug
            ? Tenant::where('slug', $tenantSlug)->get()
            : Tenant::where('status', Tenant::STATUS_ACTIVE)->get();

        if ($tenants->isEmpty()) {
            $this->error($tenantSlug ? "Tenant '{$tenantSlug}' not found." : 'No active tenants found.');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info("Syncing users for tenant: {$tenant->name} ({$tenant->slug})");

            try {
                $databaseManager->connect($tenant);

                $tenantUsers = DB::connection('tenant')->table('users')->get();
                if ($tenantUsers->isEmpty()) {
                    $this->line('No users found in tenant database.');
                    continue;
                }

                $count = 0;
                foreach ($tenantUsers as $tenantUser) {
                    if (empty($tenantUser->email) || empty($tenantUser->password)) {
                        continue;
                    }

                    $status = ($tenantUser->status == 1 || $tenantUser->status === 'active')
                        ? PlatformUser::STATUS_ACTIVE
                        : PlatformUser::STATUS_INACTIVE;

                    PlatformUser::updateOrCreate(
                        ['email' => $tenantUser->email],
                        [
                            'name' => $tenantUser->name ?? $tenantUser->email,
                            'phone' => $tenantUser->phone ?? null,
                            'avatar' => $tenantUser->avatar ?? null,
                            'password' => $tenantUser->password,
                            'status' => $status,
                            'tenant_id' => $tenant->id,
                            'role_id' => null,
                            'is_platform_owner' => false,
                        ]
                    );

                    $count++;
                }

                $this->info("Synced {$count} user(s).");
            } catch (\Throwable $e) {
                $this->error("Failed to sync users for tenant '{$tenant->slug}': {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
