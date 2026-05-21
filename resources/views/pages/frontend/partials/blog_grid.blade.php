@php
  use Illuminate\Support\Str;

  /** @var \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $articles */
  $articles = $articles ?? ($data['articles'] ?? collect());

  /** @var \App\Models\Site|null $site */
  $site = $site ?? ($data['site'] ?? null);

  $placeholder = asset('assets/imgs/placeholder.png');
  $thumbRatio  = $thumbRatio ?? '16x9';

  $ratioClass = match ($thumbRatio) {
    '1x1' => 'ratio ratio-1x1',
    '4x3' => 'ratio ratio-4x3',
    default => 'ratio ratio-16x9',
  };
@endphp

@if($articles->count())
  <div class="ag-blog-grid">
    <div class="row g-4">
      @foreach($articles as $article)
        @php
          $descInstance = $article->inputInstances->firstWhere('variable', 'description');
          $desc = $descInstance?->value ?? '';
          
          $firstCat = $article->categories?->first();

          $url = ($site && $firstCat)
            ? route('article.show', [
                'site'        => $site->slug,
                'category'    => $firstCat->name,
                'articleId'   => $article->id,
                'articleName' => Str::slug($article->title),
              ])
            : '#';
          $thumb = $article->thumbnail_url ?? $placeholder;
        @endphp

        <div class="col-12 col-md-6 col-lg-4">
          <a href="{{ $url }}" class="ag-blog-card text-decoration-none">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden p-0">
              <div class="{{ $ratioClass }}">
                <img
                  src="{{ $thumb }}"
                  alt="{{ $article->title }}"
                  loading="lazy"
                  class="w-100 h-100 object-fit-cover"
                  onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
              </div>

              <div class="card-body d-flex flex-column">
                <h3 class="h5 mb-2 ag-line-2">{{ $article->title }}</h3>
                <p class="text-muted mb-0 ag-line-3">
                  {!! $desc !!}
                </p>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>

    @if(method_exists($articles, 'links'))
      <div class="mt-4">
        {{ $articles->links() }}
      </div>
    @endif
  </div>

  <style>
    .ag-blog-card { display:block;height:100%; }
    .ag-blog-card .card { transition:.15s; }
    .ag-blog-card:hover .card {
      transform: translateY(-2px);
      box-shadow: 0 .75rem 2rem rgba(0,0,0,.10);
    }
    .ag-line-2, .ag-line-3 {
      display:-webkit-box;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    .ag-line-2 { -webkit-line-clamp:2; }
    .ag-line-3 { -webkit-line-clamp:3; }
    .object-fit-cover { object-fit:cover; }
  </style>
@else
  <div class="text-muted">{{ __('No articles yet.') }}</div>
@endif
