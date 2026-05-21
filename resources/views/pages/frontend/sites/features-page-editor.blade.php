@extends('pages.frontend.base')

@section('content')
<section class="section-sm">
  <div class="container container-narrow">

    {{-- Breadcrumbs --}}
    <div class="breadcrumbs mb-3">
      <a href="/">Home</a>
      <span class="breadcrumbs-separator">/</span>
      <span>Page Editor</span>
    </div>

    {{-- Page title --}}
    <h1 style="margin: var(--space-xl) 0;">Page Editor</h1>

    {{-- Intro --}}
    <div class="mb-4" style="font-size: var(--text-lg); line-height: 1.8;">
      <p class="mb-0">
        {{ $data['header']->value ?? '' }}
      </p>
    </div>

    {{-- IMAGE PLACEHOLDER --}}
    @php
    $thumbPath = $data['thumbnail']->value ?? null;
    @endphp

    <div class="mb-5" style="background: var(--color-bg-secondary); border-radius: var(--radius-xl); overflow:hidden;">
    @if($thumbPath)
        <img
        src="{{ asset($thumbPath) }}"
        alt="Feature preview"
        class="w-100"
        style="display:block; object-fit:cover;"
        loading="lazy"
        onerror="this.onerror=null; this.src='{{ asset('assets/imgs/placeholder.png') }}';"
        >
    @else
        <div class="p-4 text-center"
            style="border: 2px dashed var(--color-border); border-radius: var(--radius-xl);">
        <div class="text-muted" style="font-weight: 600;">Screenshot / GIF placeholder</div>
        <div class="text-muted small">
            Add a blog manager screenshot here (categories, article editor, list view, etc.)
        </div>
        <div style="height: 320px;"></div>
        </div>
    @endif
    </div>

    {{-- Key features --}}
    {!! safe_html($data['content']->value ?? '') !!}
  </div>
</section>
@endsection
