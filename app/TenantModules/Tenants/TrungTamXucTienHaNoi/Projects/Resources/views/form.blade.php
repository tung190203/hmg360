@extends('backend.index')

@section('title')
    {{ $project->exists ? 'Sửa dự án' : 'Thêm mới dự án' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.index') }}">Dự án</a></li>
    <li class="breadcrumb-item active">{{ $project->exists ? 'Sửa dự án' : 'Thêm mới dự án' }}</li>
@endsection

@section('content')
    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>
        CKFinder.config({ connectorPath: '/ckfinder/connector' });
    </script>

    @php
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
        $firstLocale = array_key_first($locales);
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="float-right mb-3">
                    @if(($project->exists && $canUpdate) || (!$project->exists && $canCreate))
                        <x-forms.button-save />
                    @endif
                    @if($project->exists && $canCreate)
                        <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus" url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.create') }}" />
                    @endif
                    @if($project->exists && $canDelete)
                        <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash" url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.delete', $project) }}" />
                    @endif
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.save', $project->exists ? $project : null) }}" method="post" enctype="multipart/form-data" class="form-horizontal" id="formDataGrid">
                @csrf
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <h4 class="mb-3">Thông tin Chung</h4>
                    <x-forms.upload name="banner_image" value="{{ old('banner_image', $project->banner_image) }}" label="Ảnh Chính (Banner)" type="image" :messages="$errors->get('banner_image')" />
                    <x-forms.upload name="detail_image" value="{{ old('detail_image', $project->detail_image) }}" label="Ảnh Phụ (nhỏ)" type="image" :messages="$errors->get('detail_image')" />

                    <div class="row">
                        <div class="col-md-12">
                            <x-forms.input name="lat" value="{{ old('lat', $project->lat) }}" label="Vĩ độ (Lat)" :messages="$errors->get('lat')" />
                        </div>
                        <div class="col-md-12">
                            <x-forms.input name="lng" value="{{ old('lng', $project->lng) }}" label="Kinh độ (Lng)" :messages="$errors->get('lng')" />
                        </div>
                    </div>

                    <x-forms.textarea name="boundary" value="{{ old('boundary', $project->boundary) }}" label="Tọa độ boundary dự án" :messages="$errors->get('boundary')" />
                    <x-forms.input name="area" value="{{ old('area', $project->area) }}" label="Giá trị" :messages="$errors->get('area')" />

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">Đơn vị tính</label>
                        <div class="col-lg-9">
                            <select name="unit" class="form-control">
                                <option value="">-- Chọn đơn vị --</option>
                                @foreach($units as $id => $name)
                                    <option value="{{ $id }}" @selected(old('unit', $project->unit) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">Loại dự án</label>
                        <div class="col-lg-9">
                            <select name="type_number" class="form-control">
                                <option value="">-- Chọn loại dự án --</option>
                                @foreach($types as $id => $name)
                                    <option value="{{ $id }}" @selected(old('type_number', $project->type_number) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">Ngành/Lĩnh vực</label>
                        <div class="col-lg-9">
                            <select name="industry_number" class="form-control">
                                <option value="">-- Chọn ngành/lĩnh vực --</option>
                                @foreach($industries as $id => $name)
                                    <option value="{{ $id }}" @selected(old('industry_number', $project->industry_number) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <x-forms.input name="price" value="{{ old('price', $project->price) }}" label="Vốn đầu tư" :messages="$errors->get('price')" />
                    <x-forms.input name="link" value="{{ old('link', $project->link) }}" label="Link dự án" :messages="$errors->get('link')" />
                    <x-forms.upload name="location_image" value="{{ old('location_image', $project->location_image) }}" label="Ảnh sơ đồ liên kết dự án" type="image" :messages="$errors->get('location_image')" />
                    <x-forms.select-multiple name="districts" label="Khu vực" :options="$districts" :selected="old('districts', $project->districts->pluck('id')->toArray())" :messages="$errors->get('districts')" />
                    <x-forms.switch name="is_invest" label="Trạng thái đầu tư" value="{{ old('is_invest', (int) $project->is_invest) }}" :messages="$errors->get('is_invest')" />
                    <x-forms.switch name="is_pinned" label="Có ghim dự án không" value="{{ old('is_pinned', (int) $project->is_pinned) }}" :messages="$errors->get('is_pinned')" />
                    <x-forms.input name="pin_order" value="{{ old('pin_order', $project->pin_order) }}" label="Thứ tự ghim dự án" :messages="$errors->get('pin_order')" />
                    <x-forms.switch name="is_hidden" label="Ẩn dự án" value="{{ old('is_hidden', (int) $project->is_hidden) }}" :messages="$errors->get('is_hidden')" />
                    <x-forms.input name="link_vrtour" value="{{ old('link_vrtour', $project->link_vrtour) }}" label="Link vrtour dự án" :messages="$errors->get('link_vrtour')" />
                    <div class="row mb-4">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-9">
                            @if(old('link_vrtour', $project->link_vrtour))
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-toggle="modal" data-target="#qrVRTourModal">
                                    <i class="fa fa-qrcode"></i> Generate QR
                                </button>
                            @endif
                        </div>
                    </div>
                    <x-forms.input name="link_sand_table" value="{{ old('link_sand_table', $project->link_sand_table) }}" label="Link sa bàn ảo dự án" :messages="$errors->get('link_sand_table')" />

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">Lựa chọn layout dự án</label>
                        <div class="col-lg-9">
                            <select name="layout_id" class="form-control">
                                @foreach($layouts as $id => $name)
                                    <option value="{{ $id }}" @selected(old('layout_id', $project->layout_id ?: 1) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h4 class="mb-3">Nội dung Dự án</h4>

                    <ul class="nav nav-tabs" role="tablist">
                        @foreach($locales as $locale => $label)
                            <li class="nav-item">
                                <a class="nav-link {{ $locale === $firstLocale ? 'active' : '' }}" data-toggle="tab" href="#lang-{{ $locale }}" role="tab">
                                    <i class="fas fa-language mr-1"></i> {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content border border-top-0 p-3">
                        @foreach($locales as $locale => $label)
                            <div id="lang-{{ $locale }}" class="tab-pane fade {{ $locale === $firstLocale ? 'show active' : '' }}" role="tabpanel">
                                <x-forms.input name="name[{{ $locale }}]" value="{{ old('name.' . $locale, $project->getTranslation('name', $locale, false)) }}" label="Tên dự án ({{ $label }})" :required="$locale === $firstLocale" :messages="$errors->get('name.' . $locale)" />
                                <x-forms.input name="slug[{{ $locale }}]" value="{{ old('slug.' . $locale, $project->getTranslation('slug', $locale, false)) }}" label="Slug ({{ $label }})" :messages="$errors->get('slug.' . $locale)" />
                                <x-forms.textarea name="short_desc[{{ $locale }}]" value="{{ old('short_desc.' . $locale, $project->getTranslation('short_desc', $locale, false)) }}" label="Mô tả ngắn ({{ $label }})" :messages="$errors->get('short_desc.' . $locale)" />
                                <x-forms.textarea name="description[{{ $locale }}]" value="{{ old('description.' . $locale, $project->getTranslation('description', $locale, false)) }}" label="Nội dung chi tiết ({{ $label }})" editor="true" :messages="$errors->get('description.' . $locale)" />
                                <x-forms.textarea name="design_short_desc[{{ $locale }}]" value="{{ old('design_short_desc.' . $locale, $project->getTranslation('design_short_desc', $locale, false)) }}" label="Mô tả thiết kế ({{ $label }})" :messages="$errors->get('design_short_desc.' . $locale)" />
                                <x-forms.textarea name="legal_short_desc[{{ $locale }}]" value="{{ old('legal_short_desc.' . $locale, $project->getTranslation('legal_short_desc', $locale, false)) }}" label="Mô tả pháp lý ({{ $label }})" :messages="$errors->get('legal_short_desc.' . $locale)" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(old('link_vrtour', $project->link_vrtour))
        <div class="modal fade" id="qrVRTourModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    <div class="modal-header">
                        <h5 class="modal-title">QR Code - VR Tour {{ $project->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="qrCodeContainer" style="display: flex; justify-content: center;"></div>
                        <small class="text-muted d-block mt-2">{{ old('link_vrtour', $project->link_vrtour) }}</small>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-success btn-sm px-4" onclick="downloadQR()">Tải QR</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    @parent
    @if(old('link_vrtour', $project->link_vrtour))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            let qr;

            $('#qrVRTourModal').on('shown.bs.modal', function () {
                const box = document.getElementById('qrCodeContainer');
                box.innerHTML = '';
                qr = new QRCode(box, {
                    text: @json(old('link_vrtour', $project->link_vrtour)),
                    width: 220,
                    height: 220
                });
            });

            function downloadQR() {
                const img = document.querySelector('#qrCodeContainer img');
                if (!img) return;

                const a = document.createElement('a');
                a.href = img.src;
                a.download = 'qr-vr-tour.png';
                a.click();
            }
        </script>
    @endif
@endsection
