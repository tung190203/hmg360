<table class="table table-hover table-grid-admin">
    <thead>
    <tr>
        <th width="1% " class="grid_header">ID</th>
        <th width="20%" nowrap="" class="grid_header">Name</th>
        <th width="10%" class="grid_header">Danh mục</th>
        <th width="10%" class="grid_header">Image</th>
        <th width="5%" class="grid_header">Ngày tạo</th>
        <th width="5%" align="center" class="grid_header">Ngày xóa</th>
        <th width="5%" align="center" nowrap="" class="grid_header1">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    @foreach($investment_guides as $investment_guide)
        <tr id="grid_tr_0">
            <td style="color:#666666">{{ $investment_guide->id }}</td>
            <td>{{ $investment_guide->name  }}</td>
            <td><b>{{ data_get($investment_guide, 'category.name', 'No category') }}</b></td>
            <td><img src="{{ $investment_guide->image }}" style="width: 60px;"></td>
            <td>{{ $investment_guide->created_at }}</td>
            <td>{{ $investment_guide->deleted_at }}</td>
            <td>
                <a class="btn btn-success btn-sm" href="{{ route('backend_investment_guide_restore', $investment_guide->id) }}"
                   title="Khôi phục">
                    <i class="fas fa-trash-restore"></i>
                </a>
                <a class="btn btn-danger btn-sm" href="{{ route('backend_investment_guide_force_delete', $investment_guide->id) }}"
                   title="Xóa vĩnh viễn">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
