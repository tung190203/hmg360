@props([
'name',
'placeholder' => '',
'value' => '',
'label' => '',
'messages' => [],
'rows' => 5,
'help' => '',
'editor' => false,
'required' => false,
'class_label' => 'col-sm-3',
'class_input' => 'col-sm-9',
'repeatable' => false,
])

<div class="form-group row">
    <label class="@if ($required) required @endif {{ $class_label }} col-form-label"
        for="{{ $name }}">
        {!! $label !!}
    </label>

    <div class="{{ $class_input }}" id="{{ $name }}_wrapper">
        @php
        $values = $repeatable ? (is_array($value) ? $value : explode(';', $value)) : [$value];
        @endphp

        @foreach ($values as $index => $val)
        @php
        $inputId = $repeatable ? $name . '_' . $index : $name;
        $inputName = $repeatable ? $name . '[]' : $name;
        @endphp

        <div class="repeatable-textarea mb-2 position-relative">
            <textarea name="{{ $inputName }}" id="{{ $inputId }}"
                class="form-control {{ !empty($messages) ? 'is-invalid' : '' }}" placeholder="{{ $placeholder ?? strip_tags($label) }}"
                rows="{{ $rows }}" {{ $attributes }}>{!! $val !!}</textarea>

            @if ($repeatable)
            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 0; right: 0;"
                onclick="this.parentElement.remove()">×</button>
            @endif

            @if ($editor)
            <script>
                CKEDITOR.replace("{{ $inputId }}", {
                    extraPlugins: 'CustomImage,html5video,iconbuttons',
                    removeButtons: 'Image',
                    allowedContent: true,
                    removePlugins: 'pastefilter', // Tắt filter khi paste
                    pasteFilter: null, // Không filter gì cả
                    forcePasteAsPlainText: false, // Không ép paste dưới dạng plain text

                    // Hoặc cấu hình chi tiết hơn:
                    pasteFromWordRemoveFontStyles: false,
                    pasteFromWordRemoveStyles: false,
                });
                CKEDITOR.on('dialogDefinition', function(ev) {
                    if (ev.data.name === 'html5video') {
                        var def = ev.data.definition;

                        def.onShow = function() {
                            setTimeout(function() {
                                // Lấy tất cả checkbox trong dialog video
                                var checkboxes = document.querySelectorAll('.cke_dialog_ui_checkbox_input');
                                checkboxes.forEach(function(checkbox) {
                                    // Auto check "Responsive width" và "Show controls"
                                    var label = checkbox.parentNode.textContent.trim().toLowerCase();
                                    if (label.includes('responsive') || label.includes('controls')) {
                                        checkbox.checked = true;
                                    }
                                });
                            }, 50);
                        };
                    }
                });
            </script>
            <style>
                .cke_button__imagebutton_icon {
                    transform: scale(1.5) !important;
                    margin: 2px !important;
                }
            </style>
            @endif
        </div>
        @endforeach

        @if ($repeatable)
        <button type="button" class="btn btn-sm btn-primary add-textarea-btn hover:btn-outline-primary"
            onclick="addTextarea('{{ $name }}', {{ $editor ? 'true' : 'false' }}, {{ $rows }})">
            + Thêm
        </button>
        @endif

        @if (!empty($messages))
        @foreach ((array) $messages as $message)
        <div class="text-danger mt-1">{{ $message }}</div>
        @endforeach
        @endif

        @if ($help)
        <div class="text-muted mt-1">{{ $help }}</div>
        @endif
    </div>
</div>

@if ($repeatable)
<script>
    function addTextarea(name, useEditor = false, rows = 5) {
        const wrapper = document.getElementById(`${name}_wrapper`);
        const count = wrapper.querySelectorAll('.repeatable-textarea').length;
        const id = `${name}_${count}`;

        const div = document.createElement('div');
        div.className = 'repeatable-textarea mb-2 position-relative';

        div.innerHTML = `
                <textarea name="${name}[]" id="${id}" class="form-control" rows="${rows}" placeholder=""></textarea>
                <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 0; right: 0;" onclick="this.parentElement.remove()">×</button>
            `;

        const addBtn = wrapper.querySelector('.add-textarea-btn');
        wrapper.insertBefore(div, addBtn); // Thêm trước nút thêm

        if (useEditor && typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace(id, {
                extraPlugins: 'CustomImage,html5video,iconbuttons',
                removeButtons: 'Image',
                allowedContent: true,
                removePlugins: 'pastefilter', // Tắt filter khi paste
                pasteFilter: null, // Không filter gì cả
                forcePasteAsPlainText: false, // Không ép paste dưới dạng plain text

                // Hoặc cấu hình chi tiết hơn:
                pasteFromWordRemoveFontStyles: false,
                pasteFromWordRemoveStyles: false,
            });
            CKEDITOR.on('dialogDefinition', function(ev) {
                if (ev.data.name === 'html5video') {
                    var def = ev.data.definition;

                    def.onShow = function() {
                        setTimeout(function() {
                            // Lấy tất cả checkbox trong dialog video
                            var checkboxes = document.querySelectorAll(
                                '.cke_dialog_ui_checkbox_input');
                            checkboxes.forEach(function(checkbox) {
                                // Auto check "Responsive width" và "Show controls"
                                var label = checkbox.parentNode.textContent.trim()
                                    .toLowerCase();
                                if (label.includes('responsive') || label.includes(
                                        'controls')) {
                                    checkbox.checked = true;
                                }
                            });
                        }, 50);
                    };
                }
            });
        }
    }
</script>
@endif