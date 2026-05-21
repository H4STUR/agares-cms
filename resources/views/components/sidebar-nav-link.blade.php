{{-- @props(['active' => false])

@php
$classes = 'block w-full py-3 px-6 text-lg font-semibold rounded-lg transition-all duration-200 ease-in-out ' .
           ($active
               ? 'text-gray-900 bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100'
               : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a> --}}


@props([
  'href' => '#',
  'active' => false,
  'target' => null,
  'class' => '',
  'title' => '',
])

<a
    href="{{ $href }}"
    @if($target) target="{{ $target }}" @endif
    class="block px-4 py-2 rounded-md transition-all {{ $active ? 'bg-gray-200 dark:bg-gray-700' : '' }} {{ $class }}"
    title="{{ $title }}"
>
    {{ $slot }}
</a>
