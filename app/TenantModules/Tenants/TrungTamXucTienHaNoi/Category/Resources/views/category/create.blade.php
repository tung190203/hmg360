@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $category->exists ? 'Sửa danh mục' : 'Thêm mới danh mục' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.index') }}">Danh mục</a></li>
    <li class="breadcrumb-item active">{{ $category->exists ? 'Sửa danh mục' : 'Thêm mới danh mục' }}</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({connectorPath: '/ckfinder/connector'});</script>

    @php
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
    @endphp

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('category/' . ($category->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @if($category->exists)
                            @can('category/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.create') }}"/>
                            @endcan
                            @can('category/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.delete', $category->id) }}"/>
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $category->status_approve === 'pending'
                            )
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $category->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <div class="modal fade" id="approveModal-{{ $category->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $category->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $category->id }}">
                                                    Xác nhận duyệt danh mục
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt danh mục: <strong>{{ $category->name }}</strong>?
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, danh mục sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, danh mục sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.reject', $category->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.approve', $category->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt danh mục</button>
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
                <form action="{{ route('tenant.trung_tam_xuc_tien_ha_noi.category.save', $category) }}" method="post"
                      enctype="multipart/form-data"
                      class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">

                        {{-- Các trường đơn ngữ --}}
                        <x-forms.input name="priority" value="{{ (old('priority') ?: $category->priority) ?: 9999 }}"
                                       label="Sắp xếp" type="number" :messages="$errors->get('priority')"/>
                        <x-forms.select name="parent_id" label="Danh mục cha" :options="new HtmlString($list_category)"
                                        :messages="$errors->get('parent_id')"/>
                        <x-forms.upload name="image" value="{{ old('image') ?: $category->image }}" label="Image"
                                        type="image" :messages="$errors->get('image')"/>
                        <x-forms.switch name="status" value="{{ $category->status ?? 1 }}" label="Hiển thị"
                                        :messages="$errors->get('status')"/>
                        <x-forms.switch name="at_home" value="{{ old('at_home') ?: $category->at_home }}"
                                        label="Hiển thị trang chủ" :messages="$errors->get('at_home')"/>

                        <hr class="my-4">

                        {{-- Tabs ngôn ngữ --}}
                        <div class="form-group">
                            <h5 class="mb-3">Nội dung đa ngôn ngữ</h5>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach($locales as $locale => $label)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
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
                                         class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         role="tabpanel">
                                        
                                        <x-forms.input 
                                            name="name[{{ $locale }}]" 
                                            value="{{ old('name.'.$locale) ?: $category->getTranslation('name', $locale, false) }}" 
                                            label="Tên danh mục ({{ $label }})"
                                            :required="$loop->first"
                                            {{-- ĐÃ BỎ onkeyup cũ. Sẽ dùng JS thuần ở cuối file. --}}
                                            :messages="$errors->get('name.'.$locale)" />
                                            
                                        <x-forms.input 
                                            name="slug[{{ $locale }}]" 
                                            value="{{ old('slug.'.$locale) ?: $category->getTranslation('slug', $locale, false) }}" 
                                            label="Slug ({{ $label }})"
                                            :messages="$errors->get('slug.'.$locale)" />
                                            
                                        <x-forms.textarea 
                                            name="description[{{ $locale }}]" 
                                            editor="true"
                                            value="{{ old('description.'.$locale) ?: $category->getTranslation('description', $locale, false) }}" 
                                            label="Mô tả ({{ $label }})" 
                                            :messages="$errors->get('description.'.$locale)" />
                                            
                                        <hr class="my-3">
                                        <h6 class="text-muted">SEO Meta Tags ({{ $label }})</h6>
                                        
                                        <x-forms.input 
                                            name="meta_title[{{ $locale }}]" 
                                            value="{{ old('meta_title.'.$locale) ?: $category->getTranslation('meta_title', $locale, false) }}"
                                            label="Meta Title ({{ $label }})" 
                                            :messages="$errors->get('meta_title.'.$locale)" />
                                            
                                        <x-forms.input 
                                            name="meta_keywords[{{ $locale }}]" 
                                            value="{{ old('meta_keywords.'.$locale) ?: $category->getTranslation('meta_keywords', $locale, false) }}"
                                            label="Meta Keywords ({{ $label }})" 
                                            :messages="$errors->get('meta_keywords.'.$locale)" />
                                            
                                        <x-forms.textarea 
                                            name="meta_description[{{ $locale }}]"
                                            value="{{ old('meta_description.'.$locale) ?: $category->getTranslation('meta_description', $locale, false) }}" 
                                            label="Meta Description ({{ $label }})"
                                            :messages="$errors->get('meta_description.'.$locale)" />

                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * Hàm chuyển đổi chuỗi có dấu (bao gồm tiếng Việt) thành slug không dấu.
             */
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

            // Logic tự động tạo slug và khởi tạo CKEditor
            @foreach($locales as $locale => $label)
                (function() {
                    var nameInput = document.querySelector('input[name="name[{{ $locale }}]"]');
                    var slugInput = document.querySelector('input[name="slug[{{ $locale }}]"]');
                    var descriptionTextarea = document.querySelector('textarea[name="description[{{ $locale }}]"]');
                    
                    // Gán sự kiện Keyup cho trường Tên danh mục (để tạo slug)
                    if (nameInput && slugInput) {
                        nameInput.addEventListener('keyup', function() {
                            var slug = convertToSlug(this.value);
                            slugInput.value = slug;
                        });
                        
                        // Chạy lần đầu nếu trường slug rỗng và name có giá trị
                        if (nameInput.value && !slugInput.value) {
                            slugInput.value = convertToSlug(nameInput.value);
                        }
                    }
                })();
                
                // Khởi tạo CKEditor cho trường Description
                if (document.querySelector('textarea[name="description[{{ $locale }}]"][editor="true"]')) {
                    CKEDITOR.replace('description[{{ $locale }}]', {
                        filebrowserBrowseUrl: '/ckfinder/browser',
                        filebrowserUploadUrl: '/ckfinder/connector?command=QuickUpload&type=Files'
                    });
                }
            @endforeach
        });
    </script>

@endsection