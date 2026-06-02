<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\User\Http\Controllers;

use App\Libs\DataGrid;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->selectedMainMenu = 'user';
        $this->selectedSubMenu('group');

        parent::__construct();

        if (!Gate::allows('group')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $query = $this->group->orderBy('id', 'desc');

        $groups = $query->paginate(20);

        $paginate = 20;
        $route_name = 'backend_group_edit';
        $option_column_button = Group::makeOptionColumnButton();

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit($route_name);
        $clsDataGrid->addColumnLabel("name", "Name", "nowrap");
        $clsDataGrid->addColumnDate("created_at", "Ngày tạo", "width='15%' nowrap ", 'd-m-Y');
        $clsDataGrid->addColumnButton('id', '&nbsp', $option_column_button, "width='5%' nowrap ");

        $dataGrid = $clsDataGrid->showDataGrid($groups, $paginate, $groups->total());

        return view('ttxt-website::group.index', compact('groups', 'dataGrid'));
    }

    public function edit(Group $group)
    {
        if (!Gate::allows('group/' . ($group->exists ? 'edit' : 'add'))) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $permission_configs = $this->getPermissionConfigs();

        $category = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Category::get(['id', 'name']);
        $projects = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Project::get(['id', 'name']);
        $posts = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Post::get(['id', 'name']);
        $investment_guides = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\InvestmentGuide::get(['id', 'name']);
        $menus = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Menu::get(['id', 'name']);
        $popups = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Popup::get(['id', 'image']);
        $users = \App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\User::get(['id', 'name']);

        return view('ttxt-website::group.create', compact(
            'group',
            'permission_configs',
            'category',
            'projects',
            'posts',
            'investment_guides',
            'menus',
            'popups',
            'users'
        ));
    }

    public function save(Group $group, Request $request)
    {
        if (!Gate::allows('group/' . ($group->exists ? 'edit' : 'add'))) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $request->validate([
            'name' => ['required', 'string', Rule::unique(Group::class, 'name')->ignore($group->id)],
            'permission' => 'required|array',
        ]);

        $permission_configs = $this->getPermissionConfigs();
        $permission_data = $this->_processPermission([], $permission_configs, $request->get('permission'));

        $group->name = strip_tags($request->get('name'));
        $group->permission_data = $permission_data;

        $scopePermissionMap = [
            'project' => 'project',
            'post' => 'post',
            'investment_guide' => 'investment_guide',
            'popup' => 'popup',
            'user' => 'user',
            'group' => 'group',
            'setting' => 'setting',
            'category' => 'category',
            'menu' => 'menu',
            'file_manager' => 'file_manager',
        ];

        $scope_data = [];

        foreach ($scopePermissionMap as $scopeKey => $permissionKey) {
            if (in_array($permissionKey, $permission_data)) {
                $scope_data[$scopeKey] = $request->get('scope_data_' . $scopeKey, []);
            } else {
                $scope_data[$scopeKey] = [];
            }
        }

        $group->scope_data = $scope_data;
        $group->save();

        return redirect()->route('backend_group_edit', $group)->with('success', 'Cập nhật thành công');
    }

    protected function _processPermission($permission_data, $permission_map, $permission_input, $prefix = '')
    {
        foreach ($permission_map as $perm_key => $perm_data) {
            if (!isset($permission_input[$perm_key])) {
                continue;
            }

            $permission_data[] = $prefix . $perm_key;

            if (is_array($perm_data) && !empty($perm_data['items']) && is_array($perm_data['items']) && count($perm_data['items']) > 0) {
                $permission_data = $this->_processPermission($permission_data, $perm_data['items'], $permission_input[$perm_key], $prefix . $perm_key . '/');
            }
        }

        return $permission_data;
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('group/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $this->group->destroy($id);
        return redirect()->to(route('backend_group'))->with('success', 'Xóa thành công');
    }

    protected function getPermissionConfigs()
    {
        $permission_configs = config('permission', []);

        $tenant = auth()->user()?->tenant;
        if ($tenant) {
            $modules = \App\Core\Module\Module::where('tenant_id', $tenant->id)
                ->where('is_enabled', true)
                ->get();

            $actionLabels = [
                'view' => 'Xem',
                'create' => 'Thêm',
                'update' => 'Sửa',
                'delete' => 'Xóa',
                'toggle' => 'Bật/tắt',
            ];

            foreach ($modules as $module) {
                if (isset($permission_configs[$module->slug])) {
                    continue;
                }

                $manifest = $module->manifest();
                $permissions = $manifest['permissions'] ?? [];

                if (empty($permissions)) {
                    continue;
                }

                $items = [];
                foreach ($permissions as $perm) {
                    $items[$perm] = $actionLabels[$perm] ?? ucfirst($perm);
                }

                $label = $manifest['name'] ?? $module->name;

                $permission_configs[$module->slug] = [
                    'label' => $label,
                    'items' => $items,
                    'super_admin_only' => false,
                ];
            }
        }

        return $permission_configs;
    }
}
