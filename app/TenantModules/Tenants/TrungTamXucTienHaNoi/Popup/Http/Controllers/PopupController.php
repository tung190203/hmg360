<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Popup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Libs\DataGrid;
use App\Libs\Util;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Group;
use Illuminate\Http\Request;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Popup;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PopupController extends Controller
{
    private Popup $popup;
    public function __construct(Popup $popup)
    {
        $this->popup = $popup;
        $this->selectedMainMenu = 'popup';

        parent::__construct();

        if (!Gate::allows('popup')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }
    public function index(Request $request)
    {
        $paginate = 20;
        $user = auth('web')->user();
        $query = $this->popup
        ->visibleFor($user)
        ->orderBy('id', 'desc');
        $scope = $user->getScope('popup');
        if (!empty($scope)) {
            $query->whereIn('id', $scope);
        }
        $popups = $query->paginate($paginate);
        $this->selectedSubMenu('popup');
        $option_column_button = Popup::makeOptionColumnButton();
        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit('backend_popup_edit');
        $clsDataGrid->addColumnText("link", "Link điều hướng", "width='5%' nowrap ");
        $clsDataGrid->addColumnImage("image", "Hình ảnh", "width='10%' nowrap ", 80);
        $clsDataGrid->addColumnLabel("status_approve", "Trạng thái duyệt", "width='10%' nowrap", 1, '', function ($col, $val, $id, $row) {
            $html = '';
    
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
        $clsDataGrid->addColumnDate("created_at", "Ngày tạo", "width='15%' nowrap ", 'd-m-Y');
        $clsDataGrid->addColumnButton('id', '&nbsp', $option_column_button, "width='5%' nowrap ");

        $dataGrid = $clsDataGrid->showDataGrid($popups, $paginate, $popups->total());

        return view('ttxt-content::popup.index',
            compact(
                'popups',
                'dataGrid'
            )
        );
    }

    public function saveDataIndex(Request $request)
    {
        // foreach($request->ids as $id) {
        //     $p = Popup::find($id);
        //     if(!Gate::allows('popup/edit', $p)) {
        //         abort(403);
        //     }
        // }

        $update = $request->get('update', []);
        foreach ($update as $key => $value) {
            Popup::where('id', $key)->update($value);
        }
        return redirect()->route('backend_popup')->with('success', 'Cập nhật thông tin thành công');
    }

    public function edit(Request $request, Popup $popup)
    {
        if($popup->exists && !Gate::allows('popup/edit', $popup)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$popup->exists && !Gate::allows('popup/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        return view('ttxt-content::popup.create', compact('popup'));
    }

    public function save(Popup $popup, Request $request)
    {
        $user = auth('web')->user();
        if($popup->exists && !Gate::allows('popup/edit', $popup)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$popup->exists && !Gate::allows('popup/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'image' => 'required|string',
            'link' => 'nullable|url',
        ]);

        try {
            if(!$popup->exists) {
                $popup->fill($validated);
                $popup->approval_level = $user->is_super_admin ? 2 : ($user->is_approve ? 1 : 0);
                $popup->max_approval = 2;
                $popup->is_draft = false;
                $popup->status_approve = $user->is_super_admin ? 'approved' : ($user->is_approve ? 'pending' : 'pending');
                if (Gate::allows('popup/add')) {
                    $this->addPopupToScope($user, $popup->id);
                }
                $popup->save();
            }else {
                if ($user->is_super_admin) {
                    $mainPopup = $popup->parent_id ? Popup::find($popup->parent_id) : $popup;

                    $mainPopup->fill($validated);
                    $mainPopup->approval_level = $mainPopup->max_approval;
                    $mainPopup->status_approve = 'approved';
                    $mainPopup->is_draft = false;
                    $mainPopup->parent_id = null;
                    $mainPopup->save();

                    // Xoá nháp
                    $drafts = Popup::where('parent_id', $mainPopup->id)->get();
                    foreach ($drafts as $draft) {
                        $this->removePopupFromScope($draft->id);
                        $draft->delete();
                    }

                    $popup = $mainPopup;
                } else {
                    // 🟨 Người dùng thường
                    if ($popup->status_approve === 'approved' && !$popup->is_draft) {
                        $draft = $popup->replicate();
                        $draft->fill($validated);
                        $draft->is_draft = true;
                        $draft->status_approve = 'pending';
                        $draft->approval_level = $user->is_approve ? 1 : 0;
                        $draft->parent_id = $popup->id;
                        $draft->save();

                        if (Gate::allows('popup/add')) {
                            $this->addPopupToScope($user, $draft->id);
                        }

                        $popup = $draft;
                    } else {
                        $popup->fill($validated);
                        $popup->status_approve = 'pending';
                        $popup->approval_level = $user->is_approve ? 1 : 0;
                        $popup->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            return back()->withInput()->withErrors(['error' => 'Lỗi không xác định: ' . $ex->getMessage()]);
        }

        return redirect()->route('backend_popup_edit', $popup)->with('success', 'Cập nhật thông tin thành công');
    }

    public function approve(Popup $popup)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền duyệt dự án.');
        }
    
        if ($user->is_super_admin) {
            $popup->approval_level = $popup->max_approval;
            $popup->status_approve = 'approved';
            $popup->is_draft = false;
    
            if ($popup->parent_id) {
                $parent = Popup::find($popup->parent_id);
                if ($parent) {
                    $draftData = $popup->toArray();
                    $this->removePopupFromScope($popup->id);
                    $popup->delete();

                    $parent->fill($draftData);

                    $parent->parent_id = null;
                    $parent->is_draft = false;
                    $parent->status_approve = 'approved';
                    $parent->approval_level = $parent->max_approval;
                    $parent->save();

                    $popup = $parent;
                }
            }
        } elseif ($user->is_approve) {
            if ($popup->approval_level < 1) {
                $popup->approval_level = 1;
                $popup->status_approve = 'pending';
            }
        }
    
        $popup->save();

        return redirect()
            ->route('backend_popup_edit', $popup->id)
            ->with('success', 'Duyệt popup thành công ' . ($user->is_super_admin ? '(Đã duyệt)' : '(Chờ duyệt cấp 2)'));
    }

    public function reject(Popup $popup)
    {
        $user = auth('web')->user();

        if (!($user->is_super_admin || $user->is_approve)) {
            abort(403, 'Bạn không có quyền từ chối duyệt popup.');
        }
        // $popup->delete();
        $popup->status_approve = 'rejected';
        $popup->save();

        return redirect()
            ->route('backend_popup')
            ->with('success', 'Từ chối duyệt popup thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('popup/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $this->popup->destroy($id);
        return redirect()->route('backend_popup')->with('success', 'Xóa popup thành công');
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('popup/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $request->validate(['ids' => 'required|array']);

        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->responseJsonBadRequest();
        }

        $this->popup->destroy($ids);
        return $this->responseJsonOk();
    }

    protected function addPopupToScope($user, $popupId)
    {
        $group = Group::find($user->group_id);
        if (!$group) return;

        $scopeData = $group->scope_data ?? [];
        $resource = 'popup';

        if (!isset($scopeData[$resource])) {
            $scopeData[$resource] = [];
        }

        if (empty($scopeData[$resource])) {
            return;
        }

        if (!in_array((string)$popupId, $scopeData[$resource])) {
            $scopeData[$resource][] = (string)$popupId;
            $group->scope_data = $scopeData;
            $group->save();
        }
    }

    protected function removePopupFromScope($popupId)
    {
        $groups = Group::whereJsonContains('scope_data->popup', (string)$popupId)->get();

        foreach ($groups as $group) {
            $scopeData = $group->scope_data ?? [];
    
            if (!isset($scopeData['popup']) || !is_array($scopeData['popup'])) {
                continue;
            }

            if (empty($scopeData['popup'])) {
                continue;
            }

            $scopeData['popup'] = array_values(array_filter(
                $scopeData['popup'],
                fn($id) => (string)$id !== (string)$popupId
            ));
    
            $group->scope_data = $scopeData;
            $group->save();
        }
    }
}
