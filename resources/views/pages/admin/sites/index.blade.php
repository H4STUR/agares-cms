<x-app-layout>
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ __('Sites') }}</div>
  </div>

  <div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h4 class="mb-0">{{ __('Manage pages') }}</h4>
      </div>

      @can('manage sites')
      <a href="{{ route('admin.sites.create') }}" class="btn btn-success d-flex align-items-center gap-2">
        <i class="material-icons-outlined">add</i>
        <span>{{ __('Add New Site') }}</span>
      </a>
      @endcan

      <form method="GET"
          action="{{ route('admin.sites') }}"
          class="ms-auto flex-grow-1 agares-page-search"
          style="min-width: 260px; max-width: 520px;">
      <input type="hidden" name="tab" value="{{ $tab }}">

      <div class="position-relative">
        <input
          name="q"
          value="{{ request('q') }}"
          class="form-control rounded-5 px-5 agares-search-control"
          type="text"
          placeholder="{{ __('Search pages…') }}"
          autocomplete="off"
        >

        <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 text-muted"
              style="pointer-events:none;">
          search
        </span>

        @if(request('q'))
          <a href="{{ route('admin.sites', ['tab' => $tab]) }}"
            class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 text-muted agares-search-close"
            style="text-decoration:none;"
            title="{{ __('Clear') }}"
          >close</a>
        @else
          <span class="material-icons-outlined position-absolute me-3 translate-middle-y end-0 top-50 text-muted"
                style="pointer-events:none;">
            close
          </span>
        @endif
      </div>
    </form>






    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs nav-primary mb-3">
      <li class="nav-item">
        <a class="nav-link {{ $tab === 'published' ? 'active' : '' }}"
           href="{{ route('admin.sites', ['tab' => 'published', 'q' => request('q')]) }}">
          {{ __('Published') }} <span class="badge text-bg-secondary ms-1">{{ $counts['published'] ?? 0 }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $tab === 'draft' ? 'active' : '' }}"
           href="{{ route('admin.sites', ['tab' => 'draft', 'q' => request('q')]) }}">
          {{ __('Drafts') }} <span class="badge text-bg-secondary ms-1">{{ $counts['draft'] ?? 0 }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}"
           href="{{ route('admin.sites', ['tab' => 'all', 'q' => request('q')]) }}">
          {{ __('All') }} <span class="badge text-bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
        </a>
      </li>
      <li class="nav-item ms-auto">
        <a class="nav-link {{ $tab === 'trash' ? 'active' : '' }}"
           href="{{ route('admin.sites', ['tab' => 'trash', 'q' => request('q')]) }}">
          {{ __('Bin') }} <span class="badge text-bg-danger ms-1">{{ $counts['trash'] ?? 0 }}</span>
        </a>
      </li>
    </ul>

    {{-- List --}}
    @if($sites->isEmpty())
      <div class="text-muted">{{ __('No sites found.') }}</div>
    @else
      <div class="row g-3">
        @foreach($sites as $site)
          @php
            $isTrashed = !is_null($site->deleted_at);
            $status = $isTrashed ? 'trashed' : ($site->status ?? 'draft');
          @endphp

          <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 14px;">
              <div class="card-body py-3">

                    {{-- Top row: name + slug (left) | status badge (right) --}}
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                        <div class="fw-semibold text-truncate">
                            <a href="{{ route('admin.sites.show', $site->id) }}" class="fw-semibold text-truncate text-decoration-none text-reset d-flex">
                                @if ($site->slug == $settings['home_url'])
                                  <i class="material-icons-outlined mx-2" height="10">home</i>
                                @endif
                                @if (!empty($shopUrl) && $site->slug == $shopUrl)
                                  <i class="material-icons-outlined mx-2" height="10">storefront</i>
                                @endif
                                <span>{{ $site->name }}</span>
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small text-truncate">
                            <span class="me-1">/</span><span class="font-monospace">{{ $site->slug }}</span>
                            </span>

                            {{-- inline updated (no extra row) --}}
                            <span class="text-muted small d-none d-sm-inline">
                            • {{ __('Updated') }} {{ $site->updated_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        </div>

                        <span class="badge
                        @if($status === 'published') text-bg-success
                        @elseif($status === 'draft') text-bg-secondary
                        @elseif($status === 'trashed') text-bg-danger
                        @else text-bg-warning
                        @endif
                        ">
                        {{ ucfirst($status) }}
                        </span>
                    </div>

                    {{-- Bottom row: actions toolbar (tight) --}}
                    <div class="d-flex align-items-center justify-content-between mt-2 gap-2">

                        {{-- Left: preview --}}
                        <div class="d-flex align-items-center gap-2">
                        @if(!$isTrashed)
                            <a href="{{ url('/'.$site->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
                            </a>
                        @else
                            @can('view unpublished content')
                            <a href="{{ url('/'.$site->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
                            </a>
                            @endcan
                        @endif
                        </div>

                        {{-- Right: compact actions (wrap nicely) --}}
                        @can('manage sites')
                        <div class="d-flex flex-wrap justify-content-end gap-2">

                            <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-info me-1"></i>{{ __('info') }}
                            </a>

                            <a href="{{ route('admin.sites.edit', $site->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                            </a>

                            <form method="POST" action="{{ route('admin.sites.duplicate', $site->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-files me-1"></i>{{ __('Duplicate') }}
                            </button>
                            </form>

                            @if(!$isTrashed && $site->status === 'published')
                            <form method="POST" action="{{ route('admin.sites.update', $site->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="draft">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                {{ __('Draft') }}
                                </button>
                            </form>
                            @elseif(!$isTrashed && $site->status === 'draft')
                            <form method="POST" action="{{ route('admin.sites.update', $site->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                {{ __('Publish') }}
                                </button>
                            </form>
                            @endif

                            @if(!$isTrashed)
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-confirm
                                    data-action="{{ route('admin.sites.delete', $site->id) }}"
                                    data-method="DELETE"
                                    data-variant="danger"
                                    data-title="{{ __('Move to bin') }}"
                                    data-body="{{ __('Move this site to bin?') }}"
                                    data-name="{{ $site->name }}"
                                    data-confirm-text="{{ __('Yes, move') }}">
                                <i class="bi bi-trash me-1"></i>{{ __('Bin') }}
                            </button>
                            @else
                            <button type="button"
                                    class="btn btn-outline-success btn-sm"
                                    data-confirm
                                    data-action="{{ route('admin.sites.restore', $site->id) }}"
                                    data-method="POST"
                                    data-variant="success"
                                    data-title="{{ __('Restore site') }}"
                                    data-body="{{ __('Restore this site from bin?') }}"
                                    data-name="{{ $site->name }}"
                                    data-confirm-text="{{ __('Yes, restore') }}">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Restore') }}
                            </button>

                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    data-confirm
                                    data-action="{{ route('admin.sites.forceDelete', $site->id) }}"
                                    data-method="DELETE"
                                    data-variant="danger"
                                    data-title="{{ __('Delete permanently') }}"
                                    data-body="{{ __('This cannot be undone. Delete permanently?') }}"
                                    data-name="{{ $site->name }}"
                                    data-confirm-text="{{ __('Yes, delete') }}">
                                <i class="bi bi-x-octagon me-1"></i>{{ __('Delete') }}
                            </button>
                            @endif

                        </div>
                        @endcan

                    </div>
                    </div>

          </div>
        @endforeach
      </div>

      <div class="mt-3">
        {{ $sites->links() }}
      </div>
    @endif
  </div>

  @push('styles')
  <style>
    .font-monospace { font-size: 12px; opacity: .85; }
    .nav-tabs .badge { font-weight: 600; }

    /* Slightly nicer input height in this theme */
    .form-control.rounded-5 { min-height: 42px; }
  </style>
  @endpush


</x-app-layout>
