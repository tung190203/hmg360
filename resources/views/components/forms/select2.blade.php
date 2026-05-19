@props([
    'name' => '',
    'placeholder' => 'Chọn...',
    'value' => '',
    'messages' => [],
    'options' => [],
    'selected' => '',
    'required' => false,
    'help' => ''
])

<div class="form-group row" style="height: 33px">
    <div class="col-sm-12">
        <select class="form-control select2bs4" 
                name="{{ $name }}"
                id="{{ $name }}" 
                {{ $attributes }}>
            
            @if(is_string($options))
                {{-- Nếu truyền HTML string qua slot --}}
                {!! $options !!}
            @else
                {{-- Nếu truyền array hoặc collection --}}
                <option value="">{{ $placeholder }}</option>
                @foreach($options as $key => $option)
                    @if(is_object($option))
                        {{-- Trường hợp Collection Eloquent: $vrtour --}}
                        <option value="{{ $option->id }}" {{ old($name, $selected) == $option->id ? 'selected' : '' }}>
                            {{ $option->name }}
                        </option>
                    @elseif(is_array($option))
                        {{-- Trường hợp array associative --}}
                        <option value="{{ $option['id'] ?? $key }}" {{ old($name, $selected) == ($option['id'] ?? $key) ? 'selected' : '' }}>
                            {{ $option['name'] ?? $option['label'] ?? $option }}
                        </option>
                    @else
                        {{-- Trường hợp array đơn giản: ['1' => 'Option 1', '2' => 'Option 2'] --}}
                        <option value="{{ $key }}" {{ old($name, $selected) == $key ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
        
        @if ($messages && count($messages) > 0)
            @foreach ((array) $messages as $message)
                <span class="text-danger d-block">{{ $message }}</span>
            @endforeach
        @endif
        
        @if($help)
            <p class="text-muted">{{ $help }}</p>
        @endif
    </div>
</div>

@once
    <link href="{{ asset('backend_assets/vendor/select2/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('backend_assets/vendor/select2/custom.css') }}" rel="stylesheet"/>
    <script src="{{ asset('backend_assets/vendor/select2/select2.min.js') }}"></script>
@endonce

<script>
    $(document).ready(function () {
        $('#{{ $name }}').select2({
            placeholder: '{{ $placeholder }}',
            allowClear: false,
            width: '100%',
            language: {
                noResults: function() {
                    return "Không tìm thấy kết quả";
                },
                searching: function() {
                    return "Đang tìm kiếm...";
                }
            }
        });
    });
</script>