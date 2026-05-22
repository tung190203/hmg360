<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\RolesPermissions\Http\Controllers;

use App\Core\Permission\Permission;
use App\Core\Permission\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    protected string $selectedMainMenu = 'tenant_roles';

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        return view('ttxt-roles-permissions::index', [
            'tenant' => $tenant,
            'roles' => Role::with('permissions')
                ->where('tenant_id', $tenant->id)
                ->orderBy('name')
                ->paginate(30),
        ]);
    }

    public function create(Request $request)
    {
        return view('ttxt-roles-permissions::form', [
            'tenant' => $request->user()->tenant,
            'role' => new Role(),
            'permissions' => $this->availablePermissions($request),
        ]);
    }

    public function edit(Request $request, Role $role)
    {
        $this->authorizeTenantRole($request, $role);
        abort_if($role->slug === 'organizer', 403, 'Không cho phép sửa role organizer mặc định.');

        $role->load('permissions');

        return view('ttxt-roles-permissions::form', [
            'tenant' => $request->user()->tenant,
            'role' => $role,
            'permissions' => $this->availablePermissions($request),
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new Role());
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeTenantRole($request, $role);
        abort_if($role->slug === 'organizer', 403, 'Không cho phép sửa role organizer mặc định.');

        return $this->save($request, $role);
    }

    private function save(Request $request, Role $role)
    {
        $tenant = $request->user()->tenant;
        $allowedPermissionIds = $this->availablePermissions($request)->flatten()->pluck('id')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('roles')->where('tenant_id', $tenant->id)->ignore($role->id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::in($allowedPermissionIds)],
        ]);

        $role->fill([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
        ])->save();

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('backend_core_roles')->with('success', 'Đã lưu role tenant.');
    }

    private function availablePermissions(Request $request)
    {
        $tenant = $request->user()->tenant;
        $moduleSlugs = $tenant->modules()
            ->where('is_enabled', true)
            ->pluck('slug')
            ->all();

        if (empty($moduleSlugs)) {
            return collect();
        }

        return Permission::whereIn('module', $moduleSlugs)
            ->orderBy('module')
            ->orderBy('permission')
            ->get()
            ->groupBy('module');
    }

    private function authorizeTenantRole(Request $request, Role $role): void
    {
        abort_unless((int) $role->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
