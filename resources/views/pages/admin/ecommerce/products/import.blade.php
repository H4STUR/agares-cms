<x-app-layout>
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ __('Products') }}</div>
    <div class="ps-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item">
            <a href="{{ route('admin.ecommerce.products.index') }}">{{ __('Products') }}</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('Import / Export') }}</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
      <h4 class="mb-0">{{ __('Import / Export Products') }}</h4>
      <a href="{{ route('admin.ecommerce.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Products') }}
      </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('warning'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Import result details --}}
    @if(session('import_result'))
      @php $r = session('import_result') @endphp
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
          <h6 class="card-title mb-3"><i class="bi bi-bar-chart me-2"></i>{{ __('Import Summary') }}</h6>
          <div class="row g-3 mb-3">
            <div class="col-auto">
              <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-success fs-6 px-3 py-2">{{ $r->created }}</span>
                <span class="text-muted">{{ __('created') }}</span>
              </div>
            </div>
            <div class="col-auto">
              <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary fs-6 px-3 py-2">{{ $r->updated }}</span>
                <span class="text-muted">{{ __('updated') }}</span>
              </div>
            </div>
            <div class="col-auto">
              <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-secondary fs-6 px-3 py-2">{{ $r->skipped }}</span>
                <span class="text-muted">{{ __('skipped') }}</span>
              </div>
            </div>
          </div>

          @if($r->hasErrors())
            <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto; background: #fff8f8;">
              <p class="text-danger fw-semibold mb-2">
                <i class="bi bi-x-circle me-1"></i>{{ count($r->errors) }} {{ __('row(s) had errors:') }}
              </p>
              <table class="table table-sm table-borderless mb-0">
                <thead>
                  <tr class="text-muted small">
                    <th style="width:80px">{{ __('Row') }}</th>
                    <th>{{ __('Message') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($r->errors as $err)
                    <tr>
                      <td class="font-monospace text-muted">{{ $err['row'] }}</td>
                      <td class="text-danger small">{{ $err['message'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    @endif

    <div class="row g-4">

      {{-- ── IMPORT PANEL ── --}}
      <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-1">
              <i class="bi bi-upload me-2 text-primary"></i>{{ __('Import Products') }}
            </h5>
            <p class="text-muted small mb-4">
              {{ __('Upload a CSV file. Existing products are matched by SKU first, then by name + type. New products and variants are created automatically.') }}
            </p>

            <form method="POST"
                  action="{{ route('admin.ecommerce.products.import.process') }}"
                  enctype="multipart/form-data"
                  x-data="{ dragging: false, fileName: '' }">
              @csrf

              {{-- Drop zone --}}
              <div
                class="import-dropzone rounded-3 border-2 border-dashed text-center p-5 mb-3 position-relative"
                :class="dragging ? 'dragging' : ''"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name ?? ''"
              >
                <i class="bi bi-file-earmark-spreadsheet display-5 text-muted mb-2"></i>
                <div class="fw-semibold mb-1" x-text="fileName || '{{ __('Drop CSV here or click to browse') }}'"></div>
                <div class="small text-muted">{{ __('Accepted: .csv, .txt — max 20 MB') }}</div>
                <input
                  x-ref="fileInput"
                  type="file"
                  name="csv_file"
                  accept=".csv,.txt"
                  class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                  style="cursor:pointer"
                  @change="fileName = $event.target.files[0]?.name ?? ''"
                  required
                >
              </div>

              @error('csv_file')
                <div class="text-danger small mb-2">{{ $message }}</div>
              @enderror

              {{-- Options --}}
              <div class="mb-4">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1" checked>
                  <label class="form-check-label" for="update_existing">
                    {{ __('Update existing products (matched by SKU / name)') }}
                  </label>
                </div>
                <div class="form-text ms-4">
                  {{ __('When unchecked, existing products are skipped entirely.') }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-upload"></i>
                {{ __('Start Import') }}
              </button>
            </form>
          </div>
        </div>
      </div>

      {{-- ── EXPORT + TEMPLATE PANEL ── --}}
      <div class="col-lg-5">
        <div class="d-flex flex-column gap-4">

          {{-- Export --}}
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-1">
                <i class="bi bi-download me-2 text-success"></i>{{ __('Export Products') }}
              </h5>
              <p class="text-muted small mb-4">
                {{ __('Downloads all active products as a WooCommerce-compatible CSV. Compatible with Allegro import tools and spreadsheet editors.') }}
              </p>

              <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.ecommerce.products.export') }}"
                   class="btn btn-success d-flex align-items-center gap-2">
                  <i class="bi bi-download"></i>
                  {{ __('Export All Products') }}
                </a>

                <a href="{{ route('admin.ecommerce.products.export', ['tab' => 'published']) }}"
                   class="btn btn-outline-success d-flex align-items-center gap-2">
                  <i class="bi bi-download"></i>
                  {{ __('Export Published Only') }}
                </a>

                <a href="{{ route('admin.ecommerce.products.export', ['tab' => 'draft']) }}"
                   class="btn btn-outline-secondary d-flex align-items-center gap-2">
                  <i class="bi bi-download"></i>
                  {{ __('Export Drafts Only') }}
                </a>
              </div>
            </div>
          </div>

          {{-- Template --}}
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-1">
                <i class="bi bi-file-earmark-text me-2 text-info"></i>{{ __('Import Template') }}
              </h5>
              <p class="text-muted small mb-4">
                {{ __('Download a blank CSV template with example rows showing simple products, variable products, and variation rows.') }}
              </p>
              <a href="{{ route('admin.ecommerce.products.import.template') }}"
                 class="btn btn-outline-info d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-arrow-down"></i>
                {{ __('Download Template') }}
              </a>
            </div>
          </div>

          {{-- Format reference --}}
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="bi bi-info-circle me-2 text-secondary"></i>{{ __('CSV Format Reference') }}
              </h6>
              <table class="table table-sm table-borderless mb-0 small">
                <thead class="text-muted">
                  <tr>
                    <th>{{ __('Column') }}</th>
                    <th>{{ __('Notes') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="font-monospace fw-semibold">Type</td>
                    <td><code>simple</code>, <code>variable</code>, <code>variation</code>, <code>digital</code>, <code>service</code></td>
                  </tr>
                  <tr>
                    <td class="font-monospace fw-semibold">Published</td>
                    <td><code>1</code> = published, <code>0</code> = draft</td>
                  </tr>
                  <tr>
                    <td class="font-monospace fw-semibold">Categories</td>
                    <td>Pipe-separated; use <code>&gt;</code> for hierarchy<br><small class="text-muted">e.g. <code>Electronics &gt; Phones|Accessories</code></small></td>
                  </tr>
                  <tr>
                    <td class="font-monospace fw-semibold">Tags</td>
                    <td>Pipe-separated<br><small class="text-muted">e.g. <code>new|promo|featured</code></small></td>
                  </tr>
                  <tr>
                    <td class="font-monospace fw-semibold">Parent</td>
                    <td>SKU of parent product (variation rows only)</td>
                  </tr>
                  <tr>
                    <td class="font-monospace fw-semibold">Attribute N value(s)</td>
                    <td>Pipe-separated for <code>variable</code> rows; single value for <code>variation</code> rows</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  @push('styles')
  <style>
    .import-dropzone {
      border: 2px dashed #c8cfd8;
      transition: background .15s, border-color .15s;
      cursor: pointer;
    }
    .import-dropzone:hover,
    .import-dropzone.dragging {
      background: #f0f6ff;
      border-color: #0d6efd;
    }
    .border-dashed { border-style: dashed !important; }
  </style>
  @endpush
</x-app-layout>
