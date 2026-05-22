<?php

namespace App\Http\Controllers\Backend\Core;

use App\Core\Module\Module;
use App\Core\Permission\Permission;
use App\Core\Permission\Role;
use App\Core\Tenant\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    protected string $selectedMainMenu = 'modules';

    public function index()
    {
        $selectedTenantId = request()->integer('tenant_id') ?: null;
        $modulesQuery = Module::with('tenant')
            ->orderBy('tenant_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($selectedTenantId) {
            $modulesQuery->where('tenant_id', $selectedTenantId);
        }

        $modules = $modulesQuery->get()
            ->filter(fn (Module $module) => is_array($module->config['menu'] ?? null))
            ->values();

        return view('backend.core.modules.index', [
            'modules' => $modules,
            'tenants' => Tenant::orderBy('name')->get(),
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function create()
    {
        return view('backend.core.modules.form', [
            'module' => new Module(['is_enabled' => true, 'sort_order' => 0]),
            'tenants' => Tenant::orderBy('name')->get(),
        ]);
    }

    public function edit(Module $module)
    {
        return view('backend.core.modules.form', [
            'module' => $module,
            'tenants' => Tenant::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $module = new Module();

        return $this->save($request, $module);
    }

    public function update(Request $request, Module $module)
    {
        return $this->save($request, $module);
    }

    public function toggle(Module $module)
    {
        $module->forceFill(['is_enabled' => ! $module->is_enabled])->save();

        return back()->with('success', 'Đã cập nhật trạng thái module.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'module_ids' => ['required', 'array', 'min:1'],
            'module_ids.*' => ['integer', 'distinct', 'exists:modules,id'],
        ]);

        $modules = Module::query()
            ->where('tenant_id', $validated['tenant_id'])
            ->whereIn('id', $validated['module_ids'])
            ->pluck('id')
            ->all();

        if (count($modules) !== count($validated['module_ids'])) {
            return response()->json([
                'message' => 'Danh sách module không hợp lệ cho tenant này.',
            ], 422);
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['module_ids'] as $index => $moduleId) {
                Module::whereKey($moduleId)->update(['sort_order' => ($index + 1) * 10]);
            }
        });

        return response()->json([
            'message' => 'Đã cập nhật thứ tự hiển thị module.',
        ]);
    }

    private function save(Request $request, Module $module)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('modules')->where('tenant_id', $request->integer('tenant_id'))->ignore($module->id),
            ],
            'path' => ['required', 'string', 'max:255'],
            'menu_section' => ['required', Rule::in(['content', 'systems'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $config = is_array($module->config) ? $module->config : [];
        $manifestMenu = $module->manifest()['menu'] ?? [];
        $configuredMenu = is_array($config['menu'] ?? null) ? $config['menu'] : [];
        $config['menu'] = array_replace(
            is_array($manifestMenu) ? $manifestMenu : [],
            $configuredMenu,
            ['section' => $validated['menu_section']]
        );

        $module->fill([
            'tenant_id' => $validated['tenant_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'path' => trim($validated['path'], '/'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_enabled' => $request->boolean('is_enabled'),
            'config' => $config,
        ])->save();

        $permissionIds = [];
        foreach (($module->manifest()['permissions'] ?? ['view', 'create', 'update', 'delete']) as $permission) {
            $permissionIds[] = Permission::firstOrCreate([
                'module' => $module->slug,
                'permission' => $permission,
            ])->id;
        }

        Role::where('tenant_id', $module->tenant_id)
            ->where('slug', 'organizer')
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($permissionIds));

        return redirect()->route('backend_core_modules')->with('success', 'Đã lưu module.');
    }
}
