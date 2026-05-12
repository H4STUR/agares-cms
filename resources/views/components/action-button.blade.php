@props([
    'permission' => null,
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'disabledTitle' => null,
])

@php
    // Block when user lacks the manage permission.
    $blocked = $permission ? ! auth()->user()?->can($permission) : false;
    // Owner bypasses everything via Gate::before — $blocked already reflects that.

    $base = 'btn btn-' . $variant;
    if ($blocked) {
        $base .= ' opacity-50 pe-none';
    }

    $title = $blocked
        ? ($disabledTitle ?? __('Read-only — you don’t have permission for this action'))
        : ($attributes->get('title') ?? '');
@endphp

@if($href && ! $blocked)
    <a href="{{ $href }}"
       {{ $attributes->except(['title'])->merge(['class' => $base]) }}
       @if($title) title="{{ $title }}" @endif>
        {{ $slot }}
    </a>
@elseif($href && $blocked)
    <span
       {{ $attributes->except(['title','class'])->merge(['class' => $base, 'aria-disabled' => 'true', 'tabindex' => '-1']) }}
       title="{{ $title }}">
        {{ $slot }}
    </span>
@else
    <button type="{{ $type }}"
            {{ $attributes->except(['title'])->merge(['class' => $base]) }}
            @if($blocked) disabled aria-disabled="true" @endif
            @if($title) title="{{ $title }}" @endif>
        {{ $slot }}
    </button>
@endif
