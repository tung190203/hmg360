<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isPlatformOwner() || $user->isTenantOrganizer()) {
                return true;
            }
            if ($user->tenant_id !== null) {
                return $user->hasPermission($ability);
            }
        });

        foreach (config('permission', []) as $permissionKey => $config) {
            Gate::define($permissionKey, fn (User $user) => $user->hasPermission($permissionKey . '.view'));

            foreach (($config['items'] ?? []) as $itemKey => $label) {
                Gate::define($permissionKey . '.' . $itemKey, function (User $user) use ($permissionKey, $itemKey) {
                    return $user->hasPermission($permissionKey . '.' . $itemKey);
                });
            }
        }
    }
}
