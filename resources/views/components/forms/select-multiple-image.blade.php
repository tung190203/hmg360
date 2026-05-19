@props([
    'name',
    'label' => '',
    'placeholder' => 'Chọn...',
    'options' => [],
    'selected' => [],
    'messages' => [],
    'required' => false,
    'help' => '',
    'displayType' => 'text', // 'text' hoặc 'image'
    'imageHeight' => '40px', // Chiều cao ảnh mặc định
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

                @php 
                    $selectedCount = count(array_intersect(array_keys($options->toArray()), $selected));
                    $totalCount = count($options);
                @endphp

                @if ($selectedCount === 0)
                    <span class="text-muted small">{{ $placeholder }}</span>
                @elseif ($displayType === 'image')
                    <span class="badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1"
                        style="height: 28px;">
                        {{ $selectedCount === $totalCount ? 'Đã chọn tất cả ảnh' : ($selectedCount === 1 ? '1 ảnh được chọn' : "{$selectedCount} ảnh được chọn") }}
                    </span>
                @else
                    @foreach ($options as $key => $value)
                        @if (in_array($key, $selected))
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
                @endif
            </div>
            
            <div id="dropdown_{{ $name }}" class="border rounded shadow bg-white position-absolute w-100"
                style="z-index: 9999; display: none; max-height: 400px; overflow-y: auto;">

                {{-- Search box --}}
                <div class="mb-2 px-2 pt-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm..."
                        oninput="filterOptions_{{ $name }}(this.value)">
                </div>

                <div id="dropdown-options-{{ $name }}" class="px-2 pb-2">
                    @foreach ($options as $key => $value)
                        <div class="form-check d-flex align-items-center py-1" data-search="{{ $displayType === 'text' ? strtolower($value) : '' }}">
                            <input type="checkbox" class="form-check-input custom-large me-2" name="{{ $name }}[]"
                                value="{{ $key }}" id="{{ $name }}_{{ $key }}"
                                {{ in_array($key, $selected) ? 'checked' : '' }}
                                onchange="updateTags_{{ $name }}()">
                            <label class="form-check-label d-flex align-items-center w-100" 
                                for="{{ $name }}_{{ $key }}" 
                                style="cursor: pointer;">
                                @if ($displayType === 'image')
                                    <img src="{{ $value }}" alt="Image" 
                                        style="height: {{ $imageHeight }}; width: auto; border-radius: 4px; object-fit: cover;">
                                @else
                                    {{ $value }}
                                @endif
                            </label>
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
    const displayType_{{ $name }} = '{{ $displayType }}';
    const imageHeight_{{ $name }} = '{{ $imageHeight }}';

    function toggleDropdown_{{ $name }}() {
        const el = document.getElementById('dropdown_{{ $name }}');
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    function updateTags_{{ $name }}() {
        const checkboxes = document.querySelectorAll('#dropdown_{{ $name }} input[type="checkbox"]');
        const container = document.getElementById('selected-tags-{{ $name }}');
        container.innerHTML = '';

        let selectedCount = 0;
        const totalCount = checkboxes.length;
        const selectedItems = [];

        checkboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                selectedCount++;
                const label = document.querySelector('label[for="' + checkbox.id + '"]');
                const display = displayType_{{ $name }} === 'image' 
                    ? label.querySelector('img').src 
                    : label.innerText;
                
                selectedItems.push({
                    value: checkbox.value,
                    display: display
                });
            }
        });

        if (selectedCount === 0) {
            const placeholder = document.createElement('span');
            placeholder.className = 'text-muted small';
            placeholder.innerText = '{{ $placeholder }}';
            container.appendChild(placeholder);
        } else if (displayType_{{ $name }} === 'image') {
            // Với image: chỉ hiển thị số lượng
            const summary = document.createElement('span');
            summary.className = 'badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1';
            summary.style.height = '28px';
            summary.innerText = selectedCount === totalCount ?
                'Đã chọn tất cả ảnh' :
                (selectedCount === 1 ? '1 ảnh được chọn' : `${selectedCount} ảnh được chọn`);
            container.appendChild(summary);
        } else {
            // Với text: hiển thị từng tag với nút xóa
            selectedItems.forEach(item => {
                const span = document.createElement('span');
                span.className = 'badge bg-primary text-white d-flex align-items-center px-2 py-2 rounded-pill mr-1';
                span.style.height = '28px';
                span.setAttribute('data-value', item.value);
                span.innerHTML = `
                    ${item.display}
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
            if (displayType_{{ $name }} === 'text') {
                const label = option.querySelector('label').innerText.toLowerCase();
                option.style.display = label.includes(lowerKeyword) ? 'flex' : 'none';
            } else {
                // Với image có thể search theo index hoặc ẩn tính năng search
                option.style.display = 'flex';
            }
        });
    }
</script>