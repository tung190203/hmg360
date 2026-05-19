<?php

namespace App\Http\Controllers\Backend;

use App\Libs\DataGrid;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->selectedMainMenu = 'user';
        $this->selectedSubMenu('user');

        parent::__construct();

        if (!Gate::allows('user')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $paginate = 20;

        $query = $this->user
            ->where('id', '<>', User::ROOT_ADMIN_ID)
            ->where('id', '<>', $user->id)
            ->visibleFor($user)
            ->orderBy('id', 'desc');

        if (!$user->isSuperAdmin()) {
            $query->where('is_super_admin', false);
        }

        $user = auth('web')->user();

        $scope = $user->getScope('user');
        if (!empty($scope)) {
            $query->whereIn('id', $scope);
        }

        $users = $query->paginate($paginate);

        $route_name = 'backend_user_edit';
        $option_column_button = User::makeOptionColumnButton();

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit($route_name);
        $clsDataGrid->addColumnLabel("name", "Name", "width='10%' nowrap", 1, '', function ($col, $val, $id, $row) {
            $html = e($row->name);
    
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
        $clsDataGrid->addColumnLabel("email", "Email", "width='15%' nowrap");
        $clsDataGrid->addColumnLabel("group_name", "Group", "nowrap");
        $clsDataGrid->addColumnDate("created_at", "Ngày tạo", "width='15%' nowrap ", 'd-m-Y');
        $clsDataGrid->addColumnButton('id', '&nbsp', $option_column_button, "width='5%' nowrap ");

        $dataGrid = $clsDataGrid->showDataGrid($users, $paginate, $users->total());

        return view('backend.user.index', compact('users', 'dataGrid'));
    }

    public function edit(User $user)
    {
        if($user->exists && !Gate::allows('user/edit', $user)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$user->exists && !Gate::allows('user/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $option_groups = Group::makeListGroup($user->group_id);
        return view('backend.user.create', compact('user', 'option_groups'));
    }

    public function save(User $user, Request $request)
    {
        $checkUser = auth('web')->user();
        if($user->exists && !Gate::allows('user/edit', $user)) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if(!$user->exists && !Gate::allows('user/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        $rules = [
            'name' => 'required|string',
            'email' => 'required|unique:users,email,' . $user->id,
        ];

        if (!$user->exists) {
            $rules['password'] = 'required|string|min:8';
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        $validated = $request->validate($rules);
        try {
            // 🟩 Tạo mới (bản chính)
            if (!$user->exists) {
                $user->fill($validated);
                $user->approval_level = $checkUser->is_super_admin ? 2 : ($checkUser->is_approve ? 1 : 0);
                $user->max_approval = 2;
                $user->is_draft = false;
                $user->status_approve = $checkUser->is_super_admin ? 'approved' : ($checkUser->is_approve ? 'pending' : 'pending');
                $user->status = $checkUser->is_super_admin ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;

                $user->name = trim(strip_tags($request->get('name')));
                $user->email = trim(strip_tags($request->get('email')));
                $user->phone = trim(strip_tags($request->get('phone')));
                $user->avatar = trim(strip_tags($request->get('avatar')));
                $password = trim(strip_tags($request->get('password')));

                if ($password) {
                    if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).+$/', $password)) {
                        return back()->withInput()->withErrors(['password' => 'Mật khẩu phải lớn hơn 8 ký tự và chứa ít nhất 1 chữ viết hoa, 1 ký tự đặc biệt']);
                    }
                    $user->password = Hash::make($request->get('password'));;
                }

                $user->is_super_admin = intval($request->get('is_super_admin'));
                $user->is_approve = intval($request->get('is_approve'));
                $user->group_id = intval($request->get('group_id'));

                $user->save();

                if (Gate::allows('user/add')) {
                    $this->addUserToScope($user, $user->id);
                }
            } else {
                // 🟦 Super admin merge
                if ($checkUser->is_super_admin) {
                    $mainUser = $user->main_id ? User::find($user->main_id) : $user;

                    $mainUser->fill($validated);
                    $mainUser->approval_level = $mainUser->max_approval;
                    $mainUser->status_approve = 'approved';
                    $mainUser->is_draft = false;
                    $mainUser->main_id = null;

                    $mainUser->name = trim(strip_tags($request->get('name')));
                    // $mainUser->email = trim(strip_tags($request->get('email')));
                    $email = trim(strip_tags($request->get('email')));
                    if (preg_match('/^(.+)\.draft_\d+$/', $email, $matches)) {
                        $email = $matches[1]; // Lấy email gốc
                    }
                    $mainUser->email = $email;
                    $mainUser->phone = trim(strip_tags($request->get('phone')));
                    $mainUser->avatar = trim(strip_tags($request->get('avatar')));
                    $password = trim(strip_tags($request->get('password')));

                    if ($password) {
                        if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).+$/', $password)) {
                            return back()->withInput()->withErrors(['password' => 'Mật khẩu phải lớn hơn 8 ký tự và chứa ít nhất 1 chữ viết hoa, 1 ký tự đặc biệt']);
                        }
                        $mainUser->password = Hash::make($request->get('password'));;
                    }

                    $mainUser->is_super_admin = intval($request->get('is_super_admin'));
                    $mainUser->is_approve = intval($request->get('is_approve'));
                    $mainUser->group_id = intval($request->get('group_id'));

                    $mainUser->save();

                    // Xoá nháp
                    $drafts = User::where('main_id', $mainUser->id)->get();
                    foreach ($drafts as $draft) {
                        $this->removeUserFromScope($draft->id);
                        $draft->delete();
                    }

                    $user = $mainUser;
                } else {
                    // 🟨 Người dùng thường
                    if ($checkUser->status_approve === 'approved' && !$checkUser->is_draft) {
                        // Bản chính đã duyệt → tạo bản nháp
                        $draft = $user->replicate();
                        $draft->fill($validated);
                        $draft->is_draft = true;
                        $draft->status_approve = 'pending';
                        $draft->approval_level = $checkUser->is_approve ? 1 : 0;
                        $draft->main_id = $user->id;

                        $draft->email = $request->get('email');
                        // Nếu email trùng và không phải bản nháp hiện tại, thêm suffix
                        if ($draft->email === $user->email && !$draft->exists) {
                            $draft->email = $request->get('email') . '.draft_' . time();
                        }

                        $draft->phone = trim(strip_tags($request->get('phone')));
                        $draft->avatar = trim(strip_tags($request->get('avatar')));
                        $password = trim(strip_tags($request->get('password')));

                        if ($password) {
                            if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).+$/', $password)) {
                                return back()->withInput()->withErrors(['password' => 'Mật khẩu phải lớn hơn 8 ký tự và chứa ít nhất 1 chữ viết hoa, 1 ký tự đặc biệt']);
                            }
                            $draft->password = Hash::make($password);;
                        }

                        $draft->is_super_admin = intval($request->get('is_super_admin'));
                        $draft->is_approve = intval($request->get('is_approve'));
                        $draft->group_id = intval($request->get('group_id'));
                        $draft->save();

                        if (Gate::allows('user/add')) {
                            $this->addUserToScope($user, $draft->id);
                        }

                        $user = $draft;
                    } else {
                        $user->fill($validated);
                        $user->status_approve = 'pending';
                        $user->approval_level = $checkUser->is_approve ? 1 : 0;
                        $user->save();
                    }
                }
            }

            return redirect()
                ->route('backend_user_edit', $user)
                ->with('success', 'Lưu dữ liệu thành công ' . (
                    $checkUser->is_super_admin ? '(Đã duyệt)' : ($checkUser->is_approve ? '(Chờ duyệt cấp 2)' : '')
                ));
        } catch (\Exception $ex) {
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }

    public function approve(User $user)
    {
        $checkUser = auth('web')->user();

        if (!($checkUser->is_super_admin || $checkUser->is_approve)) {
            abort(403, 'Bạn không có quyền duyệt dự án.');
        }

        if ($checkUser->is_super_admin) {
            $user->approval_level = $user->max_approval;
            $user->status_approve = 'approved';
            $user->is_draft = false;

            if ($user->main_id) {
                $parent = User::find($user->main_id);
                if ($parent) {
                    $draftData = $user->toArray();

                    if (isset($draftData['email']) && preg_match('/^(.+)\.draft_\d+$/', $draftData['email'], $matches)) {
                        $draftData['email'] = $matches[1];
                    }
                    $this->removeUserFromScope($user->id);
                    $user->delete();

                    $parent->fill($draftData);

                    $parent->main_id = null;
                    $parent->is_draft = false;
                    $parent->status_approve = 'approved';
                    $parent->approval_level = $parent->max_approval;
                    $parent->save();

                    $user = $parent;
                }
            } else {
                if (preg_match('/^(.+)\.draft_\d+$/', $user->email, $matches)) {
                    $user->email = $matches[1];
                }
                $user->save();
            }
        } elseif ($checkUser->is_approve) {
            if ($user->approval_level < 1) {
                $user->approval_level = 1;
                $user->status_approve = 'pending';
            }
            $user->save();
        }

        return redirect()
            ->route('backend_user_edit', ['user' => $user->id])
            ->with('success', 'Duyệt người dùng thành công ' . ($checkUser->is_super_admin ? '(Đã duyệt)' : '(Chờ duyệt cấp 2)'));
    }
    public function reject(User $user)
    {
        $checkUser = auth('web')->user();

        if (!($checkUser->is_super_admin || $checkUser->is_approve)) {
            abort(403, 'Bạn không có quyền từ chối duyệt user.');
        }
        // $user->delete();
        $user->status_approve = 'rejected';
        $user->save();

        return redirect()
            ->route('backend_user')
            ->with('success', 'Từ chối duyệt user thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('user/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }

        if (Auth::id() === $id || $id == User::ROOT_ADMIN_ID) {
            abort('403');
        }
        $this->user->destroy($id);
        return redirect()->to(route('backend_user'))->with('success', 'Xóa user thành công');
    }
    protected function addUserToScope($user, $userId)
    {
        $group = Group::find($user->group_id);
        if (!$group) return;

        $scopeData = $group->scope_data ?? [];
        $resource = 'user';

        if (empty($scopeData[$resource])) {
            return;
        }

        if (!in_array((string)$userId, $scopeData[$resource])) {
            $scopeData[$resource][] = (string)$userId;
            $group->scope_data = $scopeData;
            $group->save();
        }
    }

    protected function removeUserFromScope($userId)
    {
        $groups = Group::whereJsonContains('scope_data->user', (string)$userId)->get();

        foreach ($groups as $group) {
            $scopeData = $group->scope_data ?? [];
    
            if (!isset($scopeData['user']) || !is_array($scopeData['user'])) {
                continue;
            }

            if (empty($scopeData['user'])) {
                continue;
            }

            $scopeData['user'] = array_values(array_filter(
                $scopeData['user'],
                fn($id) => (string)$id !== (string)$userId
            ));
    
            $group->scope_data = $scopeData;
            $group->save();
        }
    }
}

