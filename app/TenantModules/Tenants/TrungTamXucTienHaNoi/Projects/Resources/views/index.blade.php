@extends('backend.index')

@section('title')
    Quản lý dự án
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Dự án</li>
@endsection

@section('content')
    <hr class="mt-0">
    <div class="container-fluid">
        <div class="row align-items-start">
            <div class="col-xl-8">
                <form action="{{ route('backend_project') }}" method="GET" class="form-filter-top-index">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" value="{{ $filter['name'] }}" placeholder="Tìm kiếm">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control" name="type_number" onchange="this.form.submit()">
                                    <option value="">Loại dự án</option>
                                    @foreach($types as $id => $type)
                                        <option value="{{ $id }}" @selected($filter['type_number'] == $id)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control" name="industry_number" onchange="this.form.submit()">
                                    <option value="">Loại ngành nghề</option>
                                    @foreach($industries as $id => $industry)
                                        <option value="{{ $id }}" @selected($filter['industry_number'] == $id)>{{ $industry }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control" name="district_id" onchange="this.form.submit()">
                                    <option value="">Địa điểm</option>
                                    @foreach($districts as $id => $district)
                                        <option value="{{ $id }}" @selected($filter['district_id'] == $id)>{{ $district }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xl-4">
                <div class="float-right mb-3">
                    @if($canUpdate)
                        <x-forms.button-save />
                    @endif
                    @if($canCreate)
                        <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus" url="{{ route('backend_project_create') }}" />
                    @endif
                    @if($canDelete)
                        <x-forms.button-bulk-delete url="{{ route('backend_project_bulk_delete') }}" />
                    @endif
                    <x-forms.button-url title="Xuất báo cáo" class="btn-success" icon="fa fa-file-export" url="{{ route('backend_project_export') }}" />
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('backend_project_save_data_index') }}" id="formDataGrid">
            @csrf
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-hover text-nowrap text-center align-middle mb-0 table-grid-admin">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th style="width: 42px;"><input type="checkbox" id="checkAllProjects"></th>
                                <th>Tên dự án</th>
                                <th>Ảnh chính</th>
                                <th>Tọa độ(lat/lng)</th>
                                <th>Giá trị</th>
                                <th>Đơn vị tính</th>
                                <th>Loại dự án</th>
                                <th>Ngành nghề</th>
                                <th>Khu vực</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th style="width: 120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>{{ $projects->firstItem() + $loop->index }}</td>
                                    <td><input type="checkbox" class="checker" value="{{ $project->id }}"></td>
                                    <td>
                                        <a href="{{ route('backend_project_edit', $project) }}">{{ $project->name }}</a>
                                        @if($project->is_draft)
                                            <span class="badge bg-warning">Bản chỉnh sửa</span>
                                        @endif
                                        @if($project->status === 'pending')
                                            <span class="badge bg-primary">Chờ duyệt</span>
                                        @elseif($project->status === 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @elseif($project->status === 'rejected')
                                            <span class="badge bg-danger">Từ chối</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->banner_image)
                                            <img src="{{ asset($project->banner_image) }}" alt="" style="width: 82px; height: 64px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td>{{ $project->lat && $project->lng ? $project->lat . ' - ' . $project->lng : '' }}</td>
                                    <td>{{ $project->area ? number_format($project->area) : '' }}</td>
                                    <td>{{ $project->unit_type_text }}</td>
                                    <td>{{ $project->type?->name }}</td>
                                    <td>{{ $project->industry?->name }}</td>
                                    <td>{{ $project->districts->pluck('name')->implode(', ') }}</td>
                                    <td>
                                        @if($project->is_hidden)
                                            <span class="badge bg-danger">Đang ẩn</span>
                                        @else
                                            <span class="badge bg-success">Đang hiện</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($project->created_at)->format('d-m-Y') }}</td>
                                    <td>
                                        @if($canUpdate)
                                            <a class="btn btn-info btn-sm mr-1" href="{{ route('backend_project_edit', $project) }}" title="Chỉnh sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endif
                                        @if($canDelete)
                                            <a class="btn btn-danger btn-sm" href="{{ route('backend_project_delete', $project) }}" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa dự án này?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">Chưa có dự án.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        {{ $projects->withQueryString()->links() }}
    </div>
@endsection

@section('script')
    <script>
        document.getElementById('checkAllProjects')?.addEventListener('change', function () {
            document.querySelectorAll('.checker').forEach((checker) => checker.checked = this.checked);
        });
    </script>
@endsection
