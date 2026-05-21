@php
    $id = $uid ?? ('input_' . str_replace(['[', ']'], '_', $name));
@endphp

<div class="mb-3">
    @if(!empty($label))
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <textarea name="{{ $name }}"
              id="{{ $id }}"
              rows="5"
              class="form-control">{{ old($name, $value ?? '') }}</textarea>
</div>
