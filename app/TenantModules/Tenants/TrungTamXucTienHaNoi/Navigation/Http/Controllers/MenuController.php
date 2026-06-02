<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Navigation\Http\Controllers;

use App\Libs\DataGrid;
use App\Libs\Util;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Menu;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Category;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Group;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;

class MenuController extends Controller
{
    private Menu $menu;

    public array $types = [
        'top' => 'Menu top',
        'main' => 'Menu chính',
        'footer' => 'Menu footer',
    ];

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
        $this->selectedMainMenu = 'menu';

        parent::__construct();

        if (!Gate::allows('menu')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $language = App::getLocale();
        $menu_type = $request->get('type', '');

        if ($menu_type !== '') {
            session(['menu_type' => $menu_type]);
        }
        $menu_type = session('menu_type', 'main');
        $parent_id = $request->get('parent_id', 0);
        $menu_raw = $this->menu->where('type', $menu_type)
            ->visibleFor(auth('web')->user())
            ->orderBy('priority')->get();
        $user = auth('web')->user();

        $scope = $user->getScope('menu');
        if (!empty($scope)) {
            $menu_raw = $menu_raw->whereIn('id', $scope);
        }
        $menus = $this->menu->showMenus($menu_raw);
        $option_positions = Util::makeHTMLOptions($this->types, $menu_type, 0, 0, 0);

        $route_name = 'tenant.trung_tam_xuc_tien_ha_noi.menu.edit';

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit($route_name);
        // $clsDataGrid->addColumnLabel("name", "Tiêu đề", "width='15%' nowrap");
        $clsDataGrid->addColumnLabel("name", "Tiêu đề", "width='10%' nowrap", 1, '', function ($col, $val, $id, $row) {
            $html = $row->name;
    
            // Hiển thị nhãn trạng thái
            if ($row->is_draft) {
                $html .= " <span class='badge bg-warning'>Bản chỉnh sửa</span>";
            }
    
            // Hiển thị trạng thái duyệt
            if ($row->status_approve === 'pending') {
                if ($row->approval_level == 0) $html .= " <span class='badge bg-secondary'>Chờ duyệt cấp 1</span>";
                elseif ($row->approval_level == 1) $html .= " <span class='badge bg-primary'>Chờ duyệt cấp 2</span>";
            } elseif ($row->status_approve === 'approved') {
                $html .= " <span class='badge bg-success'>Đã duyệt</span>";
            } elseif ($row->status_approve === 'rejected') {
                $html .= " <span class='badge bg-danger'>Từ chối</span>";
            }
    
            return $html;
        });
        $clsDataGrid->addColumnLabel("slug", "Slug", "width='20%' nowrap");
        $clsDataGrid->addColumnSelect("status", "Hiển thị", "width='5%' align='center'", ["Không", "Có"]);
        $clsDataGrid->addColumnText("priority", "STT", "width='5%' align='center'");

        $dataGrid = $clsDataGrid->showDataGrid($menus);

        return view('ttxt-content::menu.index', compact('menus', 'parent_id', 'option_positions', 'dataGrid'));
    }

    public function saveDataIndex(Request $request)
    {
        // foreach($request->ids as $id) {
        //     $p = Menu::find($id);
        //     if(!Gate::allows('menu/edit', $p)) {
        //         abort(403);
        //     }
        // }

        $update = $request->get('update', []);
        foreach ($update as $key => $value) {
            Menu::where('id', $key)->update($value);
        }
        return redirect()->route('tenant.trung_tam_xuc_tien_ha_noi.menu.index')->with('success', 'Cập nhật thông tin thành công');
    }

