<x-app-layout>

    @php
        $disableFancyFileUpload = true;
    @endphp

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Media') }}</div>
    </div>

    @php
        // Helper: for new setup we usually store file_path like "uploads/galleries/12/name.jpg"
        // and maybe some older entries have "storage/..." or already a full url in "url" column.
        $publicUrl = function ($item) {
            // if you have url column and it's filled, prefer it
            if (!empty($item->url)) {
                return $item->url;
            }

            $path = $item->file_path ?? '';

            if ($path === '') return '';

            // If path already looks like a URL
            if (preg_match('~^https?://~i', $path)) return $path;

            // If it already starts with "/" it's already public
            if (str_starts_with($path, '/')) return $path;

            // If your old system stored "storage/..." in public, keep as asset
            // Otherwise for disk-based paths you might have to use Storage::url,
            // but we avoid it because new system uses public_path().
            return asset($path);
        };

        $isImage = function ($item) {
            $mime = strtolower($item->mime_type ?? '');
            return str_starts_with($mime, 'image/');
        };

        $prettySize = function ($bytes) {
            $bytes = (int)($bytes ?? 0);
            if ($bytes <= 0) return '0 KB';
            $kb = $bytes / 1024;
            if ($kb < 1024) return number_format($kb, 2) . ' KB';
            return number_format($kb / 1024, 2) . ' MB';
        };
    @endphp

    <div class="container-fluid">

        {{-- Upload (Fancy) --}}
        <div class="card">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 text-uppercase">{{ __('Fancy File Upload') }}</h6>
                        <small class="text-muted">{{ __('Drag & drop, multiple files, progress, instant upload') }}</small>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}
                    </button>
                </div>

                <hr>

                {{-- FilePond input --}}
                <input
                id="fancy-file-upload"
                type="file"
                name="file"
                multiple
                accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.ppt,.pptx,.txt,.xlsx,.xls,.rar,.zip"
                />

                <div class="form-text text-muted mt-2">
                    {{ __('Max size and allowed types are enforced server-side too.') }}
                </div>
            </div>
        </div>


        {{-- Media Grid --}}
        <div class="row row-cols-1 row-cols-md-4 g-4 mt-4">
            @foreach ($media as $item)
                @php
                    $url = $publicUrl($item);
                    $img = $isImage($item);
                @endphp

                <div class="col">
                    <div
                        class="card h-100 shadow-sm cursor-pointer"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-media-{{ $item->id }}"
                        style="transition: transform 0.2s;"
                        onmouseover="this.style.transform='scale(1.02)'"
                        onmouseout="this.style.transform='scale(1)'"
                    >
                        @if ($img && $url)
                            <img src="{{ $url }}"
                                 class="card-img-top object-fit-cover"
                                 alt="{{ $item->file_name ?? 'media' }}"
                                 style="height: 160px;">
                        @else
                            <div class="d-flex justify-content-center align-items-center bg-light" style="height: 160px;">
                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                            </div>
                        @endif

                        <div class="card-body">
                            <h6 class="card-title text-truncate mb-1">{{ $item->file_name ?? ('#'.$item->id) }}</h6>
                            <p class="card-text small text-muted mb-0">Size: {{ $prettySize($item->size ?? 0) }}</p>
                            <p class="card-text small text-muted mb-0">Type: {{ $item->mime_type ?? '-' }}</p>
                            <p class="card-text small text-muted">Path: <code class="small">{{ $item->file_path ?? '-' }}</code></p>
                        </div>
                    </div>

                    {{-- Modal --}}
                    <div class="modal fade" id="modal-media-{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel{{ $item->id }}">{{ __('File Details') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body row g-4">

                                    {{-- Preview --}}
                                    <div class="col-md-5 text-center">
                                        @if ($img && $url)
                                            <img src="{{ $url }}" class="img-fluid rounded" alt="{{ $item->file_name ?? 'media' }}">
                                        @else
                                            <div class="d-flex justify-content-center align-items-center bg-light rounded" style="height: 200px;">
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Details --}}
                                    <div class="col-md-7">
                                        <ul class="list-unstyled small">
                                            <li><strong>ID:</strong> {{ $item->id }}</li>
                                            <li><strong>Name:</strong> {{ $item->file_name ?? '-' }}</li>
                                            <li><strong>Size:</strong> {{ $prettySize($item->size ?? 0) }}</li>
                                            <li><strong>MIME:</strong> {{ $item->mime_type ?? '-' }}</li>
                                            <li><strong>Public URL:</strong>
                                                <code class="d-block text-break">{{ $url ?: '-' }}</code>
                                            </li>
                                            <li><strong>Stored path:</strong>
                                                <code class="d-block text-break">{{ $item->file_path ?? '-' }}</code>
                                            </li>
                                        </ul>

                                        @if($img)
                                            <form action="{{ route('admin.media.update', $item->id) }}" method="POST" class="mt-3">
                                                @csrf
                                                @method('PATCH')

                                                <div class="mb-2">
                                                <label class="form-label mb-1">{{ __('Alt text') }}</label>
                                                <input type="text"
                                                        name="alternative"
                                                        class="form-control"
                                                        value="{{ old('alternative', $item->alternative ?? '') }}"
                                                        placeholder="{{ __('Short alt text for accessibility') }}">
                                                </div>

                                                <div class="mb-2">
                                                <label class="form-label mb-1">{{ __('Description') }}</label>
                                                <textarea name="description"
                                                            class="form-control"
                                                            rows="3"
                                                            placeholder="{{ __('Optional description') }}">{{ old('description', $item->description ?? '') }}</textarea>
                                                </div>

                                                <button type="submit" class="btn btn-outline-primary w-100">
                                                {{ __('Save metadata') }}
                                                </button>
                                            </form>
                                        @endif



                                        {{-- Rename --}}
                                        <form action="{{ route('admin.media.rename', $item->id) }}" method="POST" class="mb-2 mt-2">
                                            @csrf
                                            @method('PATCH')

                                            <div class="input-group">
                                                <input type="text" name="file_name" class="form-control" value="{{ $item->file_name ?? '' }}">
                                                <button type="submit" class="btn btn-primary">{{ __('Rename') }}</button>
                                            </div>

                                            {{-- <div class="form-text text-muted">
                                                {{ __('Renaming updates the DB name field. It does not necessarily rename the physical file unless your controller does that too.') }}
                                            </div> --}}
                                        </form>

                                        {{-- Copy URL --}}
                                        <button type="button"
                                            class="btn btn-outline-success w-100 mb-2"
                                            onclick="copyToClipboard(@json($url))">
                                            {{ __('Copy URL') }}
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button"
                                                class="btn btn-outline-danger w-100"
                                                data-confirm
                                                data-action="{{ route('admin.media.delete', $item->id) }}"
                                                data-method="DELETE"
                                                data-variant="danger"
                                                data-title="{{ __('Delete file') }}"
                                                data-body="{{ __('Delete this file? This will also remove the physical file from disk.') }}"
                                                data-name="{{ $item->file_name ?? ('#'.$item->id) }}"
                                                data-confirm-text="{{ __('Yes, delete') }}">
                                            {{ __('Delete') }}
                                        </button>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

