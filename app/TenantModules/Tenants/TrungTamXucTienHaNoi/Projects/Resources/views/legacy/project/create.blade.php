@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $project->exists ? 'Sửa dự án' : 'Thêm mới dự án' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.index') }}">Dự án</a></li>
    <li class="breadcrumb-item active"> {{ $project->exists ? 'Sửa dự án' : 'Thêm mới dự án' }}</li>
@endsection

@section('content')
    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>
        CKFinder.config({
            connectorPath: '/ckfinder/connector'
        });
    </script>

    @php
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
        $firstLocale = array_key_first($locales);
    @endphp

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('project/' . ($project->exists ? 'edit' : 'add'))
                            <x-forms.button-save />
                        @endcan
                        @if ($project->exists)
                            @can('project/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.create') }}" />
                            @endcan
                            @can('project/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.delete', $project->id) }}" />
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $project->status === 'pending'
                            )
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $project->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <div class="modal fade" id="approveModal-{{ $project->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $project->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $project->id }}">
                                                    Xác nhận duyệt dự án
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt dự án: <strong>{{ $project->name }}</strong>?
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, dự án sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, dự án sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.reject', $project->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.approve', $project->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt dự án</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.projects.save', $project) }}" method="post" enctype="multipart/form-data"
                    class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">

                        {{-- --- CÁC TRƯỜNG ĐƠN NGỮ --- --}}
                        <h4 class="mb-3">Thông tin Chung (Đơn ngữ)</h4>
                        
                        <x-forms.upload name="banner_image" value="{{ old('banner_image') ?: $project->banner_image }}"
                            label="Ảnh Chính (Banner)" type="image" :messages="$errors->get('banner_image')" />
                        <x-forms.upload name="detail_image" value="{{ old('detail_image') ?: $project->detail_image }}"
                            label="Ảnh Phụ (nhỏ)" type="image" :messages="$errors->get('detail_image')" />
                            
                        <div class="row">
                            <div class="col-md-12">
                                <x-forms.input name="lat" value="{{ old('lat') ?: $project->lat }}" label="Vĩ độ (Lat)"
                                    :messages="$errors->get('lat')" />
                            </div>
                            <div class="col-md-12">
                                <x-forms.input name="lng" value="{{ old('lng') ?: $project->lng }}" label="Kinh độ (Lng)"
                                    :messages="$errors->get('lng')" />
                            </div>
                        </div>

                        <x-forms.textarea name="boundary" value="{{ old('boundary') ?: $project->boundary }}" label="Tọa độ boundary dự án (khoanh vùng, tuỳ chọn) JSON: [[lat,lng], [lat,lng],...]"
                            :messages="$errors->get('boundary')" />
                        
                        <x-forms.input name="area" value="{{ old('area') ?: $project->area }}" label="Giá trị"
                            :messages="$errors->get('area')" />
                        <x-forms.select name="unit" label="Đơn vị tính" :required="true" :options="new HtmlString($option_units)"
                            :messages="$errors->get('unit')" />
                        <x-forms.select name="type_number" label="Loại dự án" :required="true" :options="new HtmlString($option_types)"
                            :messages="$errors->get('type_number')" />
                        <x-forms.select name="industry_number" label="Ngành/Lĩnh vực" :required="true" :options="new HtmlString($option_industries)"
                            :messages="$errors->get('industry_number')" />
                        <x-forms.input name="price" value="{{ old('price') ?: $project->price }}" label="Vốn đầu tư"
                            :messages="$errors->get('price')" />
                        <x-forms.input name="link" value="{{ old('link') ?: $project->link }}" label="Link dự án"
                            :messages="$errors->get('link')" />
                        <x-forms.upload name="location_image"
                            value="{{ old('location_image') ?: $project->location_image }}"
                            label="Ảnh sơ đồ liên kết dự án" type="image" :messages="$errors->get('location_image')" />
                        <x-forms.select-multiple name="districts" label="Khu vực" :options="$option_districts" :selected="old('districts', $project->districts->pluck('id')->toArray())"
                            :messages="$errors->get('districts')" help="Chọn các khu vực liên quan" />
                        <x-forms.switch name="is_invest" label="Trạng thái đầu tư" value="{{ $project->is_invest ?? 0 }}"
                            :messages="$errors->get('is_invest')" />
                        <x-forms.switch name="is_pinned" label="Có ghim dự án không" value="{{ $project->is_pinned ?? 0 }}"
                            :messages="$errors->get('is_pinned')" />
                        <x-forms.input name="pin_order" value="{{ old('pin_order') ?: $project->pin_order }}"
                            label="Thứ tự ghim dự án" :messages="$errors->get('pin_order')" />
                        <x-forms.switch name="is_hidden" label="Ẩn dự án" value="{{ $project->is_hidden ?? 0 }}"
                            :messages="$errors->get('is_hidden')" help="Nếu bật, dự án sẽ không hiển thị trên bản đồ và danh sách ngoài trang chủ" />
                        <x-forms.input name="link_vrtour" value="{{ old('link_vrtour') ?: $project->link_vrtour }}"
                            label="Link vrtour dự án" :messages="$errors->get('link_vrtour')" />
                            <div class="row mb-4">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-9">
                                    @if(old('link_vrtour') ?: $project->link_vrtour)
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary mt-2"
                                            data-toggle="modal"
                                            data-target="#qrVRTourModal">
                                            <i class="fa fa-qrcode"></i> Generate QR
                                        </button>
                                    @endif
                                </div>
                            </div>
                        <x-forms.input name="link_sand_table"
                            value="{{ old('link_sand_table') ?: $project->link_sand_table }}" label="Link sa bàn ảo dự án"
                            :messages="$errors->get('link_sand_table')" />
                        <x-forms.select name="layout_id" label="Lựa chọn layout dự án" :required="true" :options="new HtmlString($option_layouts)"
                            :selected="old('layout_id', $project->layout_id)" :messages="$errors->get('layout_id')" />
                            
                        <hr class="my-4">

                        {{-- --- TABS ĐA NGÔN NGỮ --- --}}
                        <h4 class="mb-3">Nội dung Dự án (Đa ngôn ngữ)</h4>

                        <div class="form-group">
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach($locales as $locale => $label)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $locale === $firstLocale ? 'active' : '' }}" 
                                           data-toggle="tab" 
                                           href="#lang-{{ $locale }}"
                                           role="tab">
                                            <i class="fas fa-language mr-1"></i> {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content border border-top-0 p-3">
                                @foreach($locales as $locale => $label)
                                    <div id="lang-{{ $locale }}" 
                                         class="tab-pane fade {{ $locale === $firstLocale ? 'show active' : '' }}"
                                         role="tabpanel">
                                        
                                        {{-- 1. Name & Slug --}}
                                        <x-forms.input name="name[{{ $locale }}]" 
                                            value="{!! old('name.'.$locale) ?: $project->getTranslation('name', $locale, false) !!}" 
                                            label="Tên dự án ({{ $label }})" :required="$locale === $firstLocale"
                                            onkeyup="changeNameToSlug('name[{{ $locale }}]', 'slug[{{ $locale }}]', false)" 
                                            :messages="$errors->get('name.'.$locale)" />
                                            
                                        <x-forms.input
                                            type="hidden"
                                            name="slug[{{ $locale }}]" 
                                            value="{{ old('slug.'.$locale) ?: $project->getTranslation('slug', $locale, false) }}" 
                                            label="Slug ({{ $label }})"
                                            :messages="$errors->get('slug.'.$locale)" />

                                        {{-- 2. Mô tả ngắn & Nội dung chi tiết --}}
                                        <x-forms.textarea name="short_desc[{{ $locale }}]" :required="$locale === $firstLocale"
                                            value="{{ old('short_desc.'.$locale) ?: $project->getTranslation('short_desc', $locale, false) }}" 
                                            label="Mô tả ngắn ({{ $label }})" :messages="$errors->get('short_desc.'.$locale)" />
                                            
                                        <x-forms.textarea name="description[{{ $locale }}]" :required="$locale === $firstLocale"
                                            value="{{ old('description.'.$locale) ?: $project->getTranslation('description', $locale, false) }}" 
                                            label="Nội dung chi tiết ({{ $label }})"
                                            editor="true" :messages="$errors->get('description.'.$locale)" />

                                        <hr class="my-4">
                                        
                                        {{-- 3. Lợi thế nổi bật (Advantage) --}}
                                        <h6 class="text-info">Lợi thế nổi bật ({{ $label }})</h6>
                                        @php
                                            $rawAdvTitles = old('advantage_titles.'.$locale) ?: $project->getTranslation('advantage_titles', $locale, false);
                                            $rawAdvDescs = old('advantage_descs.'.$locale) ?: $project->getTranslation('advantage_descriptions', $locale, false);

                                            $advTitles = is_string($rawAdvTitles) ? json_decode($rawAdvTitles, true) : (is_array($rawAdvTitles) ? $rawAdvTitles : []);
                                            $advDescs = is_string($rawAdvDescs) ? json_decode($rawAdvDescs, true) : (is_array($rawAdvDescs) ? $rawAdvDescs : []);
                                        @endphp
                                        <x-forms.upload-multi-combo name="advantage" :locale="$locale" 
                                            :value="[
                                                'images' => explode(';', $project->advantage_images ?? ''), 
                                                'titles' => $advTitles, 
                                                'descs' => $advDescs, 
                                            ]" label="Ảnh và mô tả lợi thế nổi bật"
                                            :editor="true" :messages="$errors->get('advantage_titles.'.$locale)" />

                                        <hr class="my-4">

                                        {{-- 4. Thiết kế mặt bằng (Design) --}}
                                        <h6 class="text-info">Thiết kế mặt bằng ({{ $label }})</h6>
                                        <x-forms.textarea name="design_short_desc[{{ $locale }}]" :required="false"
                                            value="{{ old('design_short_desc.'.$locale) ?: $project->getTranslation('design_short_desc', $locale, false) }}"
                                            label="Mô tả ngắn thiết kế mặt bằng ({{ $label }})" :messages="$errors->get('design_short_desc.'.$locale)" />
                                        @php
                                            $rawDesignDescs = old('design_descs.'.$locale) ?: $project->getTranslation('design_description', $locale, false);
                                            $designDescs = is_string($rawDesignDescs) ? json_decode($rawDesignDescs, true) : (is_array($rawDesignDescs) ? $rawDesignDescs : []);
                                        @endphp
                                        <x-forms.upload-multi-combo name="design" :locale="$locale"
                                            :value="[
                                                'images' => explode(';', $project->design_images ?? ''), 
                                                'descs' => $designDescs, 
                                            ]" label="Ảnh và mô tả thiết kế mặt bằng"
                                            :messages="$errors->get('design_descs.'.$locale)" />

                                        <hr class="my-4">

                                        {{-- 5. Văn bản pháp quy (Legal) --}}
                                        <h6 class="text-info">Văn bản pháp quy ({{ $label }})</h6>
                                        <x-forms.input name="legal_short_desc[{{ $locale }}]"
                                            value="{{ old('legal_short_desc.'.$locale) ?: $project->getTranslation('legal_short_desc', $locale, false) }}"
                                            label="Mô tả văn bản pháp quy ({{ $label }})" :messages="$errors->get('legal_short_desc.'.$locale)" />
                                        @php
                                            $rawFilesDescs = old('files_descs.'.$locale) ?: $project->getTranslation('legal_description', $locale, false);
                                            $filesDescs = is_string($rawFilesDescs) ? json_decode($rawFilesDescs, true) : (is_array($rawFilesDescs) ? $rawFilesDescs : []);
                                        @endphp
                                        <x-forms.upload-multi-combo name="files" :locale="$locale"
                                            :value="[
                                                'images' => explode(';', $project->legal_file ?? ''), 
                                                'descs' => $filesDescs, 
                                            ]" label="Tệp đính kèm văn bản pháp quy"
                                            :messages="$errors->get('files_descs.'.$locale)" />

                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    </section>

    <script>
        function convertToSlug(text) {
            text = text.toLowerCase().trim();
            text = text.replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
            text = text.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
            text = text.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
            text = text.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
            text = text.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
            text = text.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
            text = text.replace(/đ/gi, 'd');
            text = text.replace(/[^a-z0-9\s-]/g, '');
            text = text.replace(/\s+/g, '-');
            text = text.replace(/-+/g, '-');
            text = text.replace(/^-+|-+$/g, '');
            return text;
        }

        function changeNameToSlug(nameFieldName, slugFieldName, allowDraftSuffix) {
            var nameInput = document.querySelector('input[name="' + nameFieldName + '"]');
            var slugInput = document.querySelector('input[name="' + slugFieldName + '"]');

            if (nameInput && slugInput) {
                var slug = convertToSlug(nameInput.value);
                slugInput.value = slug;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($locales as $locale => $label)
                var descriptionTextarea = document.querySelector('textarea[name="description[{{ $locale }}]"][editor="true"]');
                if (descriptionTextarea) {
                    CKEDITOR.replace('description[{{ $locale }}]', {
                        filebrowserBrowseUrl: '/ckfinder/browser',
                        filebrowserUploadUrl: '/ckfinder/connector?command=QuickUpload&type=Files'
                    });
                }
            @endforeach
        });
    </script>

    <div class="modal fade" id="qrVRTourModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code – VR Tour {{ $project->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="qrCodeContainer" style="display: flex; justify-content: center;"></div>
                    <small class="text-muted d-block mt-2">
                        {{ old('link_vrtour') ?: $project->link_vrtour }}
                    </small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-success btn-sm px-4" onclick="downloadQR()">Tải QR</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    let qr;
    $('#qrVRTourModal').on('shown.bs.modal', function () {
        const box = document.getElementById('qrCodeContainer');
        box.innerHTML = '';
        qr = new QRCode(box, {
            text: @json(old('link_vrtour') ?: $project->link_vrtour),
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
@endsection