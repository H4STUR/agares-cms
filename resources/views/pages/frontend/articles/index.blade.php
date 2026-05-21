@extends('pages.frontend.base')
@section('content')

<section class="section-sm">
    <div class="container container-narrow">
      <div class="breadcrumbs"><a href="/">Home</a><span class="breadcrumbs-separator">/</span><a
          href="/{{ $data['site']->slug }}">{{ $data['site']->name }}</a><span class="breadcrumbs-separator">/</span><span>{{ $data['article']->title }}</span></div>
      <h1 style="margin: var(--space-xl) 0;">{{ $data['header']->value }}</h1>
      

        <div style="background: var(--color-bg-secondary); border-radius: var(--radius-xl); margin-bottom: var(--space-3xl); text-align: center;">
        <img
                  src="{{ asset($data['thumbnail']->value) ?: asset('assets/imgs/placeholder.png') }}"
                  alt="{{ $data['article']->title }}"
                  loading="lazy"
                  class="w-100 h-100 object-fit-cover"
                  style="border-radius: var(--radius-xl);"
                  onerror="this.onerror=null; this.src='{{ asset('assets/imgs/placeholder.png') }}';"
                >
        </div>

      <article style="font-size: var(--text-lg); line-height: 1.8;">
        <p>{!! safe_html($data['content']->value) !!}</p>
      </article>

    </div>
  </section>

@endsection