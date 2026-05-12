@props([
    'permission' => null,
    'message' => null,
])

@php
    $isReadOnly = $permission ? is_read_only($permission) : is_viewer();
    $defaultMessage = __('Read-only mode — your role can view this page but not change it.');
@endphp

@if($isReadOnly)
    <div {{ $attributes->merge(['class' => 'alert d-flex align-items-center gap-2 mb-3', 'role' => 'alert', 'style' => 'background:#fff3cd;border:1px solid #ffe69c;color:#664d03;border-radius:6px;padding:.6rem .85rem;']) }}>
        <i class="material-icons-outlined" style="font-size:18px;">visibility</i>
        <div class="small">{{ $message ?? $defaultMessage }}</div>
    </div>
@endif
