@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    Sửa nội dung
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_vrtour_content_index') }}">Nội dung</a></li>
    <li class="breadcrumb-item active">Sửa nội dung</li>
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
                        @can('content/add')
                            <x-forms.button-save/>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('backend_vrtour_content_store', $pano->id) }}" method="post" enctype="multipart/form-data" class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">
                        <x-forms.input name="ct_title" value="{{ (old('ct_title') ?: $pano->title) }}"
                                       label="Tiêu đề VN" type="text" :messages="$errors->get('ct_title')"/>
                        <x-forms.input name="ct_title_en" value="{{ (old('ct_title_en') ?: $pano->title_en) }}"
                                       label="Tiêu đề EN" type="text" :messages="$errors->get('ct_title_en')"/>
                        <x-forms.textarea editor="true" name="ct_content" value="{{ old('ct_content') ?: $pano->content }}" label="Nội dung VN"
                                        type="image" :messages="$errors->get('ct_content')"/>
                        <x-forms.textarea editor="true" name="ct_content_en" value="{{ old('ct_content_en') ?: $pano->content_en }}" label="Nội dung EN"
                                        type="image" :messages="$errors->get('ct_content_en')"/>
                        <x-forms.upload name="ct_audio" value="{{ (old('ct_audio') ?: $pano->audio) }}"
                                       label="Voice VN" type="text" :messages="$errors->get('ct_audio')"/>
                        <x-forms.upload name="ct_audio_en" value="{{ (old('ct_audio_en') ?: $pano->audio_en) }}"
                                       label="Voice EN" type="text" :messages="$errors->get('ct_audio_en')"/>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection

@section('script')
    <script>
        const range     = document.getElementById('hp_opacity');
        const output    = document.getElementById('rangeValue');
        function updateValue() {
            output.textContent = parseFloat(range.value).toFixed(2);
        }
        range.addEventListener('input', updateValue);
        updateValue();
    </script>
@endsection