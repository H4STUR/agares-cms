@props(['active' => false, 'icon' => null])

@php
$classes = 'nav-link d-flex align-items-center' . ($active ? ' active' : '');
@endphp

<a {{ $attributes->merge([
    'class' => $classes,
    'data-bs-toggle' => 'tab',
    'role' => 'tab',
    'aria-selected' => $active ? 'true' : 'false'
]) }}>
    @if($icon)
        <i class="bi {{ $icon }} me-1 fs-6"></i>
    @endif
    <span class="tab-title">{{ $slot }}</span>
</a>
