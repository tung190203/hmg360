<?php

namespace App\Console\Commands\Tenant;

use App\Core\Module\Module;
use App\Core\Permission\Permission;
use App\Core\Permission\Role;
use App\Core\Tenant\Tenant;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleCreateCommand extends Command
{
    protected $signature = 'tenant:module-create {tenant} {name} {--slug=} {--path=}';

    protected $description = 'Create a tenant module folder and register it in the platform database.';

    public function handle(Filesystem $files): int
    {
        $tenant = Tenant::where('slug', $this->argument('tenant'))->firstOrFail();
        $name = $this->argument('name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $path = $this->option('path') ?: 'Tenants/' . Str::studly($tenant->slug) . '/' . Str::studly($name);
        $fullPath = app_path('TenantModules/' . trim($path, '/'));
        $routeName = 'tenant.' . str_replace('-', '_', $tenant->slug) . '.' . str_replace('-', '_', $slug) . '.index';

        foreach (['Http/Controllers', 'Http/Requests', 'Models', 'Services', 'Repositories', 'Resources/views', 'Database/migrations'] as $directory) {
            $files->ensureDirectoryExists($fullPath . '/' . $directory);
        }

        if (! $files->exists($fullPath . '/module.php')) {
            $viewNamespace = 'tenant-' . $tenant->slug . '-' . $slug;
            $files->put($fullPath . '/module.php', "<?php\n\nreturn [\n    'name' => '{$name}',\n    'slug' => '{$slug}',\n    'view_namespace' => '{$viewNamespace}',\n    'menu' => [\n        'title' => '{$name}',\n        'icon' => 'fas fa-cube',\n        'route' => '{$routeName}',\n    ],\n    'permissions' => ['view', 'create', 'update', 'delete'],\n];\n");
        }

        if (! $files->exists($fullPath . '/routes.php')) {
            $middlewarePath = trim($path, '/');
            $prefix = 'backend/tenants/' . $tenant->slug . '/modules/' . $slug;
            $files->put($fullPath . '/routes.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:{$slug},{$middlewarePath}'])\n    ->prefix('{$prefix}')\n    ->name('tenant." . str_replace('-', '_', $tenant->slug) . "." . str_replace('-', '_', $slug) . ".')\n    ->group(function () {\n        Route::get('/', fn () => view('{$viewNamespace}::index'))->name('index');\n    });\n");
        }

        if (! $files->exists($fullPath . '/Resources/views/index.blade.php')) {
            $files->put($fullPath . '/Resources/views/index.blade.php', "@extends('backend.index')\n\n@section('title', '{$name}')\n\n@section('content')\n<div class=\"container-fluid\">\n    <div class=\"card\">\n        <div class=\"card-body\">\n            {$name}\n        </div>\n    </div>\n</div>\n@endsection\n");
        }

        Module::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => $slug],
            ['name' => $name, 'path' => trim($path, '/'), 'is_enabled' => true, 'sort_order' => 0],
        );

        $permissionIds = [];
        foreach (['view', 'create', 'update', 'delete'] as $permission) {
            $permissionIds[] = Permission::firstOrCreate([
                'module' => $slug,
                'permission' => $permission,
            ])->id;
        }

        Role::where('tenant_id', $tenant->id)
            ->where('slug', 'organizer')
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($permissionIds));

        $this->info("Module ready: {$tenant->slug}/{$slug} at app/TenantModules/{$path}");

        return self::SUCCESS;
    }
}
