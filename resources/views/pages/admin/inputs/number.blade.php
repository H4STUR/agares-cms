@php
    $id = $uid ?? ('input_' . str_replace(['[', ']'], '_', $name));
@endphp

<div class="mb-3">
    @if(!empty($label))
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <input type="number"
           name="{{ $name }}"
           id="{{ $id }}"
           value="{{ old($name, $value ?? '') }}"
           class="form-control">
</div>
