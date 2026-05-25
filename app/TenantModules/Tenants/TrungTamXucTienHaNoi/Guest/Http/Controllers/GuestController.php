<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Guest\Http\Controllers;

use App\Http\Controllers\Controller;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Guest;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\Nation;
use Illuminate\Http\Request;
use App\Libs\DataGrid;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class GuestController extends Controller
{
    private Guest $guest;

    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
        $this->selectedMainMenu = 'guest';

        parent::__construct();

        if (!Gate::allows('guest')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
    }

    public function index(Request $request)
    {
        $this->selectedSubMenu('guest');

        $filter['name'] = $request->get('name', '');
        $filter['email'] = $request->get('email', '');
        $filter['phone'] = $request->get('phone', '');

        $query = $this->guest->with('nation')->orderBy('id', 'desc');

        if (!empty($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }
        if (!empty($filter['email'])) {
            $query->where('email', 'like', '%' . $filter['email'] . '%');
        }
        if (!empty($filter['phone'])) {
            $query->where('phone', 'like', '%' . $filter['phone'] . '%');
        }

        $guests = $query->paginate(25);

        $clsDataGrid = new DataGrid();
        $clsDataGrid->setLinkEdit('backend_guest_edit');
        $clsDataGrid->addColumnImage("avatar_url", "Avatar", "width=50px height=50px");
        $clsDataGrid->addColumnLabel("name", "Name", "width='15%' nowrap");
        $clsDataGrid->addColumnLabel("email", "Email", "width='15%' nowrap");
        $clsDataGrid->addColumnLabel("phone", "Phone", "width='10%' nowrap");
        $clsDataGrid->addColumnLabel("identification_number", "CMND/CCCD", "width='10%' nowrap");
        $clsDataGrid->addColumnLabel("nation_id", "Quốc gia", "width='10%' nowrap", 1, '', function ($col, $val, $id, $row) {
            return $row->nation->name ?? 'N/A';
        });
        $clsDataGrid->addColumnLabel("address", "Address", "width='20%'");
        $clsDataGrid->addColumnDate("created_at", "Ngày tạo", "width='10%' nowrap ", 'd-m-Y');

        $option_column_button = [
            'edit' => ['title' => 'Sửa', 'icon' => 'fa fa-edit', 'class' => 'btn-primary', 'route' => 'backend_guest_edit'],
            'delete' => ['title' => 'Xóa', 'icon' => 'fa fa-trash', 'class' => 'btn-danger', 'route' => 'backend_guest_delete'],
        ];
        $clsDataGrid->addColumnButton('id', '&nbsp', $option_column_button, "width='10%' nowrap ");

        $dataGrid = $clsDataGrid->showDataGrid($guests);

        return view('ttxt-leads::guest.index', compact('guests', 'filter', 'dataGrid'));
    }

    public function saveDataIndex(Request $request)
    {
        $update = $request->get('update', []);
        foreach ($update as $key => $value) {
            $this->guest->where('id', $key)->update($value);
        }
        return redirect()->route('backend_guest')->with('success', 'Cập nhật thông tin thành công');
    }

    public function edit(Request $request, Guest $guest)
    {
        if ($guest->exists && !Gate::allows('guest/edit')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if (!$guest->exists && !Gate::allows('guest/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $nations = Nation::all();
        return view('ttxt-leads::guest.create', compact('guest', 'nations'));
    }

    public function save(Guest $guest, Request $request)
    {
        if ($guest->exists && !Gate::allows('guest/edit')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        if (!$guest->exists && !Gate::allows('guest/add')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['required', 'email', Rule::unique(Guest::class, 'email')->ignore($guest->id)],
            'address' => 'nullable|string',
            'identification_number' => 'nullable|string|max:50',
            'nation_id' => ['nullable', Rule::exists(Nation::class, 'id')],
            'password' => $guest->exists ? 'nullable|min:6' : 'required|min:6',
        ];

        $validated = $request->validate($rules);

        $data = $request->only([
            'name',
            'avatar',
            'phone',
            'email',
            'address',
            'identification_number',
            'nation_id'
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $guest->fill($data);
        $guest->save();

        return redirect()->route('backend_guest_edit', $guest->id)->with('success', 'Lưu thông tin thành công');
    }

    public function delete(Request $request, $id)
    {
        if (!Gate::allows('guest/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $this->guest->destroy($id);
        return redirect()->route('backend_guest')->with('success', 'Xóa thành công');
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('guest/delete')) {
            abort(403, self::MESSAGE_UNAUTHORIZED);
        }
        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->responseJsonBadRequest();
        }

        $this->guest->destroy($ids);
        return $this->responseJsonOk();
    }
}
