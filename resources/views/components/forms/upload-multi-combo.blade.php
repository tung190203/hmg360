@props(['name', 'locale' => null, 'value' => [], 'label' => '', 'messages' => [], 'help' => '', 'editor' => false])

@php
    $hasTitles = array_key_exists('titles', $value);
    $hasDescs = array_key_exists('descs', $value);

    $items = collect($value['files'] ?? ($value['images'] ?? []))
        ->filter()
        ->values()
        ->all();
    $titles = $value['titles'] ?? [];
    $descs = $value['descs'] ?? [];
    
    // ✅ Tạo tên field với locale nếu có
    $imageFieldName = $name . '_images';
    $titleFieldName = $name . '_titles' . ($locale ? "[{$locale}]" : '');
    $descFieldName = $name . '_descs' . ($locale ? "[{$locale}]" : '');
@endphp

<div class="form-group row">
    <label class="col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-9">
        <div id="{{ $name }}_{{ $locale }}_wrapper">
            <div class="upload-combo-list mb-3">
                @foreach ($items as $index => $file)
                    @php
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
                        $descId = $name . '_' . $locale . '_desc_' . $index;
                    @endphp
                    <div class="upload-combo-item mt-4">
                        <div class="position-relative">
                            @if ($isImage)
                                <img src="{{ $file }}" class="rounded"
                                    style="max-width:200px; max-height:150px; object-fit:cover;">
                                <button type="button"
                                    class="btn btn-sm btn-danger rounded-circle btn-close-custom position-absolute top-0 end-0"
                                    onclick="removeUploadComboItem(this)">
                                    &times;
                                </button>
                            @else
                                <div class="d-flex align-items-center border rounded p-2 bg-light position-relative"
                                    style="max-width:200px; max-height:150px;">
                                    <i class="fas fa-file-alt fa-2x text-secondary me-2"></i>
                                    <span class="text-truncate" style="max-width:160px;">{{ basename($file) }}</span>
                                    <button type="button"
                                        class="btn btn-sm btn-danger rounded-circle btn-close-custom1 position-absolute"
                                        onclick="removeUploadComboItem(this)">
                                        &times;
                                    </button>
                                </div>
                            @endif
                            @php
                                $locales = array_keys(config('app.locales', ['vi' => 'Tiếng Việt']));
                                $firstLocale = $locales[0] ?? null;
                            @endphp

                            @if(!$locale || $locale === $firstLocale)
                                <input type="hidden" name="{{ $imageFieldName }}[]" value="{{ $file }}">
                            @endif
                        </div>

                        @if ($hasTitles)
                            <input type="text" name="{{ $titleFieldName }}[]" class="form-control mt-2 mb-2" placeholder="Tiêu đề"
                                value="{{ $titles[$index] ?? '' }}">
                        @endif

                        @if ($hasDescs)
                            <textarea name="{{ $descFieldName }}[]" id="{{ $descId }}" class="form-control mt-2" rows="3"
                                placeholder="Mô tả">{{ $descs[$index] ?? '' }}</textarea>
                            @if ($editor)
                                <script>
                                    if (typeof CKEDITOR !== 'undefined') {
                                        CKEDITOR.replace('{{ $descId }}');
                                    }
                                </script>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Nút thêm file/ảnh -->
            <div class="d-flex justify-content-start">
                <div id="{{ $name }}_{{ $locale }}_add" class="upload-combo-add text-muted"
                    onclick="selectFilesWithCKFinder('{{ $name }}', '{{ $locale }}', {{ $hasTitles ? 'true' : 'false' }}, {{ $hasDescs ? 'true' : 'false' }}, {{ $editor ? 'true' : 'false' }})">
                    +
                </div>
            </div>
        </div>

        @if ($messages)
            @foreach ((array) $messages as $message)
                <div class="text-danger mt-1">{{ $message }}</div>
            @endforeach
        @endif

        @if ($help)
            <div class="text-muted mt-2">{{ $help }}</div>
        @endif
    </div>
</div>

