@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $post->exists ? 'Sửa tin tức' : 'Thêm mới tin tức' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_post') }}">Tin tức</a></li>
    <li class="breadcrumb-item active">{{ $post->exists ? 'Sửa tin tức' : 'Thêm mới tin tức' }}</li>
@endsection

@section('content')

    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({ connectorPath: '/ckfinder/connector' });</script>

    @php
        $locales = config('app.locales', ['vi' => 'Tiếng Việt', 'en' => 'Tiếng Anh']);
    @endphp

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('post/' . ($post->exists ? 'edit' : 'add'))
                            <x-forms.button-save />
                        @endcan
                        @can('post/import')
                            <x-forms.button-url title="Tạo từ link" class="btn-warning text-white" icon="fa fa-link"
                                                url="{{ route('backend_post_show_import_form') }}"/>
                        @endcan
                        @if($post->exists)
                            @can('post/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                    url="{{ route('backend_post_create') }}" />
                            @endcan
                            @can('post/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                    url="{{ route('backend_post_delete', $post->id) }}" />
                            @endcan
                            @if(
                                (auth('web')->user()->is_super_admin || auth('web')->user()->is_approve) &&
                                $post->status_approve === 'pending'
                            )
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-sm fw-bold btn-success" data-toggle="modal" data-target="#approveModal-{{ $post->id }}">
                                    <i class="fa fa-check" aria-hidden="true"></i> Duyệt
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="approveModal-{{ $post->id }}" tabindex="-1" aria-labelledby="approveModalLabel-{{ $post->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="approveModalLabel-{{ $post->id }}">
                                                    Xác nhận duyệt tin tức
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn duyệt tin tức: <strong>{{ $post->name }}</strong>?
                                                <p class="text-muted mt-2">
                                                    @if(auth('web')->user()->is_super_admin)
                                                        Sau khi duyệt, tin tức sẽ được cập nhật trạng thái và hiển thị cho người dùng.
                                                    @elseif(auth('web')->user()->is_approve)
                                                        Sau khi duyệt thành công, tin tức sẽ chờ duyệt lần cuối bởi admin.
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('backend_post_reject', $post->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger fw-bold">Yêu cầu chỉnh sửa</button>
                                                </form>
                                                <form action="{{ route('backend_post_approve', $post->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success fw-bold">Duyệt tin tức</button>
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
                <form action="{{ route('backend_post_save', $post) }}" method="post" enctype="multipart/form-data"
                    class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">

                        {{-- Các trường không đa ngôn ngữ --}}
                        <x-forms.input name="priority" value="{{ (old('priority') ?: $post->priority) ?: 9999 }}"
                            label="Sắp xếp" type="number" :messages="$errors->get('priority')" />
                        
                        <x-forms.select name="cat_id" label="Danh mục cha" :options="new HtmlString($option_categories)"
                            :messages="$errors->get('cat_id')" />
                        
                        <x-forms.select-multiple name="projects" label="Thuộc các dự án" :options="$option_projects" 
                            :selected="old('projects', $post->projects->pluck('id')->toArray())"
                            :messages="$errors->get('projects')" help="Chọn các dự án trực thuộc"
                            selectAll="true" selectLabel="dự án" />

                        <x-forms.upload name="image" value="{{ old('image') ?: $post->image }}" label="Image" type="image"
                            :messages="$errors->get('image')" />

                        @if(auth('web')->user()->is_super_admin)
                        <x-forms.switch name="status" value="{{ $post->status ?? 1 }}" label="Hiển thị"
                            :messages="$errors->get('status')" />
                        @endif
                        <x-forms.switch name="is_hot" value="{{ $post->is_hot ?? 1 }}" label="Nổi bật"
                            :messages="$errors->get('is_hot')" />
                        
                        <x-forms.input name="published_at" label="Ngày xuất bản" type="date"
                            :value="$post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('Y-m-d') : null"
                            :messages="$errors->get('published_at')" />

                        {{-- Tabs ngôn ngữ --}}
                        <div class="form-group">
                            <hr class="my-4">
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
                                            value="{{ old('name.'.$locale) ?: $post->getTranslation('name', $locale, false) }}" 
                                            label="Tên bài viết ({{ $label }})"
                                            :required="$loop->first" 
                                            onkeyup="changeNameToSlug('name[{{ $locale }}]', 'slug[{{ $locale }}]', false)"
                                            :messages="$errors->get('name.'.$locale)" />
                                            
                                        <x-forms.input 
                                            name="slug[{{ $locale }}]" 
                                            value="{{ old('slug.'.$locale) ?: $post->getTranslation('slug', $locale, false) }}" 
                                            label="Slug ({{ $label }})"
                                            :messages="$errors->get('slug.'.$locale)" />
                                            
                                        <x-forms.textarea 
                                            name="description[{{ $locale }}]" 
                                            value="{{ old('description.'.$locale) ?: $post->getTranslation('description', $locale, false) }}" 
                                            label="Mô tả ({{ $label }})"
                                            :messages="$errors->get('description.'.$locale)" />
                                            
                                        <x-forms.textarea 
                                            name="content[{{ $locale }}]" 
                                            value="{{ old('content.'.$locale) ?: $post->getTranslation('content', $locale, false) }}"
                                            label="Nội dung chi tiết ({{ $label }})" 
                                            editor="true" 
                                            :messages="$errors->get('content.'.$locale)" />
                                            
                                        <hr class="my-3">
                                        <h6 class="text-muted">Tệp đính kèm ({{ $label }})</h6>

                                        @php
                                            // Lấy URL tệp đã dịch (phân cách bằng dấu chấm phẩy)
                                            $fileUrls = explode(';', $post->getTranslation('files', $locale, false) ?? '');
                                            $fileUrls = array_filter($fileUrls, 'trim'); 
                                            
                                            // Lấy mô tả đã dịch
                                            $descsJson = $post->getTranslation('short_file_descs', $locale, false);
                                            $descs = is_string($descsJson) ? json_decode($descsJson, true) : [];
                                            if (!is_array($descs)) $descs = [];
                                        @endphp

                                        {{-- Container chứa các tệp (URL và Mô tả) --}}
                                        <div id="file-container-{{ $locale }}">
                                            @foreach($fileUrls as $index => $url)
                                                {{-- ĐÃ THÊM d-flex align-items-center ĐỂ CĂN CHỈNH CHIỀU DỌC --}}
                                                <div class="row mb-2 file-row-{{ $locale }} d-flex align-items-center">
                                                    <div class="col-md-5">
                                                        {{-- Trường URL Tệp --}}
                                                        <input type="text" class="form-control file-url-input-{{ $locale }}" 
                                                               name="files[{{ $locale }}][]" 
                                                               value="{{ trim($url) }}" 
                                                               placeholder="URL tệp">
                                                    </div>
                                                    <div class="col-md-1">
                                                        {{-- Nút mở File Manager --}}
                                                        <button type="button" class="btn btn-sm btn-primary btn-browse-file" data-locale="{{ $locale }}">
                                                            <i class="fa fa-folder-open"></i>
                                                        </button>
                                                    </div>
                                                    <div class="col-md-5">
                                                        {{-- Trường Mô tả Tệp --}}
                                                        <input type="text" class="form-control" 
                                                               name="files_descs[{{ $locale }}][]" 
                                                               value="{{ $descs[$index] ?? '' }}" 
                                                               placeholder="Mô tả ngắn">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-danger btn-sm remove-file" data-locale="{{ $locale }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Nút Thêm Tệp --}}
                                        <button type="button" class="btn btn-sm btn-success add-file mt-2" data-locale="{{ $locale }}">
                                            <i class="fa fa-plus"></i> Thêm tệp
                                        </button>

                                        @if($loop->first)
                                            @include('backend.partials.ai-extract-upload', ['extractId' => 'post-ai-extract', 'extractModel' => $post])
                                        @endif
                                            
                                        <hr class="my-3">
                                        <h6 class="text-muted">SEO Meta Tags ({{ $label }})</h6>
                                        
                                        <x-forms.input 
                                            name="meta_title[{{ $locale }}]" 
                                            value="{{ old('meta_title.'.$locale) ?: $post->getTranslation('meta_title', $locale, false) }}"
                                            label="Meta Title ({{ $label }})" 
                                            :messages="$errors->get('meta_title.'.$locale)" />
                                            
                                        <x-forms.input 
                                            name="meta_keywords[{{ $locale }}]" 
                                            value="{{ old('meta_keywords.'.$locale) ?: $post->getTranslation('meta_keywords', $locale, false) }}"
                                            label="Meta Keywords ({{ $label }})" 
                                            :messages="$errors->get('meta_keywords.'.$locale)" />
                                            
                                        <x-forms.textarea 
                                            name="meta_description[{{ $locale }}]"
                                            value="{{ old('meta_description.'.$locale) ?: $post->getTranslation('meta_description', $locale, false) }}" 
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
            function convertToSlug(text) {
                text = text.toLowerCase();
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
            @foreach($locales as $locale => $label)
                (function() {
                    var nameInput = document.querySelector('input[name="name[{{ $locale }}]"]');
                    var slugInput = document.querySelector('input[name="slug[{{ $locale }}]"]');
                    
                    if (nameInput && slugInput) {
                        nameInput.addEventListener('keyup', function() {
                            var slug = convertToSlug(this.value);
                            slugInput.value = slug;
                        });
                    }
                })();
                if (document.querySelector('textarea[name="content[{{ $locale }}]"]')) {
                    CKEDITOR.replace('content[{{ $locale }}]', {
                        filebrowserBrowseUrl: '/ckfinder/browser',
                        filebrowserUploadUrl: '/ckfinder/connector?command=QuickUpload&type=Files'
                    });
                }
            @endforeach
                        /**
             * Tạo cấu trúc HTML cho một tệp tin mới
             * @param {string} locale Ngôn ngữ hiện tại
             */
             function createNewFileRow(locale) {
                const newRow = document.createElement('div');
                // ĐÃ THÊM d-flex align-items-center VÀO ĐÂY
                newRow.className = 'row mb-2 file-row-' + locale + ' d-flex align-items-center';
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" class="form-control file-url-input-${locale}" 
                               name="files[${locale}][]" 
                               placeholder="URL tệp">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-primary btn-browse-file" data-locale="${locale}">
                            <i class="fa fa-folder-open"></i>
                        </button>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" 
                               name="files_descs[${locale}][]" 
                               placeholder="Mô tả ngắn">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-file" data-locale="${locale}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                `;
                return newRow;
            }

            // 2. Xử lý Thêm/Xóa tệp
            document.addEventListener('click', function(e) {
                // Xóa file
                const removeButton = e.target.closest('.remove-file');
                if (removeButton) {
                    e.preventDefault();
                    removeButton.closest(`.file-row-${removeButton.dataset.locale}`).remove();
                    return; 
                }

                // Thêm file
                const addButton = e.target.closest('.add-file');
                if (addButton) {
                    e.preventDefault();
                    var locale = addButton.dataset.locale;
                    var container = document.getElementById('file-container-' + locale);
                    
                    container.appendChild(createNewFileRow(locale));
                    return;
                }

                // 3. Logic Mở CKFinder cho trường URL tệp
                const browseButton = e.target.closest('.btn-browse-file');
                if (browseButton) {
                    e.preventDefault();
                    // Tìm input URL nằm trong cùng hàng (row)
                    const input = browseButton.closest(`.file-row-${browseButton.dataset.locale}`).querySelector(`input[name="files[${browseButton.dataset.locale}][]"]`);
                    
                    if (!input) return;

                    CKFinder.popup({
                        chooseFiles: true,
                        onInit: function(finder) {
                            finder.on('files:choose', function(evt) {
                                var file = evt.data.files.first();
                                input.value = file.getUrl();
                            });
                        }
                    });
                }
            });
        });
    </script>

@endsection
