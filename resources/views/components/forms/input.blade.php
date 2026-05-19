@props(['name', 'placeholder', 'value', 'label', 'messages' => null, 'type' => 'text', 'required' => false, 'help' => null, 'class' => null])

{{-- Kiểm tra nếu type là 'hidden' thì không cần div bọc ngoài --}}
@if($type === 'hidden')
<input type="hidden" name="{{ $name }}" value="{{ $value }}" id="{{ $name }}" {{ $attributes }}>
@else
{{-- Đây là khối code cho các type khác (text, email, password, v.v.) --}}
<div class="form-group row">
    <label class="col-sm-3 col-form-label">
        {!! $label !!}
    </label>
    <div class="col-sm-9">
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" id="{{ $name }}"
            @if($required) required @endif
            class="form-control {{ $class }} {{ !empty($messages) ? 'is-invalid' : '' }}"
            placeholder="{{ $placeholder ?? strip_tags($label) }}" {{ $attributes }}>
        @if ($messages)
        @foreach ((array) $messages as $message)
        <div class="text-danger">{{ $message }}</div>
        @endforeach
        @endif
        @if($help)
        <div class="text-muted">{{ $help }}</div>
        @endif
    </div>
</div>
@endif