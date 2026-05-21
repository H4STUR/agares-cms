@php
  /**
   * Required from parent include:
   * - $label
   * - $instanceType  (e.g. 'input_instance')
   * - $instanceId
   * - $fileItems (Collection of Media) -> via $input->files
   *
   * Also receives:
   * - $name (inputs[{id}][value]) for bulkUpdate, but we won't use it except hidden.
   * - $value (string) old single-file value (legacy) - we keep hidden so bulkUpdate won't wipe it.
   */
  $fileItems = $fileItems ?? collect();

  $accId      = "files-acc-{$instanceType}-{$instanceId}";
  $headingId  = "files-heading-{$instanceType}-{$instanceId}";
  $collapseId = "files-collapse-{$instanceType}-{$instanceId}";

  $listId     = "files-items-{$instanceType}-{$instanceId}";
  $fileId     = "files-upload-{$instanceType}-{$instanceId}";
  $keepId     = "files-keep-{$instanceType}-{$instanceId}";
  $msgId      = "files-msg-{$instanceType}-{$instanceId}";
@endphp

<div class="card border">
  <div class="card-body">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <div class="fw-semibold">{{ $label ?? 'Files' }}</div>
        <div class="text-muted small">Upload files, drag & drop to reorder, rename filename via Media meta.</div>
      </div>
    </div>

    {{-- keep bulkUpdate value safe (legacy single-file value) --}}
    <input type="hidden" name="{{ $name }}" value="{{ $value ?? '' }}">

    {{-- Upload --}}
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <input type="file"
        id="{{ $fileId }}"
        multiple
        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.7z,.txt,.csv"
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
              onclick="uploadInstanceFiles('{{ $instanceType }}', {{ (int)$instanceId }})">
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
              <span>Files ({{ $fileItems->count() }})</span>
              <small class="text-muted">Click to expand</small>
            </div>
          </button>
        </h2>

        <div id="{{ $collapseId }}"
             class="accordion-collapse collapse"
             aria-labelledby="{{ $headingId }}"
             data-bs-parent="#{{ $accId }}">
          <div class="accordion-body">

            @if($fileItems->count() === 0)
              <div class="text-muted">No files yet.</div>
            @else
              <div class="text-muted small mb-2">Drag & drop to reorder</div>

              <div id="{{ $listId }}"
                   class="list-group"
                   data-files-list="1"
                   data-instance-id="{{ (int)$instanceId }}">
                @foreach($fileItems as $media)
                  <div class="list-group-item draggable d-block mb-2"
                       draggable="true"
                       data-media-id="{{ $media->id }}">

                    <div class="d-flex justify-content-between align-items-start gap-2">
                      <div class="d-flex flex-column">
                        <div class="fw-semibold">#{{ $media->id }}</div>
                        <a href="{{ asset($media->file_path ?? '') }}" target="_blank" rel="noopener">
                          {{ $media->file_name ?? 'file' }}
                        </a>
                        <div class="small text-muted">{{ $media->file_path ?? '' }}</div>
                      </div>

                      <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="detachInstanceFile('{{ $instanceType }}', {{ (int)$instanceId }}, {{ (int)$media->id }})">
                          <i class="bi bi-x-circle me-1"></i> Remove
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-danger"
                                onclick="deleteMediaPermanently({{ (int)$media->id }})">
                          <i class="bi bi-trash me-1"></i> Delete file
                        </button>
                      </div>
                    </div>

                    <div class="row g-2 mt-2">
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
                          <i class="bi bi-save me-1"></i> Save filename
                        </button>
                        <div class="text-muted small align-self-center" id="media-msg-{{ $media->id }}"></div>
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
