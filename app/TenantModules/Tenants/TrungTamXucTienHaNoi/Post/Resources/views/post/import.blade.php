@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    Thêm mới tin tức bằng link
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.post.index') }}">Tin tức</a></li>
    <li class="breadcrumb-item active">Thêm mới tin tức bằng link</li>
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
                        @can('post/' . ($post->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @can('post/import')
                            <x-forms.button-url title="Tạo từ link" class="btn-warning text-white" icon="fa fa-link"
                                                url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.post.import') }}"/>
                        @endcan
                        @if($post->exists)
                            @can('post/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.post.create') }}"/>
                            @endcan
                            @can('post/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.post.delete', $post->id) }}"/>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.post.import', $post) }}" method="post"
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
