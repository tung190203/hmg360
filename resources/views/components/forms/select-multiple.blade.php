@props([
    'name',
    'label' => '',
    'placeholder' => 'Chọn...',
    'options' => [],
    'selected' => [],
    'messages' => [],
    'required' => false,
    'help' => '',
    'selectAll' => false,
    'selectLabel' => 'mục',
])

@php
    $selected = is_array($selected) ? $selected : (array) $selected;
@endphp

<div class="form-group row">
    @if ($label)
        <label class="col-sm-3 col-form-label {{ $required ? 'required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="col-sm-9">
        <div class="position-relative">
            {{-- Tags hiển thị --}}
            <div id="selected-tags-{{ $name }}"
                class="form-control flex flex-wrap items-center gap-1 px-2 py-2 mb-1"
                onclick="toggleDropdown_{{ $name }}()"
                style="min-height: fit-content; cursor: pointer; display: flex; align-items: center; justify-content: flex-start; gap: 5px">

                @php $hasSelected = false; @endphp
                @foreach ($options as $key => $value)
                    @if (in_array($key, $selected))
                        @php $hasSelected = true; @endphp
                        <span class="badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1"
                            data-value="{{ $key }}" style="height: 28px;">
                            {{ $value }}
                            <button type="button" class="ms-2 bg-transparent border-0 text-white"
                                style="font-size: 1rem; line-height: 1;" aria-label="Remove"
                                onclick="removeTag_{{ $name }}('{{ $key }}', event)">
                                &times;
                            </button>
                        </span>
                    @endif
                @endforeach

                @unless ($hasSelected)
                    <span class="text-muted small">{{ $placeholder }}</span>
                @endunless
            </div>
            <div id="dropdown_{{ $name }}" class="border rounded shadow bg-white position-absolute w-100"
                style="z-index: 9999; display: none; max-height: 220px; overflow-y: auto;">

                {{-- Search box --}}
                <div class="mb-2 px-2 pt-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm..."
                        oninput="filterOptions_{{ $name }}(this.value)">
                </div>

                <div id="dropdown-options-{{ $name }}" class="px-2 pb-2">
                    @if($selectAll)
                        <div class="form-check border-bottom mb-2 pb-1">
                            <input type="checkbox" class="form-check-input custom-large" id="select_all_{{ $name }}"
                                onchange="toggleAll_{{ $name }}(this.checked)"
                                {{ count($selected) === count($options) && count($options) > 0 ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="select_all_{{ $name }}">Chọn tất cả</label>
                        </div>
                    @endif
                    @foreach ($options as $key => $value)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input custom-large" name="{{ $name }}[]"
                                value="{{ $key }}" id="{{ $name }}_{{ $key }}"
                                {{ in_array($key, $selected) ? 'checked' : '' }}
                                onchange="updateTags_{{ $name }}()">
                            <label class="form-check-label"
                                for="{{ $name }}_{{ $key }}">{{ $value }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Error message --}}
        @if ($messages)
            @foreach ((array) $messages as $message)
                <span class="text-danger">{{ $message }}</span>
            @endforeach
        @endif

        {{-- Help --}}
        @if ($help)
            <p class="text-muted">{{ $help }}</p>
        @endif
    </div>
</div>

{{-- JS: Dropdown logic + render tags --}}
<script>
    function toggleDropdown_{{ $name }}() {
        const el = document.getElementById('dropdown_{{ $name }}');
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
        if (el.style.display === 'block') {
            updateSelectAllState_{{ $name }}();
        }
    }

    function toggleAll_{{ $name }}(checked) {
        const checkboxes = document.querySelectorAll('#dropdown-options-{{ $name }} .form-check:not(.border-bottom) input[type="checkbox"]');
        checkboxes.forEach(cb => {
            if (cb.closest('.form-check').style.display !== 'none') {
                cb.checked = checked;
            }
        });
        updateTags_{{ $name }}();
    }

    function updateSelectAllState_{{ $name }}() {
        const selectAllCb = document.getElementById('select_all_{{ $name }}');
        if (!selectAllCb) return;

        const checkboxes = document.querySelectorAll('#dropdown-options-{{ $name }} .form-check:not(.border-bottom) input[type="checkbox"]');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        selectAllCb.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
    }

    function updateTags_{{ $name }}() {
        const checkboxes = document.querySelectorAll('#dropdown_{{ $name }} input[type="checkbox"]');
        const container = document.getElementById('selected-tags-{{ $name }}');
        container.innerHTML = '';

        let selectedCount = 0;
        const totalCount = checkboxes.length;
        const selectedLabels = [];

        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                selectedCount++;
                const label = document.querySelector('label[for="' + checkbox.id + '"]').innerText;
                selectedLabels.push({
                    value: checkbox.value,
                    label: label
                });
            }
        });

        if (selectedCount === 0) {
            const placeholder = document.createElement('span');
            placeholder.className = 'text-muted small';
            placeholder.innerText = '{{ $placeholder }}';
            container.appendChild(placeholder);
        } else if (selectedCount > 3) {
            const summary = document.createElement('span');
            summary.className = 'badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1';
            summary.style.height = '28px';
            summary.innerText = selectedCount === totalCount ?
                'Đã lựa chọn tất cả {{ $selectLabel }}' :
                `${selectedCount} trên ${totalCount} {{ $selectLabel }} đã chọn`;
            container.appendChild(summary);
        } else {
            selectedLabels.forEach(item => {
                const span = document.createElement('span');
                span.className =
                    'badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1';
                span.style.height = '28px';
                span.setAttribute('data-value', item.value);
                span.innerHTML = `
                    ${item.label}
                    <button type="button"
                        class="ms-2 bg-transparent border-0 text-white"
                        style="font-size: 1rem; line-height: 1;"
                        aria-label="Remove"
                        onclick="removeTag_{{ $name }}('${item.value}', event)">
                        &times;
                    </button>
                `;
                container.appendChild(span);
            });
        }
        updateSelectAllState_{{ $name }}();
    }

    function removeTag_{{ $name }}(value, event) {
        event.stopPropagation();
        const checkbox = document.getElementById('{{ $name }}_' + value);
        if (checkbox) {
            checkbox.checked = false;
            updateTags_{{ $name }}();
        }
    }

    // Đóng dropdown nếu click ra ngoài
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('dropdown_{{ $name }}');
        const tags = document.getElementById('selected-tags-{{ $name }}');
        if (!dropdown.contains(e.target) && !tags.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    function filterOptions_{{ $name }}(keyword) {
        const lowerKeyword = keyword.toLowerCase();
        const options = document.querySelectorAll('#dropdown-options-{{ $name }} .form-check');

        options.forEach(option => {
            const label = option.querySelector('label').innerText.toLowerCase();
            option.style.display = label.includes(lowerKeyword) ? 'block' : 'none';
        });
    }
</script>
