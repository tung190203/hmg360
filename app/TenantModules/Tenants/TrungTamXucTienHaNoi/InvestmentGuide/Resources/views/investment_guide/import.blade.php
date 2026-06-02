@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    Thêm mới cẩm nang đầu tư bằng link
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.index') }}">Cẩm nang đầu tư</a></li>
    <li class="breadcrumb-item active">Thêm mới cẩm nang đầu tư bằng link</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({connectorPath: '/ckfinder/connector'});</script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('investment_guide/' . ($investment_guide->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @can('investment_guide/import')
                            <x-forms.button-url title="Tạo từ link" class="btn-warning text-white" icon="fa fa-link"
                                                url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.import') }}"/>
                        @endcan
                        @if($investment_guide->exists)
                            @can('investment_guide/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.create') }}"/>
                            @endcan
                            @can('investment_guide/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.delete', $investment_guide->id) }}"/>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.import', $investment_guide) }}" method="post"
                      class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">
                        <x-forms.input name="url" value="{{ old('url') }}" label="Thêm bài viết từ link" type="text" :messages="$errors->get('url')"/>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
