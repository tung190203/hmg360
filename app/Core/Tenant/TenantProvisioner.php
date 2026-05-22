<?php

namespace App\Core\Tenant;

use App\Core\Permission\Permission;
use App\Core\Permission\Role;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantProvisioner
{
    public function __construct(private readonly Filesystem $files)
    {
    }

    public function provision(array $data, ?Tenant $tenant = null): Tenant
    {
        $tenant ??= new Tenant();

        $tenant->fill([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'status' => $data['status'] ?? Tenant::STATUS_ACTIVE,
        ])->save();

        $this->ensureTenantModuleNamespace($tenant);
        $this->updateDatabase($tenant, $data['database']);

        if (! empty($data['create_organizer']) && ! empty($data['organizer']['email'])) {
            $this->createOrUpdateOrganizer($tenant, $data['organizer']);
        }

        return $tenant->refresh();
    }

    public function ensureTenantModuleNamespace(Tenant $tenant): void
    {
        $tenantModulePath = app_path('TenantModules/Tenants/' . Str::studly($tenant->slug));

        $this->files->ensureDirectoryExists($tenantModulePath);

        if (! $this->files->exists($tenantModulePath . '/.gitkeep')) {
            $this->files->put($tenantModulePath . '/.gitkeep', '');
        }
    }

    private function updateDatabase(Tenant $tenant, array $database): void
    {
        $clearPassword = ! empty($database['clear_password']);
        unset($database['clear_password']);

        if ($clearPassword) {
            $database['password'] = null;
        } elseif (blank($database['password'] ?? null) && $tenant->database) {
            unset($database['password']);
        }

        $tenant->database()->updateOrCreate(['tenant_id' => $tenant->id], $database);
    }

    private function createOrUpdateOrganizer(Tenant $tenant, array $organizer): User
    {
        $existingUser = User::where('email', $organizer['email'])->first();

        if ($existingUser && (
            ($existingUser->tenant_id !== null && (int) $existingUser->tenant_id !== (int) $tenant->id)
            || $existingUser->isPlatformOwner()
        )) {
            throw ValidationException::withMessages([
                'organizer.email' => 'Email organizer đã thuộc platform owner hoặc tenant khác.',
            ]);
        }

        $role = Role::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'organizer'],
            ['name' => 'Organizer'],
        );

        $role->permissions()->sync($this->tenantModulePermissionIds($tenant));

        return User::updateOrCreate(
            ['email' => $organizer['email']],
            [
                'tenant_id' => $tenant->id,
                'role_id' => $role->id,
                'name' => $organizer['name'] ?? 'Organizer',
                'password' => Hash::make($organizer['password']),
                'status' => User::STATUS_ACTIVE,
                'is_platform_owner' => false,
            ],
        );
    }

    private function tenantModulePermissionIds(Tenant $tenant): array
    {
        $moduleSlugs = $tenant->modules()->pluck('slug')->all();

        if (empty($moduleSlugs)) {
            return [];
        }

        return Permission::whereIn('module', $moduleSlugs)->pluck('id')->all();
    }
}
