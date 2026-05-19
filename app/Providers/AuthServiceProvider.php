<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super admin => full quyền
        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        $permissions_configs = config('permission');

        foreach ($permissions_configs as $permission_key => $configs) {
            // Gate cho quyền chính (không có record cụ thể)
            Gate::define($permission_key, function (User $user, $model = null) use ($permission_key) {
                if ($model) {
                    // Nếu có model → check theo scope
                    return $user->canDoOn($permission_key, $model->id ?? null);
                }
                return $user->hasPermission($permission_key);
            });

            // Các quyền cấp con
            if (!empty($configs['items'])) {
                foreach ($configs['items'] as $config_key => $config) {
                    $level1_permission_key = $permission_key . '/' . $config_key;

                    Gate::define($level1_permission_key, function (User $user, $model = null) use ($level1_permission_key) {
                        if ($model) {
                            return $user->canDoOn($level1_permission_key, $model->id ?? null);
                        }
                        return $user->hasPermission($level1_permission_key);
                    });
                }
            }
        }
    }
}
