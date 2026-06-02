@extends('backend.index')

@section('title')
Quản lý khách hàng
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Khách hàng</li>
@endsection

@section('content')

<hr class="mt-0">
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-9">
                <form action="" method="GET" class="form-filter-top-index">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" value="{{ $filter['name'] }}"
                                    placeholder="Tên khách hàng">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="email" class="form-control" value="{{ $filter['email'] }}"
                                    placeholder="Email">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="phone" class="form-control" value="{{ $filter['phone'] }}"
                                    placeholder="Số điện thoại">
                            </div>
                        </div>
                        <div class="col-md-3 text-left">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xl-3">
                <div class="float-right mb-3">
                    @can('guest')
                    <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                        url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.guest.create') }}" />
                    @endcan
                    @can('guest')
                    <x-forms.button-bulk-delete url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.guest.bulk_delete')}}" />
                    @endcan
                </div>
            </div>
        </div>
        <form method="post" action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.guest.save_data_index') }}" id="formDataGrid">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            {!! $dataGrid !!}
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection