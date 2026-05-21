@extends('pages.frontend.base')

@section('content')

  <section class="section-sm" style="background: var(--color-bg-secondary);">
    <div class="container text-center">
        @if(!empty($data['header']->value))
            <h1 class="cms-title">
                {{ $data['header']->value }}
            </h1>
        @endif
        {{-- <p style="font-size: var(--text-lg); max-width: 700px; margin: 0 auto;">
            We're building the content management system that developers and creators deserve.
        </p> --}}
    </div>
</section>

@if(!empty($data['content']->value))
<section class="section-sm">
  <div class="container container-narrow">

    <div class="card cms-content">

      {{-- Content (HTML) --}}
      {!! safe_html($data['content']->value ?? '') !!}

    </div>

  </div>
</section>
@endif
{{-- Gallery --}}
@if(!empty($data['gallery']->value))
<section class="section-sm">
  <div class="container container-narrow">
    <div class="card">
      {!! $data['gallery']->value !!}
    </div>
  </div>
</section>
@endif

{{-- Files --}}
@if(!empty($data['files']->value))
<section class="section-sm">
  <div class="container container-narrow">
    <div class="card">
      {!! $data['files']->value !!}
    </div>
  </div>
</section>
@endif

@endsection
