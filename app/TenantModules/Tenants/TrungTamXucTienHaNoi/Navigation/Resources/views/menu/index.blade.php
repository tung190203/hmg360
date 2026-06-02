@extends('backend.index')

@section('title')
    Quản lý menu
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Menu</li>
@endsection

@section('content')

    <hr class="mt-0">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-3"></div>
                <div class="col-xl-6 text-center">
                    <form action="" method="GET" class="form-filter-top-index">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <select class="form-control" name="type" onchange="this.form.submit()">
                                        {!! $option_positions !!}
                                    </select>
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
                        @can('menu/edit')
                            <x-forms.button-save/>
                        @endcan
                        {{-- @can('menu/add')
                            <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.create') }}"/>
                        @endcan --}}
                        @can('menu/delete')
                            <x-forms.button-bulk-delete url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.bulk_delete')}}"/>
                        @endcan
                    </div>
                </div>
            </div>
            <form method="post" action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.menu.save_data_index') }}" id="formDataGrid">
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
