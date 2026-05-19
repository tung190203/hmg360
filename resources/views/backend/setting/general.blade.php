@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    Cấu hình chung
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">Cấu hình chung</li>
@endsection

@section('content')
    <script src="{{ asset('js/ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>
    <script>CKFinder.config({ connectorPath: '/ckfinder/connector' });</script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3 mt-3">
                        <x-forms.button-save />
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <form action="{{ route('backend_setting_save') }}" method="post" enctype="multipart/form-data"
                    class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body">

                        {{-- ================================================================= --}}
                        {{-- KHỐI TAB NGÔN NGỮ CHO CÁC FIELD ĐƠN GIẢN CẦN DỊCH --}}
                        {{-- **Đã thêm ADDRESS vào đây** --}}
                        {{-- ================================================================= --}}
                        <ul class="nav nav-tabs" id="settingTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="vi-tab" data-toggle="tab" href="#tab-vi" role="tab"
                                    aria-controls="tab-vi" aria-selected="true">Tiếng Việt (VI)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="en-tab" data-toggle="tab" href="#tab-en" role="tab"
                                    aria-controls="tab-en" aria-selected="false">Tiếng Anh (EN)</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="settingTabsContent">
                            
                            {{-- TAB TIẾNG VIỆT --}}
                            <div class="tab-pane fade show active" id="tab-vi" role="tabpanel" aria-labelledby="vi-tab">
                                <h4 class="mt-3">Cài đặt cơ bản (VI)</h4>
                                <x-forms.upload name="settings[logo][vi]" value="{{ $settings['logo']['vi'] ?? '' }}" label="Logo[VI]"
                                type="image" />
                                <x-forms.input name="settings[site_name][vi]" 
                                    value="{{ $settings['site_name']['vi'] ?? '' }}"
                                    label="Site name (VI)" />
                                <x-forms.textarea name="settings[footer_info][vi]" :required="true" editor="true"
                                    value="{{ $settings['footer_info']['vi'] ?? '' }}" label="Footer info (VI)" />
                                <x-forms.textarea name="settings[copyright_notice][vi]" :required="true" editor="true"
                                    value="{{ $settings['copyright_notice']['vi'] ?? '' }}" label="Copyright notice (VI)" />
                                <x-forms.textarea name="settings[copyright][vi]" :required="true"
                                    value="{{ $settings['copyright']['vi'] ?? '' }}" label="Copyright (VI)" />
                                
                                {{-- ADDRESS ĐÃ ĐƯỢC CHUYỂN VÀO ĐÂY --}}
                                <x-forms.input name="settings[address][vi]" value="{{ $settings['address']['vi'] ?? '' }}" label="Address (VI)" />
                                <x-forms.input name="settings[social_title][vi]" value="{{ $settings['social_title']['vi'] ?? '' }}" label="Social Title (VI)" />
                            </div>

                            {{-- TAB TIẾNG ANH --}}
                            <div class="tab-pane fade" id="tab-en" role="tabpanel" aria-labelledby="en-tab">
                                <h4 class="mt-3">Cài đặt cơ bản (EN)</h4>
                                <x-forms.upload name="settings[logo][en]" value="{{ $settings['logo']['en'] ?? '' }}" label="Logo[EN]"
                                type="image" />
                                <x-forms.input name="settings[site_name][en]" 
                                    value="{{ $settings['site_name']['en'] ?? '' }}"
                                    label="Site name (EN)" />
                                <x-forms.textarea name="settings[footer_info][en]" :required="true" editor="true"
                                    value="{{ $settings['footer_info']['en'] ?? '' }}" label="Footer info (EN)" />
                                <x-forms.textarea name="settings[copyright_notice][en]" :required="true" editor="true"
                                    value="{{ $settings['copyright_notice']['en'] ?? '' }}" label="Copyright notice (EN)" />
                                <x-forms.textarea name="settings[copyright][en]" :required="true"
                                    value="{{ $settings['copyright']['en'] ?? '' }}" label="Copyright (EN)" />
                                    
                                {{-- ADDRESS ĐÃ ĐƯỢC CHUYỂN VÀO ĐÂY --}}
                                <x-forms.input name="settings[address][en]" value="{{ $settings['address']['en'] ?? '' }}" label="Address (EN)" />
                                <x-forms.input name="settings[social_title][en]" value="{{ $settings['social_title']['en'] ?? '' }}" label="Social Title (EN)" />
                            </div>
                        </div>

                        {{-- ================================================================= --}}
                        {{-- KHỐI FIELDS ĐƠN NGỮ (Images, Inputs không cần dịch) --}}
                        {{-- **ADDRESS đã được loại bỏ** --}}
                        {{-- ================================================================= --}}
                        <h4 class="mt-4">Cài đặt Đơn ngữ</h4>
                        <x-forms.upload name="settings[favicon]" value="{{ $settings['favicon'] ?? '' }}" label="Favicon"
                            type="image" />
                        <x-forms.upload name="settings[logo_footer]" value="{{ $settings['logo_footer'] ?? '' }}"
                            label="Logo Footer" type="image" />
                        {{-- <x-forms.input name="settings[address]" value="{{ $settings['address'] ?? '' }}" label="Address" /> <-- Đã xóa --}}
                        <x-forms.input name="settings[website]" value="{{ $settings['website'] ?? '' }}" label="Website" />
                        <x-forms.input name="settings[phone]" value="{{ $settings['phone'] ?? '' }}" label="Phone" />
                        <x-forms.input name="settings[email]" value="{{ $settings['email'] ?? '' }}" label="Email" />
                        <x-forms.switch name="settings[noindex]" value="{{ !empty($settings['noindex']) ? 1 : 0 }}"
                            label="Chặn index" :messages="$errors->get('noindex')" />


                        {{-- ================================================================= --}}
                        {{-- KHỐI DỮ LIỆU PHỨC TẠP (BANNERS) --}}
                        {{-- ================================================================= --}}

                        {{-- Danh sách link + ảnh --}}
                        @php
                            $links = $settings['banners'] ?? [];
                            if (!is_array($links)) $links = [];
                            if (empty($links)) $links = [['link' => '', 'image' => '']];
                        @endphp

                        <div class="form-group row mt-4">
                            <label class="col-sm-3 col-form-label">Danh sách liên kết + ảnh</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="settings[banners]" value="">

                                <div id="links-wrapper">
                                    @foreach($links as $i => $item)
                                        <div class="link-item border p-3 mb-3 rounded" data-index="{{ $i }}">
                                            <x-forms.input name="settings[banners][{{ $i }}][link]"
                                                value="{{ $item['link'] ?? '' }}" label="Link" />
                                            <x-forms.upload name="settings[banners][{{ $i }}][image]"
                                                value="{{ $item['image'] ?? '' }}" label="Ảnh" />

                                            <div class="form-group row">
                                                <div class="col-sm-9 offset-sm-3">
                                                    <button type="button" class="btn btn-danger btn-sm remove-link">Xóa</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-primary mt-2" id="add-link">+ Thêm liên kết</button>
                            </div>
                        </div>

                        {{-- ================================================================= --}}
                        {{-- KHỐI DỮ LIỆU PHỨC TẠP (FEATURES) - ĐÃ LÀM ĐA NGÔN NGỮ --}}
                        {{-- ================================================================= --}}
                        @php
                            $features = $settings['features'] ?? [];
                            if (!is_array($features)) $features = [];
                            // Khởi tạo fields đa ngôn ngữ bên trong
                            if (empty($features)) $features = [['icon' => '', 'title' => ['vi' => '', 'en' => ''], 'content' => ['vi' => '', 'en' => '']]];
                        @endphp

                        <div class="form-group row mt-4">
                            <label class="col-sm-3 col-form-label">Danh sách thành tựu (Đa ngôn ngữ)</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="settings[features]" value="">

                                <div id="features-wrapper">
                                    @foreach($features as $i => $item)
                                        <div class="feature-item border p-3 mb-3 rounded" data-index="{{ $i }}">
                                            <x-forms.upload name="settings[features][{{ $i }}][icon]"
                                                value="{{ $item['icon'] ?? '' }}" label="Icon" type="image" />
                                            
                                            <x-forms.input name="settings[features][{{ $i }}][title][vi]"
                                                value="{{ $item['title']['vi'] ?? '' }}" label="Tiêu đề (VI)" />
                                            <x-forms.input name="settings[features][{{ $i }}][title][en]"
                                                value="{{ $item['title']['en'] ?? '' }}" label="Tiêu đề (EN)" />

                                            <x-forms.textarea name="settings[features][{{ $i }}][content][vi]"
                                                value="{{ $item['content']['vi'] ?? '' }}" label="Nội dung (VI)" />
                                            <x-forms.textarea name="settings[features][{{ $i }}][content][en]"
                                                value="{{ $item['content']['en'] ?? '' }}" label="Nội dung (EN)" />

                                            <div class="form-group row">
                                                <div class="col-sm-9 offset-sm-3">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm remove-feature">Xóa</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-primary mt-2" id="add-feature">+ Thêm thành tựu</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        // ==================== Banner (Giữ nguyên) ====================
        function inputIdFromIndex(index) { return `settings_banners_${index}_image`; }

        function createLinkItemHtml(index) {
            const inputNameLink = `settings[banners][${index}][link]`;
            const inputNameImage = `settings[banners][${index}][image]`;
            const imgIdBase = inputIdFromIndex(index);

            return `
                <div class="link-item border p-3 mb-3 rounded" data-index="${index}">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Link</label>
                        <div class="col-sm-9">
                            <input type="text" name="${inputNameLink}" class="form-control" placeholder="Link">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Ảnh</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-btn color-white">
                                    <a id="${imgIdBase}" class="btn btn-primary border-radius-0">
                                        <i class="far fa-image"></i> Chọn file
                                    </a>
                                </span>
                                <input id="${imgIdBase}_input" class="form-control" type="text" name="${inputNameImage}">
                            </div>
                            <div id="${imgIdBase}_preview" class="preview_image" style="max-width: 200px"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="button" class="btn btn-danger btn-sm remove-link">Xóa</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function bindUploaderForIndex(index) {
            const imgIdBase = inputIdFromIndex(index);
            const btn = document.getElementById(imgIdBase);
            if (!btn) return;
            btn.addEventListener('click', function () {
                selectFileWithCKFinder(`${imgIdBase}_input`, `${imgIdBase}_preview`);
            });
        }

        document.getElementById('add-link').addEventListener('click', function () {
            const wrapper = document.getElementById('links-wrapper');
            let max = -1;
            wrapper.querySelectorAll('.link-item').forEach(it => {
                const di = parseInt(it.getAttribute('data-index'));
                if (!isNaN(di) && di > max) max = di;
            });
            const nextIndex = max + 1;

            const tmp = document.createElement('div');
            tmp.innerHTML = createLinkItemHtml(nextIndex);
            const newEl = tmp.firstElementChild;
            wrapper.appendChild(newEl);

            newEl.querySelector('.remove-link').addEventListener('click', () => newEl.remove());
            bindUploaderForIndex(nextIndex);
        });

        document.querySelectorAll('.remove-link').forEach(btn => {
            btn.addEventListener('click', function () {
                const item = btn.closest('.link-item');
                if (item) item.remove();
            });
        });

        // Bind uploader cho các link cũ
        @foreach($links as $i => $item)
        bindUploaderForIndex({{ $i }});
        @endforeach


        // ==================== Feature / Thành tựu (Đã cập nhật JS) ====================
        function inputFeatureIdFromIndex(index) { return `settings_features_${index}_icon`; }

        function createFeatureItemHtml(index) {
            const imgIdBase = inputFeatureIdFromIndex(index);
            return `
                <div class="feature-item border p-3 mb-3 rounded" data-index="${index}">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Icon</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-btn color-white">
                                    <a id="${imgIdBase}" class="btn btn-primary border-radius-0">
                                        <i class="far fa-image"></i> Chọn file
                                    </a>
                                </span>
                                <input id="${imgIdBase}_input" class="form-control" type="text" name="settings[features][${index}][icon]">
                            </div>
                            <div id="${imgIdBase}_preview" class="preview_image" style="max-width: 200px"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Tiêu đề (VI)</label>
                        <div class="col-sm-9">
                            <input type="text" name="settings[features][${index}][title][vi]" class="form-control" placeholder="Tiêu đề (VI)">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Tiêu đề (EN)</label>
                        <div class="col-sm-9">
                            <input type="text" name="settings[features][${index}][title][en]" class="form-control" placeholder="Tiêu đề (EN)">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nội dung (VI)</label>
                        <div class="col-sm-9">
                            <textarea name="settings[features][${index}][content][vi]" class="form-control" placeholder="Nội dung (VI)"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nội dung (EN)</label>
                        <div class="col-sm-9">
                            <textarea name="settings[features][${index}][content][en]" class="form-control" placeholder="Nội dung (EN)"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="button" class="btn btn-danger btn-sm remove-feature">Xóa</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function bindFeatureUploaderForIndex(index) {
            const imgIdBase = inputFeatureIdFromIndex(index);
            const btn = document.getElementById(imgIdBase);
            if (!btn) return;
            btn.addEventListener('click', function () {
                selectFileWithCKFinder(`${imgIdBase}_input`, `${imgIdBase}_preview`);
            });
        }

        document.getElementById('add-feature').addEventListener('click', function () {
            const wrapper = document.getElementById('features-wrapper');
            let max = -1;
            wrapper.querySelectorAll('.feature-item').forEach(it => {
                const di = parseInt(it.getAttribute('data-index'));
                if (!isNaN(di) && di > max) max = di;
            });
            const nextIndex = max + 1;

            const tmp = document.createElement('div');
            tmp.innerHTML = createFeatureItemHtml(nextIndex);
            const newEl = tmp.firstElementChild;
            wrapper.appendChild(newEl);

            newEl.querySelector('.remove-feature').addEventListener('click', () => newEl.remove());
            bindFeatureUploaderForIndex(nextIndex);
        });

        document.querySelectorAll('.remove-feature').forEach(btn => {
            btn.addEventListener('click', function () {
                const item = btn.closest('.feature-item');
                if (item) item.remove();
            });
        });

        // Bind uploader cho các feature cũ
        @foreach($features as $i => $item)
        bindFeatureUploaderForIndex({{ $i }});
        @endforeach
    </script>
@endsection