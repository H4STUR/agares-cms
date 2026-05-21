@php
  use Illuminate\Support\Str;

  /** @var \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator|null $articles */
  $articles = $articles ?? ($data['articles'] ?? collect());

  $site = $site ?? ($data['site'] ?? null);

  // Optional config
  $showCategory = $showCategory ?? true;
  $showExcerpt  = $showExcerpt ?? true;
  $excerptLen   = $excerptLen ?? 160;
@endphp

@if($articles && $articles->count())
  <div class="ag-blog-list">

    @foreach($articles as $article)
      @php
        $firstCat = $article->categories?->first();

        // Your preview URL style:
        // /{siteSlug}/{categoryName}/{articleId}/{slug(articleTitle)}
        $url = '#';
        if ($site && $firstCat) {
          $url = url('/' . $site->slug . '/' . $firstCat->name . '/' . $article->id . '/' . Str::slug($article->title));
        } elseif ($site) {
          // fallback if no categories
          $url = url('/' . $site->slug . '/article/' . $article->id . '/' . Str::slug($article->title));
        }

        // Excerpt: prefer explicit description/content; otherwise empty
        $raw = (string)($article->description ?? $article->content ?? '');
        $excerpt = $raw !== '' ? Str::limit(strip_tags($raw), $excerptLen) : null;

        $publishedAt = $article->published_at ?? null;
      @endphp

      <article class="ag-blog-item border rounded-4 p-3 mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div class="flex-grow-1">
            <h3 class="h5 mb-1">
              <a href="{{ $url }}" class="text-decoration-none">
                {{ $article->title }}
              </a>
            </h3>

            <div class="text-muted small d-flex flex-wrap gap-2">
              @if($showCategory && $firstCat)
                <span>
                  <i class="bi bi-tag me-1"></i>{{ $firstCat->name }}
                </span>
              @endif

              @if($publishedAt)
                <span>
                  <i class="bi bi-calendar3 me-1"></i>{{ \Illuminate\Support\Carbon::parse($publishedAt)->format('Y-m-d') }}
                </span>
              @endif
            </div>

            @if($showExcerpt && $excerpt)
              <p class="mt-2 mb-0 text-muted">
                {{ $excerpt }}
              </p>
            @endif
          </div>

          <div class="flex-shrink-0">
            <a class="btn btn-outline-primary btn-sm" href="{{ $url }}">
              {{ __('Read') }} <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </article>
    @endforeach

    {{-- If you pass a paginator instead of collection --}}
    @if(method_exists($articles, 'links'))
      <div class="mt-3">
        {{ $articles->links() }}
      </div>
    @endif

  </div>
@else
  <div class="text-muted">
    {{ __('No articles yet.') }}
  </div>
@endif
