@php
  /**
   * Required inputs passed from parent:
   * - $label (string|null)
   * - $ownerType (site|article)
   * - $ownerId (int)
   * - $instanceType (string) e.g. 'input_instance'
   * - $instanceId (int)   e.g. $input->id
   * - $galleryItems (Collection)  e.g. $input->galleryMedia
   * - $galleryId (int|string|null) e.g. $input->gallery_id
   */
  $galleryItems = $galleryItems ?? collect();

  $accId      = "gallery-acc-{$instanceType}-{$instanceId}";
  $headingId  = "gallery-heading-{$instanceType}-{$instanceId}";
  $collapseId = "gallery-collapse-{$instanceType}-{$instanceId}";

  $listId     = "gallery-items-{$instanceType}-{$instanceId}";
  $fileId     = "gallery-files-{$instanceType}-{$instanceId}";
  $keepId     = "keepOriginalName-{$instanceType}-{$instanceId}";
  $msgId      = "gallery-upload-msg-{$instanceType}-{$instanceId}";
@endphp

<div class="card border">
  <div class="card-body">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <div class="fw-semibold">{{ $label ?? 'Gallery' }}</div>
        <div class="text-muted small">Upload images, drag & drop to reorder, edit meta, remove.</div>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                onclick="ensureGallery('{{ $instanceType }}', {{ (int)$instanceId }})">
          <i class="bi bi-arrow-repeat me-1"></i> Refresh
        </button>
      </div>
    </div>

    {{-- Upload (AJAX, no form) --}}
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <input type="file"
             id="{{ $fileId }}"
             multiple
             accept="image/*"
             class="form-control"
             style="max-width: 460px;">

      <div class="form-check ms-1">
        <input class="form-check-input"
               type="checkbox"
               value="1"
               id="{{ $keepId }}">
        <label class="form-check-label" for="{{ $keepId }}">
          keep original name
        </label>
      </div>

      <button type="button"
              class="btn btn-sm btn-outline-primary"
              onclick="uploadGalleryFiles('{{ $instanceType }}', {{ (int)$instanceId }})">
        <i class="bi bi-upload me-1"></i> Upload
      </button>

      <div class="text-muted small" id="{{ $msgId }}"></div>
    </div>

    <hr class="my-3">

    {{-- Collapsible list --}}
    <div class="accordion" id="{{ $accId }}">
      <div class="accordion-item">
        <h2 class="accordion-header" id="{{ $headingId }}">
          <button class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#{{ $collapseId }}"
                  aria-expanded="false"
                  aria-controls="{{ $collapseId }}">
            <div class="d-flex justify-content-between align-items-center w-100 me-3">
              <span>Images ({{ $galleryItems->count() }})</span>
              <small class="text-muted">Click to expand</small>
            </div>
          </button>
        </h2>

        <div id="{{ $collapseId }}"
             class="accordion-collapse collapse"
             aria-labelledby="{{ $headingId }}"
             data-bs-parent="#{{ $accId }}">
          <div class="accordion-body">

            @if($galleryItems->count() === 0)
              <div class="text-muted">No images yet.</div>
            @else
              <div class="text-muted small mb-2">Drag & drop to reorder</div>

              <div id="{{ $listId }}"
                   class="list-group"
                   data-instance-type="{{ $instanceType }}"
                   data-instance-id="{{ (int)$instanceId }}"
                   data-gallery-id="{{ $galleryId ?? '' }}">
                @foreach($galleryItems as $media)
                  <div class="list-group-item draggable d-block mb-2"
                       draggable="true"
                       data-media-id="{{ $media->id }}">

                    <div class="row g-3 align-items-start">
                      <div class="col-md-4">
                        <div class="border rounded p-2 bg-light">
                          <img src="{{ asset($media->file_path ?? '') }}"
                               class="img-fluid rounded"
                               alt="{{ $media->alternative ?? '' }}">
                        </div>
                      </div>

                      <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <div class="fw-semibold">#{{ $media->id }}</div>
                          <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="removeFromGallery('{{ $instanceType }}', {{ (int)$instanceId }}, {{ (int)$media->id }})">
                              <i class="bi bi-x-circle me-1"></i> Remove
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-danger"
                                    onclick="deleteMediaPermanently({{ (int)$media->id }})">
                              <i class="bi bi-trash me-1"></i> Delete file
                            </button>
                          </div>
                        </div>

                        <div class="row g-2">
                          <div class="col-md-6">
                            <label class="form-label small mb-1">Name</label>
                            <input class="form-control form-control-sm"
                                   id="media-name-{{ $media->id }}"
                                   value="{{ $media->name ?? '' }}">
                          </div>

                          <div class="col-md-6">
                            <label class="form-label small mb-1">Alt</label>
                            <input class="form-control form-control-sm"
                                   id="media-alt-{{ $media->id }}"
                                   value="{{ $media->alternative ?? '' }}">
                          </div>

                          <div class="col-12">
                            <label class="form-label small mb-1">Description</label>
                            <textarea class="form-control form-control-sm"
                                      id="media-desc-{{ $media->id }}"
                                      rows="2">{{ $media->description ?? '' }}</textarea>
                          </div>

                          <div class="col-md-8">
                            <label class="form-label small mb-1">Filename</label>
                            <input class="form-control form-control-sm"
                                   id="media-file-{{ $media->id }}"
                                   value="{{ $media->file_name ?? '' }}">
                          </div>

                          <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary w-100 media-copy-btn"
                                    data-media-id="{{ $media->id }}"
                                    data-copy-text="{{ asset($media->file_path ?? '') }}"
                                    onclick="copyTextFromBtn(this, 'media-msg-{{ $media->id }}')">
                              <i class="bi bi-clipboard me-1"></i> Copy path
                            </button>
                          </div>

                          <div class="col-12 d-flex gap-2 mt-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    onclick="saveMediaMeta({{ (int)$media->id }})">
                              <i class="bi bi-save me-1"></i> Save meta
                            </button>
                            <div class="text-muted small align-self-center" id="media-msg-{{ $media->id }}"></div>
                          </div>
                        </div>

                      </div>
                    </div>

                  </div>
                @endforeach
              </div>
            @endif

          </div>
        </div>
      </div>
    </div>

  </div>
</div>