@push('scripts')
<script>window.disableFancyFileUpload = true;</script>
@endpush

@push('styles')
  <link rel="stylesheet" href="https://unpkg.com/filepond/dist/filepond.min.css">
  <style>
    /* Avoid theme hiding file inputs */
    #filepond-upload { display: block !important; }
  </style>
@endpush



    @push('scripts')
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>

<script>
(function () {
  const input = document.getElementById('filepond-upload');

  if (!input) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const pond = FilePond.create(input, {
    allowMultiple: true,
    instantUpload: true,
    credits: false,
    labelIdle: `{{ __('Drag & drop files or') }} <span class="filepond--label-action">{{ __('Browse') }}</span>`,
  });

  pond.setOptions({
    server: {
      process: {
        url: @json(route('admin.media.upload')),
        method: 'POST',

        // IMPORTANT: don't set headers / withCredentials -> avoids OPTIONS preflight 405
        withCredentials: false,

        ondata: (formData) => {
          // Add CSRF token as normal POST field
          if (csrf) formData.append('_token', csrf);

          // Your controller expects single "file"
          const file = formData.get('files[]') || formData.get('filepond');
          formData.delete('files[]');
          formData.delete('filepond');
          if (file) formData.append('file', file);

          return formData;
        }
      }
    }
  });

  pond.on('processfiles', () => window.location.reload());
})();
</script>
@endpush



    @push('scripts')
    <script>
        async function copyToClipboard(text) {
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                // optional toast/alert
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        }
    </script>
    @endpush
</x-app-layout>
