@extends('backend.index')

@section('title')
    Quản lý Popup
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Popup</li>
@endsection

@section('content')

    <hr class="mt-0">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="float-right mb-3">
                        @can('popup/edit')
                            <x-forms.button-save/>
                        @endcan
                        @can('popup/add')
                            <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                url="{{ route('backend_popup_create') }}"/>
                        @endcan
                        @can('popup/delete')
                            <x-forms.button-bulk-delete url="{{ route('backend_popup_bulk_delete')}}"/>
                        @endcan
                    </div>
                </div>
            </div>
            <form method="post" action="{{ route('backend_popup_save_data_index') }}" id="formDataGrid">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body table-responsive p-0">
                                {!! $dataGrid !!}
                            </div>
                        </div>
                        {{ $popups->links() }}
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
