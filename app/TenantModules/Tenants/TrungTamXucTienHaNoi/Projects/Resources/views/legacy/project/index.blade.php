@extends('backend.index')

@section('title')
    Quản lý dự án
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Dự án</li>
@endsection

@section('content')

    <hr class="mt-0">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-8 text-center">
                    <form action="" method="GET" class="form-filter-top-index">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" value="{{ $filter['name'] }}"
                                           placeholder="Tìm kiếm">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="form-control" name="type_number" onchange="this.form.submit()">
                                        <option value="">Loại dự án</option>
                                        @foreach($types as $id => $type)
                                            <option value="{{ $id }}"
                                                    @if($filter['type_number'] == $id) selected @endif>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="form-control" name="industry_number" onchange="this.form.submit()">
                                        <option value="">Loại ngành nghề</option>
                                        @foreach($industries as $id => $industry)
                                            <option value="{{ $id }}"
                                                    @if($filter['industry_number'] == $id) selected @endif>
                                                {{ $industry }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="form-control" name="district_id" onchange="this.form.submit()">
                                        <option value="">Địa điểm</option>
                                        @foreach($districts as $id => $district)
                                            <option value="{{ $id }}"
                                                    @if($filter['district_id'] == $id) selected @endif>
                                                {{ $district }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-left">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-4">
                    <div class="float-right mb-3">
                        @can('project/edit')
                            <x-forms.button-save/>
                        @endcan
                        @can('project/add')
                            <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.create') }}"/>
                        @endcan
                        @can('project/delete')
                            <x-forms.button-bulk-delete url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.bulk_delete')}}"/>
                        @endcan
                        @can('project/export')
                        <x-forms.button-url title="Xuất báo cáo" class="btn-success" icon="fa fa-file-export"
                                                url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.export') }}"/>
                        @endcan
                    </div>
                </div>
            </div>
            <form method="post" action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.save_data_index') }}" id="formDataGrid">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body table-responsive p-0">
                                {!! $dataGrid !!}
                            </div>
                        </div>
                        {{ $projects->withQueryString()->links() }}
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