<style>
    .upload-combo-item {
        width: 100%;
    }

    .upload-combo-add {
        width: 200px;
        height: 133px;
        border: 2px dashed #ccc;
        border-radius: 6px;
        cursor: pointer;
        font-size: 48px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .upload-combo-add:hover {
        background-color: #f8f9fa;
        border-color: #888;
    }

    .btn-close-custom {
        width: 28px;
        height: 28px;
        background-color: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        font-size: 16px;
        text-align: center;
        line-height: 24px;
        padding: 0;
    }

    .btn-close-custom1 {
        width: 28px;
        height: 28px;
        background-color: rgba(255, 0, 0, 0.8);
        color: white;
        border: none;
        font-size: 16px;
        text-align: center;
        line-height: 24px;
        padding: 0;
        top: 0;
        right: -30px;
    }

    .btn-close-custom:hover {
        background-color: rgba(220, 0, 0, 0.9);
    }

    .btn-close-custom1:hover {
        background-color: rgba(220, 0, 0, 0.9);
    }
</style>

<script>
    function selectFilesWithCKFinder(name, locale = null, hasTitles = true, hasDescs = true, useEditor = false) {
        CKFinder.modal({
            chooseFiles: true,
            width: 800,
            height: 600,
            onInit: function (finder) {
                finder.on('files:choose', function (evt) {
                    const files = evt.data.files.models;
                    const list = document.querySelector(`#${name}_${locale}_wrapper .upload-combo-list`);
                    let index = list.querySelectorAll('.upload-combo-item').length;

                    // ✅ Xác định tên field với locale
                    const imageFieldName = name + '_images';
                    const titleFieldName = name + '_titles' + (locale ? `[${locale}]` : '');
                    const descFieldName = name + '_descs' + (locale ? `[${locale}]` : '');
                    
                    // ✅ Chỉ cho phép upload ảnh ở ngôn ngữ đầu tiên
                    const locales = {{ Js::from(array_keys(config('app.locales', ['vi' => 'Tiếng Việt']))) }};
                    const firstLocale = locales[0];
                    const canUploadImage = !locale || locale === firstLocale;

                    files.forEach(function (file) {
                        const url = file.getUrl();
                        const item = document.createElement('div');
                        item.className = 'upload-combo-item mt-4';

                        const descId = `${name}_${locale}_desc_${index}`;
                        const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(url);

                        let html = '';
                        
                        // ✅ Chỉ hiển thị ảnh/file nếu là ngôn ngữ đầu tiên
                        if (canUploadImage) {
                            html += isImage ? `
                                <div class="position-relative" style="max-width:200px; max-height:150px;">
                                    <img src="${url}" class="rounded" style="max-width:200px; max-height:150px; object-fit:cover;">
                                    <button type="button"
                                        class="btn btn-sm btn-danger rounded-circle btn-close-custom position-absolute top-0 end-0"
                                        onclick="removeUploadComboItem(this)">
                                        &times;
                                    </button>
                                    <input type="hidden" name="${imageFieldName}[]" value="${url}">
                                </div>
                            ` : `
                                <div class="d-flex align-items-center border rounded p-2 bg-light position-relative" style="max-width:200px; max-height:150px;">
                                    <i class="fas fa-file-alt fa-2x text-secondary me-2"></i>
                                    <span class="text-truncate" style="max-width:160px;">${url.split('/').pop()}</span>
                                    <button type="button"
                                        class="btn btn-sm btn-danger rounded-circle btn-close-custom1 position-absolute top-0 end-0"
                                        onclick="removeUploadComboItem(this)">
                                        &times;
                                    </button>
                                    <input type="hidden" name="${imageFieldName}[]" value="${url}">
                                </div>
                            `;
                        }

                        if (hasTitles) {
                            html += `<input type="text" name="${titleFieldName}[]" class="form-control mt-2 mb-2" placeholder="Tiêu đề">`;
                        }

                        if (hasDescs) {
                            html += `<textarea name="${descFieldName}[]" id="${descId}" class="form-control mt-2" rows="3" placeholder="Mô tả"></textarea>`;
                        }

                        item.innerHTML = html;
                        list.appendChild(item);

                        if (hasDescs && useEditor && typeof CKEDITOR !== 'undefined') {
                            CKEDITOR.replace(descId);
                        }

                        index++;
                    });
                });
            }
        });
    }

    function removeUploadComboItem(button) {
        const container = button.closest('.upload-combo-item');
        const textarea = container.querySelector('textarea');
        if (textarea && CKEDITOR.instances[textarea.id]) {
            CKEDITOR.instances[textarea.id].destroy(true);
        }
        container.remove();
    }
</script>