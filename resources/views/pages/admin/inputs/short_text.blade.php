@php
    $id = $uid ?? ('input_' . str_replace(['[', ']'], '_', $name));
@endphp

<div class="mb-3">
    @if(!empty($label))
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <input type="text"
           name="{{ $name }}"
           id="{{ $id }}"
           value="{{ old($name, $value ?? '') }}"
           maxlength="255"
           class="form-control">
</div>
