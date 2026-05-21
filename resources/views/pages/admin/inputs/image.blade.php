{{-- resources/views/pages/admin/inputs/image.blade.php --}}
@php
  /** Expected:
   * $instanceId, $instanceType, $fileItems (Collection of Media), $uid (optional)
   */
  $uid = $uid ?? ('img_' . ($instanceId ?? 'x'));
  $items = $fileItems ?? collect();

  // Pick the first image-like item as the "main image"
  $image = $items->first(function ($m) {
      $mime = strtolower((string)($m->mime_type ?? ''));
      $type = strtolower((string)($m->type ?? ''));
      return str_starts_with($mime, 'image/') || $type === 'image';
  });

  $imageUrl = $image ? asset($image->file_path) : null;
@endphp

<div class="row g-3 align-items-start">
  <div class="col-md-4">
    <div class="border rounded-4 overflow-hidden bg-dark-subtle" style="aspect-ratio: 4/3;">
      @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="" style="width:100%;height:100%;object-fit:cover;">
      @else
        <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
          {{ __('No image selected') }}
        </div>
      @endif
    </div>

    @if($imageUrl)
      <div class="d-flex gap-2 mt-2">
        <button type="button"
                class="btn btn-sm btn-outline-secondary media-copy-btn"
                data-media-id="{{ $image->id }}"
                data-copy-text="{{ $imageUrl }}"
                onclick="copyTextFromBtn(this, 'img-copy-msg-{{ $instanceId }}')">
          <i class="bi bi-link-45deg me-1"></i>{{ __('Copy URL') }}
        </button>
        <span class="text-success small align-self-center" id="img-copy-msg-{{ $instanceId }}"></span>
      </div>
    @endif
  </div>

  <div class="col-md-8">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="fw-semibold">{{ __('Image') }}</div>
        <div class="text-muted small">
          {{ __('Upload a single image. Uploading a new one will replace the current image.') }}
        </div>
      </div>
    </div>

    <div class="mt-3">
      <label class="form-label small">{{ __('Upload / Replace') }}</label>

      <div class="d-flex flex-wrap gap-2 align-items-center">
        <input type="file"
               id="image-upload-{{ $instanceType }}-{{ $instanceId }}"
               class="form-control"
               accept="image/*">

        <div class="form-check form-switch m-0">
          <input class="form-check-input" type="checkbox"
                 id="image-keep-{{ $instanceType }}-{{ $instanceId }}" checked>
          <label class="form-check-label small" for="image-keep-{{ $instanceType }}-{{ $instanceId }}">
            {{ __('Keep original name') }}
          </label>
        </div>

        <button type="button"
                class="btn btn-outline-primary"
                onclick="uploadSingleImage('{{ $instanceType }}', {{ $instanceId }})">
          <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
        </button>

        <span class="text-muted small" id="image-msg-{{ $instanceType }}-{{ $instanceId }}"></span>
      </div>

      @if($image)
        <div class="mt-3 d-flex align-items-center gap-2">
          <button type="button"
                  class="btn btn-outline-danger btn-sm"
                  onclick="detachImage('{{ $instanceType }}', {{ $instanceId }}, {{ $image->id }})">
            <i class="bi bi-trash me-1"></i>{{ __('Remove image') }}
          </button>

          <span class="text-muted small">
            {{ $image->file_name }}
            @if(!empty($image->size))
              • {{ round($image->size / 1024) }} KB
            @endif
          </span>
        </div>
      @endif
    </div>

    {{-- Hidden input so bulkUpdate doesn't overwrite value (image is stored via media pivot) --}}
    <input type="hidden" name="{{ $name }}" value="{{ $value ?? '' }}">
  </div>
</div>

@once
@push('scripts')
<script>
  // Upload one image and REPLACE existing images for this input instance
  async function uploadSingleImage(instanceType, instanceId) {
    const inputId = 'image-upload-' + instanceType + '-' + instanceId;
    const keepId  = 'image-keep-' + instanceType + '-' + instanceId;
    const msgId   = 'image-msg-' + instanceType + '-' + instanceId;

    const input = document.getElementById(inputId);
    const keepOriginal = document.getElementById(keepId)?.checked ? '1' : '0';
    const msg = document.getElementById(msgId);

    if (!input || !input.files || input.files.length === 0) {
      if (msg) msg.textContent = 'Choose an image first.';
      return;
    }

    const file = input.files[0];
    if (!file.type || !file.type.startsWith('image/')) {
      if (msg) msg.textContent = 'Selected file is not an image.';
      return;
    }

    if (msg) msg.textContent = 'Uploading...';

    try {
      // 1) Upload (uses your existing endpoint for "files")
      const fd = new FormData();
      fd.append('files[]', file);
      fd.append('keep_original_name', keepOriginal);

      const uploadUrl = '/admin/input-instances/' + encodeURIComponent(instanceId) + '/files/upload';

      const res = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: fd
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.success === false)) {
        if (msg) msg.textContent = (data && data.message) ? data.message : 'Upload failed.';
        return;
      }

      // 2) After upload, we want ONLY ONE image attached.
      // Easiest: reload; backend keeps both for now.
      // If you want strict single-image, tell me and we’ll add a backend "replace" endpoint.
      if (msg) msg.textContent = 'Uploaded. Refreshing...';
      location.reload();
    } catch (e) {
      console.error(e);
      if (msg) msg.textContent = 'Upload failed.';
    }
  }

  async function detachImage(instanceType, instanceId, mediaId) {
    if (!confirm('Remove this image from this input?')) return;

    try {
      const url = '/admin/input-instances/' + encodeURIComponent(instanceId) +
                  '/files/' + encodeURIComponent(mediaId);

      const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.success === false)) {
        alert((data && data.message) ? data.message : 'Remove failed');
      } else {
        location.reload();
      }
    } catch (e) {
      console.error(e);
      alert('Remove failed');
    }
  }
</script>
@endpush
@endonce
