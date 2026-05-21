<x-app-layout>
<div class="card rounded-4 shadow-sm">
    <div class="card-body">

        {{-- Top Button Group --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

    {{-- LEFT ACTIONS --}}
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.sites.edit', $data['site']->id) }}">
            <x-primary-button>Edit</x-primary-button>
        </a>

        <button
          type="button"
          class="btn btn-danger"
          data-confirm
          data-action="{{ route('admin.sites.delete', $data['site']->id) }}"
          data-method="DELETE"
          data-variant="danger"
          data-title="Delete site"
          data-body="Are you sure you want to delete"
          data-name="{{ $data['site']->name }}"
        >
          Delete
        </button>

        <a href="{{ route('admin.categories.create', ['site' => $data['site']->id]) }}">
            <x-success-button>Add Category</x-success-button>
        </a>

        @if($data['site']->categories->count())
            <a href="{{ route('admin.articles.create', ['site' => $data['site']->id]) }}">
                <x-success-button>Add Article</x-success-button>
            </a>
        @endif
    </div>

    {{-- RIGHT ACTION --}}
    <div>
        <button
          type="button"
          class="btn btn-primary"
          data-confirm
          data-action="{{ route('admin.sites.duplicate', $data['site']->id) }}"
          data-method="POST"
          data-variant="primary"
          data-title="Duplicate site"
          data-body="Do you want to duplicate"
          data-name="{{ $data['site']->name }}"
          data-confirm-text="Yes, duplicate"
        >
          <i class="bi bi-copy me-1"></i> Duplicate
        </button>
    </div>

</div>


        {{-- Dropdowns Section --}}
        <div class="accordion mb-4" id="siteAccordion">

            {{-- Content Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingContent">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContent" aria-expanded="false" aria-controls="collapseContent">
                        Content
                    </button>
                </h2>
                <div id="collapseContent" class="accordion-collapse collapse" aria-labelledby="headingContent" data-bs-parent="#siteAccordion">
                    <div class="accordion-body">
                        <p>Content 1</p>
                        <p>Content 2</p>
                    </div>
                </div>
            </div>

            {{-- Categories Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCategories">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="false" aria-controls="collapseCategories">
                        Categories
                    </button>
                </h2>
                <div id="collapseCategories" class="accordion-collapse collapse" aria-labelledby="headingCategories" data-bs-parent="#siteAccordion">
                    <div class="accordion-body">
                        @forelse($data['site']->categories as $category)
                          <div class="card border-0 shadow-sm mb-2 rounded-3">
                            <div class="card-body py-2 px-3">

                              <div class="d-flex justify-content-between align-items-center gap-3">

                                {{-- Left --}}
                                <div class="flex-grow-1 min-w-0">
                                  <div class="fw-semibold text-truncate">
                                    {{ $category->name }}
                                  </div>

                                  <div class="text-muted small">
                                    {{ $category->articles->count() }} articles
                                  </div>
                                </div>

                                {{-- Right actions --}}
                                <div class="d-flex gap-2 flex-shrink-0">

                                  <a href="{{ route('admin.categories.edit', ['site' => $data['site']->id, 'category' => $category->id]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                  </a>

                                  <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    data-confirm
                                    data-action="{{ route('admin.categories.delete', ['site' => $data['site']->id, 'category' => $category->id]) }}"
                                    data-method="DELETE"
                                    data-variant="danger"
                                    data-title="Delete category"
                                    data-body="Are you sure you want to delete"
                                    data-name="{{ $category->name }}"
                                    data-confirm-text="Yes, delete"
                                  >
                                    <i class="bi bi-trash"></i>
                                  </button>

                                </div>
                              </div>

                            </div>
                          </div>
                        @empty
                          <p class="text-muted">No categories available for this site.</p>
                        @endforelse

                    </div>
                </div>
            </div>

            {{-- Articles Section --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingArticles">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArticles" aria-expanded="true" aria-controls="collapseArticles">
                        Articles
                    </button>
                </h2>
                <div id="collapseArticles" class="accordion-collapse collapse show" aria-labelledby="headingArticles" data-bs-parent="#siteAccordion">
                    <div class="accordion-body">
                        @php
  $reorderUrl = route('admin.articles.reorder', $data['site']->id);
@endphp

@if($data['site']->articles->isEmpty())
  <p class="text-muted">No articles available for this site.</p>
@else
  <div class="text-muted small mb-2">Drag & drop articles to change order.</div>

  <div id="articles-list"
       class="list-group"
       data-reorder-url="{{ $reorderUrl }}">
    @foreach($data['site']->articles as $article)
      <div class="list-group-item draggable p-0 mb-2 rounded"
           draggable="true"
           data-article-id="{{ $article->id }}">

        <div class="card border-0 shadow-sm mb-0">
          <div class="card-body d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted" title="Drag to reorder" style="cursor:grab;">
                  <i class="bi bi-grip-vertical"></i>
                </span>

                <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                  <span>{{ $article->title }}</span>

                  <span class="badge
                    @if($article->status === 'published') bg-success
                    @elseif($article->status === 'draft') bg-secondary
                    @elseif($article->status === 'scheduled') bg-warning text-dark
                    @else bg-dark
                    @endif
                  ">
                    {{ ucfirst($article->status ?? 'draft') }}
                  </span>
                </div>
              </div>

              <div class="mt-2 d-flex flex-wrap gap-1">
                @foreach($article->categories as $category)
                  <span class="badge bg-light text-muted border">{{ $category->name }}</span>
                @endforeach
              </div>
            </div>

            <div class="d-flex gap-2 flex-shrink-0">

              <a href="{{ route('admin.articles.edit', ['site' => $data['site']->id, 'article' => $article->id]) }}"
                 class="btn btn-primary"
                 title="Edit article">
                <i class="bi bi-pencil-square me-1"></i> Edit
              </a>

              <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    title="Duplicate article"
                    data-confirm
                    data-action="{{ route('admin.articles.duplicate', [$data['site']->id, $article->id]) }}"
                    data-method="POST"
                    data-variant="primary"
                    data-title="Duplicate article"
                    data-body="Do you want to duplicate"
                    data-name="{{ $article->title }}"
                    data-confirm-text="Yes, duplicate"
                    >
                    <i class="bi bi-copy"></i> 
                </button>

                {{-- Publish / Draft --}}
                @if(($article->status ?? 'draft') === 'draft')
                  <form method="POST" action="{{ route('admin.articles.update', [$data['site']->id, $article->id]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="published">
                    <button type="submit" class="btn btn-outline-success " title="Publish">
                      <i class="bi bi-upload me-1"></i> Publish
                    </button>
                  </form>
                @elseif($article->status === 'published')
                  <form method="POST" action="{{ route('admin.articles.update', [$data['site']->id, $article->id]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="draft">
                    <button type="submit" class="btn btn-outline-secondary " title="Set as draft">
                      <i class="bi bi-file-earmark me-1"></i> Draft
                    </button>
                  </form>
                @endif


                <button
                    type="button"
                    class="btn btn-danger"
                    title="Delete article"
                    data-confirm
                    data-action="{{ route('admin.articles.delete', [$data['site']->id, $article->id]) }}"
                    data-method="DELETE"
                    data-variant="danger"
                    data-title="Delete article"
                    data-body="Are you sure you want to delete"
                    data-name="{{ $article->title }}"
                    >
                    <i class="bi bi-trash"></i>
                </button>
            </div>
          </div>
        </div>

      </div>
    @endforeach
  </div>

  <div class="text-muted small mt-2" id="articles-reorder-msg"></div>
@endif

                    </div>
                </div>
            </div>

        </div>

        {{-- Site Details --}}
        <div class="card border rounded-3">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ $data['site']->name }} – {{ __('Details') }}</h5>

                <p class="mb-2">
                    <strong>{{ __('URL:') }}</strong>
                    <a href="{{ route('site.show', $data['site']->slug) }}" target="_blank">
                        {{ route('site.show', $data['site']->slug) }}
                    </a>
                </p>

                <p class="mb-2">
                    <strong>{{ __('Description:') }}</strong>
                    {{ $data['site']->description ?? __('No description available.') }}
                </p>

                <p class="text-muted small mb-0">
                    {{ __('Created by:') }} {{ $data['site']->createdBy->username ?? 'Unknown' }}
                </p>
            </div>
        </div>

    </div>
</div>




@push('styles')

<style>
  .card.shadow-sm {
  transition: transform .12s ease, box-shadow .12s ease;
}

.card.shadow-sm:hover {
  transform: translateY(-1px);
  box-shadow: 0 .25rem .75rem rgba(0,0,0,.12);
}

</style>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('articles-list');
  if (!list) return;

  if (list.dataset.dndBound === '1') return;
  list.dataset.dndBound = '1';

  let dragged = null;

  list.addEventListener('dragstart', (e) => {
    dragged = e.target.closest('.draggable');
    if (!dragged) return;
    dragged.classList.add('opacity-50');
  });

  list.addEventListener('dragover', (e) => {
    e.preventDefault();
    const target = e.target.closest('.draggable');
    if (!dragged || !target || target === dragged) return;

    const rect = target.getBoundingClientRect();
    const next = (e.clientY - rect.top) > rect.height / 2;
    list.insertBefore(dragged, next ? target.nextSibling : target);
  });

  list.addEventListener('dragend', async () => {
    if (!dragged) return;
    dragged.classList.remove('opacity-50');

    const msg = document.getElementById('articles-reorder-msg');
    if (msg) msg.textContent = 'Saving order...';

    const url = list.getAttribute('data-reorder-url');
    const ids = Array.from(list.querySelectorAll('.draggable'))
      .map(el => parseInt(el.getAttribute('data-article-id'), 10))
      .filter(n => Number.isFinite(n));

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ids })
      });

      const data = await res.json().catch(() => null);

      if (!res.ok || (data && data.success === false)) {
        if (msg) msg.textContent = (data && data.message) ? data.message : 'Failed to save order.';
      } else {
        if (msg) msg.textContent = 'Order saved.';
        setTimeout(() => { if (msg) msg.textContent = ''; }, 1200);
      }
    } catch (e) {
      console.error(e);
      if (msg) msg.textContent = 'Failed to save order.';
    }

    dragged = null;
  });
});
</script>
@endpush
</x-app-layout>