    public function edit(Request $request, Menu $menu)
    {
        if($menu->exists && !Gate::allows('menu/edit', $menu)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$menu->exists && !Gate::allows('menu/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $parent_id = $request->get('parent_id', 0);
        $menu_type = session('menu_type', 'main');
        $option_categories = Category::makeListCategory(0, -1, $menu->cat_id, true);
        $option_menu = Menu::makeListMenu(0, $menu_type, $menu->parent_id, true);
        return view('ttxt-content::menu.create',
            compact('menu', 'option_menu', 'option_categories', 'parent_id'));
    }

    public function save(Menu $menu, Request $request)
    {
        $user = auth('web')->user();
        if($menu->exists && !Gate::allows('menu/edit', $menu)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$menu->exists && !Gate::allows('menu/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        // --- Validation for translatable fields ---
        $validationRules = [];
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
        foreach (array_keys($locales) as $locale) {
            // Chỉ bắt buộc trường 'name' cho ngôn ngữ đầu tiên (thường là 'vi')
            if ($locale === array_key_first($locales)) {
                $validationRules["name.{$locale}"] = 'required|string';
            } else {
                $validationRules["name.{$locale}"] = 'nullable|string';
            }
        }
        
        $validated = $request->validate($validationRules);

        try {
            $data = $request->only('name');
            if (!$menu->exists) {
                $menu->approval_level = $user->is_super_admin ? 2 : ($user->is_approve ? 1 : 0);
                $menu->max_approval = 2;
                $menu->is_draft = false;
                $menu->status_approve = $user->is_super_admin ? 'approved' : 'pending'; // Dùng 'pending' cho tất cả cấp dưới Super Admin
                $menu->status = $user->is_super_admin ? Menu::STATUS_ACTIVE : Menu::STATUS_INACTIVE;
                $menu->language = App::getLocale();
                
                $parent_id = intval($request->get('parent_id', 0));
                if ($menu->exists && $menu->id == $parent_id) {
                    return back()->withInput()->withErrors(['parent_id' => 'Danh mục cha không thể là chính nó']);
                }
                
                // Lưu trường đa ngôn ngữ
                $menu->setTranslations('name', $data['name']);

                // Lưu các trường đơn ngữ
                $menu->image = $request->get('image');
                $menu->page_id = $request->get('page_id');
                $menu->cat_id = $request->get('cat_id');
                $menu->parent_id = $parent_id;
                $menu->priority = $request->get('priority', 999999);
                $menu->status = (int)$request->get('status', 0);
                $menu->is_mega = (int)$request->get('is_mega', 0);
                $menu->type = session('menu_type', 'main');

                $menu->save();

                if (Gate::allows('menu/add')) {
                    $this->addMenuToScope($user, $menu->id);
                }
            } else {
                // 🟦 Super admin merge
                if ($user->is_super_admin) {
                    $mainMenu = $menu->main_id ? Menu::find($menu->main_id) : $menu;

                    // Lưu trường đa ngôn ngữ
                    $mainMenu->setTranslations('name', $data['name']);

                    $mainMenu->approval_level = $mainMenu->max_approval;
                    $mainMenu->status_approve = 'approved';
                    $mainMenu->is_draft = false;
                    $mainMenu->main_id = null;   
                    $parent_id = intval($request->get('parent_id', 0));
                    if ($menu->exists && $menu->id == $parent_id) {
                        return back()->withInput()->withErrors(['parent_id' => 'Danh mục cha không thể là chính nó']);
                    }
                    $mainMenu->image = $request->get('image');
                    $mainMenu->page_id = $request->get('page_id');
                    $mainMenu->cat_id = $request->get('cat_id');
                    $mainMenu->parent_id = $parent_id;
                    $mainMenu->priority = $request->get('priority', 999999);
                    $mainMenu->status = (int)$request->get('status', 0);
                    $mainMenu->is_mega = (int)$request->get('is_mega', 0);
                    $mainMenu->type = session('menu_type', 'main');
                    $mainMenu->save();

                    // Xoá nháp
                    $drafts = Menu::where('main_id', $mainMenu->id)->get();
                    foreach ($drafts as $draft) {
                        $this->removeMenuFromScope($draft->id);
                        $draft->delete();
                    }

                    $menu = $mainMenu;
                } else {
                    // 🟨 Người dùng thường
                    if ($menu->status_approve === 'approved' && !$menu->is_draft) {
                        // Bản chính đã duyệt → tạo bản nháp
                        $draft = $menu->replicate();
                        $draft->setTranslations('name', $menu->getTranslations('name'));

                        $draft->is_draft = true;
                        $draft->status_approve = 'pending';
                        $draft->approval_level = $user->is_approve ? 1 : 0;
                        $draft->main_id = $menu->id;
                        $draft->save();
                        $draft->setTranslations('name', $data['name']);
                        $draft->save();

                        if (Gate::allows('menu/add')) {
                            $this->addMenuToScope($user, $draft->id);
                        }

                        $menu = $draft;
                    } else {
                        $menu->setTranslations('name', $data['name']);
                        $menu->status_approve = 'pending';
                        $menu->approval_level = $user->is_approve ? 1 : 0;
                        $menu->save();
                    }
                }
            }

            return redirect()
                ->route('tenant.trung_tam_xuc_tien_ha_noi.menu.edit', $menu)
                ->with('success', 'Lưu dữ liệu thành công ' . (
                    $user->is_super_admin ? '(Đã duyệt)' : ($user->is_approve ? '(Chờ duyệt cấp 2)' : '')
                ));
        } catch (\Exception $ex) {
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }
    public function approve(Menu $menu)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền duyệt dự án.');
        }
    
        if ($user->is_super_admin) {
            $menu->approval_level = $menu->max_approval;
            $menu->status_approve = 'approved';
            $menu->is_draft = false;
    
            if ($menu->main_id) {
                $parent = Menu::find($menu->main_id);
                if ($parent) {
                    $draftData = $menu->toArray();
                    $this->removeMenuFromScope($menu->id);
                    $menu->delete();

                    $parent->fill($draftData);

                    $parent->main_id = null;
                    $parent->is_draft = false;
                    $parent->status_approve = 'approved';
                    $parent->approval_level = $parent->max_approval;
                    $parent->save();

                    $menu = $parent;
                }
            }
        } elseif ($user->is_approve) {
            if ($menu->approval_level < 1) {
                $menu->approval_level = 1;
                $menu->status_approve = 'pending';
            }
        }
    
        $menu->save();

        return redirect()
            ->route('tenant.trung_tam_xuc_tien_ha_noi.menu.edit', ['menu' => $menu->id])
            ->with('success', 'Duyệt menu thành công ' . ($user->is_super_admin ? '(Đã duyệt)' : '(Chờ duyệt cấp 2)'));
    }

    public function reject(Menu $menu)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền từ chối menu.');
        }

        // $menu->delete();
        $menu->status_approve = 'rejected';
        $menu->save();

        return redirect()
            ->route('tenant.trung_tam_xuc_tien_ha_noi.menu.index')
            ->with('success', 'Từ chối duyệt menu thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('menu/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->menu->destroy($id);
        return redirect()->route('tenant.trung_tam_xuc_tien_ha_noi.menu.index')->with('success', 'Xóa menu thành công');
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('menu/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $request->validate(['ids' => 'required|array']);

        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->responseJsonBadRequest();
        }

        $this->menu->destroy($ids);
        return $this->responseJsonOk();
    }

    protected function addMenuToScope($user, $menuId)
    {
        $group = Group::find($user->group_id);
        if (!$group) return;

        $scopeData = $group->scope_data ?? [];
        $resource = 'menu';

        if (empty($scopeData[$resource])) {
            return;
        }

        if (!in_array((string)$menuId, $scopeData[$resource])) {
            $scopeData[$resource][] = (string)$menuId;
            $group->scope_data = $scopeData;
            $group->save();
        }
    }

    protected function removeMenuFromScope($menuId)
    {
        $groups = Group::whereJsonContains('scope_data->menu', (string)$menuId)->get();

        foreach ($groups as $group) {
            $scopeData = $group->scope_data ?? [];
    
            if (!isset($scopeData['menu']) || !is_array($scopeData['menu'])) {
                continue;
            }

            if (empty($scopeData['menu'])) {
                continue;
            }

            $scopeData['menu'] = array_values(array_filter(
                $scopeData['menu'],
                fn($id) => (string)$id !== (string)$menuId
            ));
    
            $group->scope_data = $scopeData;
            $group->save();
        }
    }
}

