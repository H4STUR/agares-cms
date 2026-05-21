<x-app-layout>
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ __('Products') }}</div>
  </div>

  <div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h4 class="mb-0">{{ __('Manage products') }}</h4>
      </div>

      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.ecommerce.products.create') }}" class="btn btn-success d-flex align-items-center gap-2">
          <i class="material-icons-outlined">add</i>
          <span>{{ __('Add product') }}</span>
        </a>

        {{-- Import / Export dropdown --}}
        <div class="dropdown">
          <button class="btn btn-outline-secondary d-flex align-items-center gap-2 dropdown-toggle"
                  type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-down-up"></i>
            <span class="d-none d-sm-inline">{{ __('Import / Export') }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">{{ __('Export') }}</h6></li>
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2"
                 href="{{ route('admin.ecommerce.products.export', array_filter(['tab' => $tab, 'q' => $q])) }}">
                <i class="bi bi-download text-success"></i>
                {{ __('Export current view') }}
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2"
                 href="{{ route('admin.ecommerce.products.export') }}">
                <i class="bi bi-download text-muted"></i>
                {{ __('Export all products') }}
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">{{ __('Import') }}</h6></li>
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2"
                 href="{{ route('admin.ecommerce.products.import') }}">
                <i class="bi bi-upload text-primary"></i>
                {{ __('Import from CSV') }}
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2"
                 href="{{ route('admin.ecommerce.products.import.template') }}">
                <i class="bi bi-file-earmark-arrow-down text-info"></i>
                {{ __('Download template') }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      {{-- Search --}}
      <form method="GET"
            action="{{ route('admin.ecommerce.products.index') }}"
            class="ms-auto flex-grow-1 agares-page-search"
            style="min-width: 260px; max-width: 520px;">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="position-relative">
          <input
            name="q"
            value="{{ request('q') }}"
            class="form-control rounded-5 px-5 agares-search-control"
            type="text"
            placeholder="{{ __('Search products…') }}"
            autocomplete="off"
          >

          <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 text-muted"
                style="pointer-events:none;">
            search
          </span>

          @if(request('q'))
            <a href="{{ route('admin.ecommerce.products.index', ['tab' => $tab]) }}"
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
           href="{{ route('admin.ecommerce.products.index', ['tab' => 'published', 'q' => request('q')]) }}">
          {{ __('Published') }} <span class="badge text-bg-secondary ms-1">{{ $counts['published'] ?? 0 }}</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ $tab === 'draft' ? 'active' : '' }}"
           href="{{ route('admin.ecommerce.products.index', ['tab' => 'draft', 'q' => request('q')]) }}">
          {{ __('Drafts') }} <span class="badge text-bg-secondary ms-1">{{ $counts['draft'] ?? 0 }}</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}"
           href="{{ route('admin.ecommerce.products.index', ['tab' => 'all', 'q' => request('q')]) }}">
          {{ __('All') }} <span class="badge text-bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
        </a>
      </li>

      <li class="nav-item ms-auto">
        <a class="nav-link {{ $tab === 'trash' ? 'active' : '' }}"
           href="{{ route('admin.ecommerce.products.index', ['tab' => 'trash', 'q' => request('q')]) }}">
          {{ __('Bin') }} <span class="badge text-bg-danger ms-1">{{ $counts['trash'] ?? 0 }}</span>
        </a>
      </li>
    </ul>

    {{-- List --}}
    <div class="card">
      <div class="card-body">
        @if($products->isEmpty())
          <div class="text-muted">{{ __('No products found.') }}</div>
        @else
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th class="text-end">{{ __('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($products as $product)
                  @php
                    $isTrashed = !is_null($product->deleted_at);
                    $status = $isTrashed ? 'trashed' : ($product->status ?? 'draft');
                  @endphp

                  <tr>
                    <td class="min-w-0">
                      <div class="fw-semibold">{{ $product->name }}</div>
                      <div class="small text-muted">
                        <span class="me-1">/</span><span class="font-monospace">{{ $product->slug }}</span>
                        <span class="d-none d-sm-inline"> • {{ __('Updated') }} {{ $product->updated_at?->format('Y-m-d H:i') }}</span>
                      </div>
                    </td>

                    <td>
                      <span class="badge
                        @if($status === 'published') text-bg-success
                        @elseif($status === 'draft') text-bg-secondary
                        @elseif($status === 'trashed') text-bg-danger
                        @else text-bg-warning
                        @endif
                      ">
                        {{ ucfirst($status) }}
                      </span>
                    </td>

                    <td>{{ $product->product_type }}</td>

                    <td class="text-end">
                      <div class="d-flex flex-wrap justify-content-end gap-2">
                        @if(!$isTrashed)
                          <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.products.edit', $product) }}">
                            <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                          </a>

                          <form method="POST" action="{{ route('admin.ecommerce.products.destroy', $product) }}"
                                onsubmit="return confirm('{{ __('Move to bin?') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                              <i class="bi bi-trash me-1"></i>{{ __('Bin') }}
                            </button>
                          </form>
                        @else
                          {{-- Optional: add restore + force delete later if you create routes --}}
                          <span class="text-muted small">{{ __('In bin') }}</span>
                        @endif
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $products->links() }}
          </div>
        @endif
      </div>
    </div>

  </div>

  @push('styles')
  <style>
    .font-monospace { font-size: 12px; opacity: .85; }
    .nav-tabs .badge { font-weight: 600; }
    .form-control.rounded-5 { min-height: 42px; }
  </style>
  @endpush
</x-app-layout>
