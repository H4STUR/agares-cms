<x-app-layout>

@php
  $activeTab = request('tab', 'content'); // content|settings|seo
@endphp


@php
    // We are editing inputs for a Site
    $ownerType = 'site';
    $ownerId   = $data['site']->id;
@endphp

<div class="card mb-4">
    <div class="card-body">


      @php
        $site = $data['site'];
        $isPublished = ($site->status === \App\Models\Site::STATUS_PUBLISHED);
      @endphp

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">

        <div class="d-flex align-items-center gap-2">
          <div>
            <h4 class="mb-2">{{ __('Edit site') }}: {{ $site->name }}</h4>
          </div>
          <span class="badge
            @if($site->deleted_at) text-bg-danger
            @elseif($site->status === 'published') text-bg-success
            @elseif($site->status === 'scheduled') text-bg-warning
            @else text-bg-secondary
            @endif
          ">
            {{ $site->deleted_at ? __('Trashed') : ucfirst($site->status ?? 'draft') }}
          </span>

          <small class="text-muted">
            /{{ $site->slug }}
          </small>
        </div>

        @can('manage sites')
          <div class="d-flex align-items-center gap-2">

            {{-- Quick publish/draft toggle --}}
            @if(!$site->deleted_at)
              <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="d-flex align-items-center gap-2">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="{{ $isPublished ? 'draft' : 'published' }}">

                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" id="publishSwitch"
                        {{ $isPublished ? 'checked' : '' }}
                        onchange="this.form.submit()">
                  <label class="form-check-label small" for="publishSwitch">
                    {{ $isPublished ? __('Published') : __('Draft') }}
                  </label>
                </div>
              </form>
            @endif

            {{-- Preview --}}
            <a href="{{ url('/'.$site->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm ml-2">
              <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
            </a>

          </div>
        @endcan
      </div>


        <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
            <li class="nav-item" role="presentation">
              <x-nav-link href="#tab-content" :active="$activeTab === 'content'" id="tab-button-content" icon="bi-layers"
                          data-tab="content"
                          data-url="{{ route('admin.sites.edit', $data['site']->id) }}?tab=content">
                {{ __('Content') }}
              </x-nav-link>

            </li>
            <li class="nav-item" role="presentation">
              <x-nav-link href="#tab-settings" :active="$activeTab === 'settings'" id="tab-button-settings" icon="bi-gear"
                          data-tab="settings"
                          data-url="{{ route('admin.sites.edit', $data['site']->id) }}?tab=settings">
                {{ __('Settings') }}
              </x-nav-link>
            </li>
            <li class="nav-item" role="presentation">
              <x-nav-link href="#tab-seo" :active="$activeTab === 'seo'" id="tab-button-seo" icon="bi-graph-up"
                          data-tab="seo"
                          data-url="{{ route('admin.sites.edit', $data['site']->id) }}?tab=seo">
                {{ __('SEO') }}
              </x-nav-link>
            </li>
        </ul>

        {{-- =========================================================
            UNIFIED FORM - saves all tabs at once
        ========================================================== --}}
        <form action="{{ route('admin.sites.update', $data['site']->id) }}" method="POST" id="site-edit-form">
            @csrf
            @method('PATCH')

            <div class="tab-content">

                {{-- =========================================================
                    CONTENT TAB (Inputs)
                ========================================================== --}}
                <div id="tab-content" class="tab-pane fade {{ $activeTab === 'content' ? 'show active' : '' }}">

                    <div id="input-container">
                        @foreach($data['inputs'] as $index => $input)
                            @php
                                $fieldType = $input->field?->field_type;

                                $viewMap = [
                                    'short_text'  => 'short_text',
                                    'number'      => 'number',

                                    // WYSIWYG
                                    'text'        => 'text_editor',
                                    'text_editor' => 'text_editor',
                                    'wysiwyg'     => 'text_editor',

                                    'textarea'    => 'textarea',
                                    'code'        => 'code',

                                    'file'        => 'file',
                                    'image'       => 'image',

                                    'gallery'     => 'gallery',

                                    'contact_form'=> 'contact_form',
                                    'faq'         => 'faq',
                                ];

                                $partial = $viewMap[$fieldType] ?? 'textarea';
                            @endphp

                            @if($fieldType === 'contact_form')
                              @continue
                            @endif

                            <div class="input-item mb-4 p-3 border rounded" data-instance-id="{{ $input->id }}">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">
                                        {{ $input->label ?? ($input->description ?? 'No label') }}

                                        @if($input->variable)

                                            @php
                                                // Choose the snippet you want to paste later in frontend blade:
                                                // Option A: show the value only
                                                $bladeSnippet = "{{ \$data['{$input->variable}']->value ?? '' }}";

                                                // Option B: if you want raw HTML output (e.g. WYSIWYG)
                                                // $bladeSnippet = "{!! input_value('{$input->variable}', \$site ?? null, \$category ?? null, \$article ?? null) !!}";
                                            @endphp

                                            <span class="text-muted small ms-2 copy-variable"
                                                  data-copy-text='@json($bladeSnippet)'
                                                  data-message-id="copy-message-{{ $input->id }}"
                                                  style="cursor:pointer;"
                                                  title="Click to copy">
                                              ({{ $input->variable }})
                                            </span>
                                            <span class="text-success small ms-2 d-none" id="copy-message-{{ $input->id }}">Copied!</span>

                                        @endif

                                        @if($input->is_default)
                                            <span class="badge bg-light text-muted ms-2">default</span>
                                        @else
                                            <span class="badge bg-light text-muted ms-2">custom</span>
                                        @endif
                                        @if($input->is_locked)
                                            <span class="badge bg-warning text-muted ms-1">locked</span>
                                        @endif
                                    </div>

                                    <div class="btn-group" role="group">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary {{ $index === 0 ? 'disabled' : '' }}"
                                                onclick="moveInstanceInstant({{ $input->id }}, 'up')">
                                            <i class="bi bi-arrow-up"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary {{ $index === (count($data['inputs']) - 1) ? 'disabled' : '' }}"
                                                onclick="moveInstanceInstant({{ $input->id }}, 'down')">
                                            <i class="bi bi-arrow-down"></i>
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger {{ ($input->is_default) ? 'disabled' : '' }}"
                                                onclick="deleteInstance({{ $input->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Normal inputs --}}
                                @include("pages.admin.inputs.$partial", [
                                    'name'  => "inputs[{$input->id}][value]",
                                    'value' => $input->value ?? '',
                                    'label' => $input->label ?? $input->description ?? null,
                                    'uid'   => "input_{$input->id}",

                                    'ownerType'    => $ownerType,
                                    'ownerId'      => $ownerId,
                                    'instanceType' => 'input_instance',
                                    'instanceId'   => $input->id,

                                    'galleryId'    => $input->gallery_id ?? null,
                                    'galleryItems' => $input->galleryMedia ?? collect(),

                                    'fileItems'    => $input->files ?? collect(),
                                ])



                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- =========================================================
                    SETTINGS TAB
                ========================================================== --}}
                <div id="tab-settings" class="tab-pane fade {{ $activeTab === 'settings' ? 'show active' : '' }}">

                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $data['site']->name) }}" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="slug" class="form-label">{{ __('Slug') }}</label>
                        <div class="input-group">
                            <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $data['site']->slug) }}" required>
                            <button type="button" class="btn btn-outline-secondary" id="slugifyButton" title="{{ __('Generate from name') }}">
                                <i class="bi bi-magic"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label" for="template">{{ __('Page template') }}</label>
                      <select class="form-select" name="template" id="template">
                        @foreach(($data['frontendTemplates'] ?? collect()) as $tpl)
                          <option value="{{ $tpl }}" {{ old('template', $data['site']->template) === $tpl ? 'selected' : '' }}>
                            {{ $tpl }}
                          </option>
                        @endforeach
                      </select>

                      <div class="form-text text-muted">
                        {{ __('Template file from resources/views/pages/frontend/sites/') }}
                      </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="menu_id">{{ __('Menu') }}</label>
                        <select class="form-select" name="menu_id" id="menu_id">
                            @foreach($menus as $menu)
                                <option value="{{ $menu->id }}" {{ $data['site']->menus->contains($menu->id) ? 'selected' : '' }}>
                                    {{ $menu->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="parent_id">{{ __('Parent Site') }}</label>
                        <select class="form-select" name="parent_id" id="parent_id">
                            <option value="">{{ __('No Parent') }}</option>

                            @foreach(($data['site']->menus->first()?->sites ?? []) as $parentSite)
                                @continue($parentSite->id === $data['site']->id)

                                <option value="{{ $parentSite->id }}"
                                    {{ $parentSite->id == $data['site']->parent_id ? 'selected' : '' }}>
                                    {{ $parentSite->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                      <label class="form-label">{{ __('Publish status') }}</label>

                      <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $data['site']->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="published" {{ old('status', $data['site']->status) === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                        <option value="scheduled" {{ old('status', $data['site']->status) === 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                        <option value="private" {{ old('status', $data['site']->status) === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                      </select>

                      <div class="form-text text-muted">
                        {{ __('Scheduled requires a publish date. Published sets publish date to now.') }}
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label" for="published_at">{{ __('Publish date (for scheduled)') }}</label>
                      <input
                        type="datetime-local"
                        id="published_at"
                        name="published_at"
                        class="form-control"
                        value="{{ old('published_at', $data['site']->published_at ? $data['site']->published_at->format('Y-m-d\TH:i') : '') }}"
                      >
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-2">{{ __('Forward / Redirect') }}</h6>
                    <p class="text-muted mb-3">
                      {{ __('If enabled, this page will redirect visitors to another URL instead of rendering content.') }}
                    </p>

                    <div class="form-check form-switch mb-3">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="is_redirect"
                        name="is_redirect"
                        value="1"
                        {{ old('is_redirect', $data['site']->is_redirect) ? 'checked' : '' }}
                      >
                      <label class="form-check-label" for="is_redirect">
                        {{ __('This page is a forward') }}
                      </label>
                    </div>

                    <div class="row g-3 align-items-end">
                      <div class="col-12 col-lg-7">
                        <label class="form-label" for="redirect_url">{{ __('Target URL') }}</label>
                        <input
                          type="text"
                          class="form-control"
                          id="redirect_url"
                          name="redirect_url"
                          placeholder="https://example.com or /kontakt"
                          value="{{ old('redirect_url', $data['site']->redirect_url) }}"
                        >
                        {{-- <div class="form-text text-muted">
                          {{ __('Use full https:// URL for external pages or /path for internal.') }}
                        </div> --}}
                      </div>

                      <div class="col-6 col-lg-3">
                        <label class="form-label" for="redirect_type">{{ __('Redirect type') }}</label>
                        <select class="form-select" id="redirect_type" name="redirect_type">
                          @php $rt = (int) old('redirect_type', $data['site']->redirect_type ?? 302); @endphp
                          <option value="302" {{ $rt === 302 ? 'selected' : '' }}>302 ({{ __('Temporary') }})</option>
                          <option value="301" {{ $rt === 301 ? 'selected' : '' }}>301 ({{ __('Permanent') }})</option>
                        </select>
                      </div>

                      <div class="col-6 col-lg-2">
                        <div class="form-check mt-4">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            id="redirect_new_tab"
                            name="redirect_new_tab"
                            value="1"
                            {{ old('redirect_new_tab', $data['site']->redirect_new_tab) ? 'checked' : '' }}
                          >
                          <label class="form-check-label" for="redirect_new_tab">
                            {{ __('New tab') }}
                          </label>
                        </div>
                      </div>
                    </div>

                    <div class="mt-3">
                      <a
                        href="{{ $data['site']->redirect_url ?: 'javascript:void(0);' }}"
                        class="btn btn-outline-secondary btn-sm {{ $data['site']->redirect_url ? '' : 'disabled' }}"
                        target="_blank"
                        rel="noopener"
                        id="redirect_test_btn"
                      >
                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Test target') }}
                      </a>
                    </div>


                    <hr class="my-4">

                    {{-- Inputs template (displayed in Settings tab, but form is outside main form via JS) --}}
                    <div class="mb-4">
                        <label class="form-label" for="template_id">{{ __('Inputs template') }}</label>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="input_template_select" class="form-select">
                                @foreach(($data['inputTemplates'] ?? []) as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" onclick="applyInputTemplate()">
                                {{ __('Apply') }}
                            </button>
                        </div>
                        <div class="form-text text-muted">
                            {{ __('Apply a predefined set of input fields to this site.') }}
                        </div>
                    </div>

                </div>

                {{-- =========================================================
                    SEO TAB
                ========================================================== --}}
                <div id="tab-seo" class="tab-pane fade {{ $activeTab === 'seo' ? 'show active' : '' }}">

                    @if(\App\Models\Setting::bool('ai_seo_enabled'))
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#aiSeoModal-site-{{ $data['site']->id }}">
                                <i class="bi bi-stars me-1"></i> {{ __('Generate SEO') }}
                            </button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="title">{{ __('Title') }}</label>
                        <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $data['site']->title) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" id="description" rows="5">{{ old('description', $data['site']->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="keywords">{{ __('Keywords') }}</label>
                        <input class="form-control" type="text" name="keywords" id="keywords" value="{{ old('keywords', $data['site']->keywords) }}">
                    </div>

                </div>
            </div>

        </form>

        @include('pages.admin.partials.ai-seo-modal', [
            'contentType' => 'site',
            'contentId'   => $data['site']->id,
            'fieldMap'    => [
                'meta_title'       => 'title',
                'meta_description' => 'description',
                'slug'             => 'slug',
            ],
        ])

        {{-- =========================================================
            CONTENT TAB EXTRAS (synced with tab-content visibility via JS)
        ========================================================= --}}
        <div id="content-tab-extras" class="{{ $activeTab !== 'content' ? 'd-none' : '' }}">
            {{-- CONTACT FORMS --}}
            @php
              $contactForms = collect($data['inputs'] ?? [])->filter(fn($i) => $i->field?->field_type === 'contact_form');
            @endphp

            @if($contactForms->count())
              <div class="card mt-4">
                <div class="card-body">

                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                      <h5 class="mb-0"><i class="bi bi-envelope me-1"></i> {{ __('Contact forms') }}</h5>
                      <small class="text-muted">{{ __('Manage recipients, email subject, success message and form fields.') }}</small>
                    </div>
                  </div>

                  @foreach($contactForms as $cfIndex => $input)
                    <div class="border rounded-4 p-3 mb-3">

                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">
                          {{ $input->label ?? __('Contact form') }}

                          @if($input->variable)
                            <span class="text-muted small ms-2 copy-variable"
                                  data-copy-text='@json("{{ \$data['{$input->variable}']->value ?? '' }}")'
                                  data-message-id="copy-message-{{ $input->id }}"
                                  style="cursor:pointer;"
                                  title="Click to copy">
                              ({{ $input->variable }})
                            </span>
                            <span class="text-success small ms-2 d-none" id="copy-message-{{ $input->id }}">Copied!</span>
                          @endif

                          @if($input->is_default)
                            <span class="badge bg-light text-muted ms-2">default</span>
                          @else
                            <span class="badge bg-light text-muted ms-2">custom</span>
                          @endif

                          @if($input->is_locked)
                            <span class="badge bg-warning text-muted ms-1">locked</span>
                          @endif
                        </div>

                        <div class="btn-group" role="group">
                          <button type="button"
                                  class="btn btn-sm btn-outline-danger {{ ($input->is_default) ? 'disabled' : '' }}"
                                  onclick="deleteInstance({{ $input->id }})">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>

                      @include('pages.admin.inputs.contact_form', [
                        'value' => $input->value ?? '',
                        'instanceId' => $input->id,
                        'ownerType' => $ownerType,
                        'ownerId' => $ownerId,
                      ])

                    </div>
                  @endforeach

                </div>
              </div>
            @endif

            {{-- ADD INPUT --}}
            <div class="card mt-4">
                <div class="card-body">
                    <form action="{{ route('admin.inputs.store', ['type' => 'site', 'id' => $data['site']->id]) }}" method="POST">
                        @csrf

                        <div class="row g-3 align-items-center">
                            <label class="form-label">{{ __('Add Input Field') }}</label>

                            <div class="col-auto">
                                <select name="input_field_id" class="form-select" required>
                                    @foreach($data['inputTypes'] as $field)
                                        <option value="{{ $field->id }}">{{ ucfirst($field->name) }} ({{ $field->field_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <input type="text" name="variable" placeholder="Variable" class="form-control" />
                            </div>

                            <div class="col">
                                <input type="text" name="label" placeholder="Label / Title" class="form-control" />
                            </div>

                            <div class="col-auto">
                                <x-primary-button type="submit">{{ __('Add') }}</x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Hidden form for applying input template (submitted via JS) --}}
<form id="apply-input-template-form" action="{{ route('admin.inputTemplates.applyToOwner') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="owner_type" value="site">
    <input type="hidden" name="owner_id" value="{{ $data['site']->id }}">
    <input type="hidden" name="template_id" id="hidden_template_id" value="">
</form>

{{-- =========================================================
    STICKY BOTTOM SAVE BAR
========================================================== --}}
<div class="sticky-bottom-bar bg-body border-top shadow-sm p-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-end align-items-center gap-3">
            <button type="submit" form="site-edit-form" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> {{ __('Save All Changes') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
  const OWNER_TYPE = 'site';

  function applyInputTemplate() {
    const select = document.getElementById('input_template_select');
    const hiddenInput = document.getElementById('hidden_template_id');
    const form = document.getElementById('apply-input-template-form');

    if (select && hiddenInput && form) {
      hiddenInput.value = select.value;
      form.submit();
    }
  }

    document.addEventListener('DOMContentLoaded', () => {
    // Only gallery lists have data-gallery-id
    document.querySelectorAll('.list-group[data-gallery-id]').forEach(container => {
        setupGalleryDnD(container);
    });

    // Only file lists have data-files-list="1"
    document.querySelectorAll('.list-group[data-files-list="1"]').forEach(container => {
        setupFilesDnD(container);
    });
    });


  function setupGalleryDnD(container) {
    if (container.dataset.dndBound === '1') return;
    container.dataset.dndBound = '1';

    let dragged = null;

    container.addEventListener('dragstart', (e) => {
      dragged = e.target.closest('.draggable');
      if (!dragged) return;
      dragged.classList.add('opacity-50');
    });

    container.addEventListener('dragover', (e) => {
      e.preventDefault();
      const target = e.target.closest('.draggable');
      if (!dragged || !target || target === dragged) return;

      const rect = target.getBoundingClientRect();
      const next = (e.clientY - rect.top) > rect.height / 2;
      container.insertBefore(dragged, next ? target.nextSibling : target);
    });

    container.addEventListener('dragend', async () => {
      if (!dragged) return;
      dragged.classList.remove('opacity-50');

      const galleryId = container.getAttribute('data-gallery-id');
      if (!galleryId) {
        alert('Gallery not initialized yet. Click Refresh.');
        dragged = null;
        return;
      }

      const ids = Array.from(container.querySelectorAll('.draggable'))
        .map(el => parseInt(el.getAttribute('data-media-id'), 10));

      const url = '/admin/galleries/' + encodeURIComponent(galleryId) + '/reorder';

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ ids: ids })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || (data && data.success === false)) {
          alert((data && data.message) ? data.message : 'Reorder failed');
        }
      } catch (e) {
        console.error(e);
        alert('Reorder failed');
      }

      dragged = null;
    });
  }

  async function ensureGallery(instanceType, instanceId) {
    try {
      const url = '/admin/input-instances/' + encodeURIComponent(instanceId) + '/gallery/ensure';

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.success === false)) {
        alert((data && data.message) ? data.message : 'Failed to ensure gallery');
      } else {
        location.reload();
      }
    } catch (e) {
      console.error(e);
      alert('Failed to ensure gallery');
    }
  }

  async function uploadGalleryFiles(instanceType, instanceId) {
    const fileId = 'gallery-files-' + instanceType + '-' + instanceId;
    const keepId = 'keepOriginalName-' + instanceType + '-' + instanceId;
    const msgId  = 'gallery-upload-msg-' + instanceType + '-' + instanceId;

    const input = document.getElementById(fileId);
    const keepOriginal = document.getElementById(keepId)?.checked ? '1' : '0';
    const msg = document.getElementById(msgId);

    if (!input || !input.files || input.files.length === 0) {
      if (msg) msg.textContent = 'Choose images first.';
      return;
    }

    if (msg) msg.textContent = 'Uploading...';

    const fd = new FormData();
    for (const f of input.files) fd.append('images[]', f);
    fd.append('keep_original_name', keepOriginal);

    try {
      const url = '/admin/input-instances/' + encodeURIComponent(instanceId) + '/gallery/upload';

      const res = await fetch(url, {
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

      if (msg) msg.textContent = 'Uploaded.';
      location.reload();
    } catch (e) {
      console.error(e);
      if (msg) msg.textContent = 'Upload failed.';
    }
  }

  async function removeFromGallery(instanceType, instanceId, mediaId) {
    const listId = 'gallery-items-' + instanceType + '-' + instanceId;
    const container = document.getElementById(listId);
    const galleryId = container ? container.getAttribute('data-gallery-id') : null;

    if (!galleryId) return alert('Gallery not initialized yet. Click Refresh.');
    if (!confirm('Remove this image from the gallery?')) return;

    try {
      const url = '/admin/galleries/' + encodeURIComponent(galleryId) +
                  '/media/' + encodeURIComponent(mediaId);

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

  async function deleteMediaPermanently(mediaId) {
    if (!confirm('Delete this file permanently?')) return;

    try {
      const url = '/admin/media/' + encodeURIComponent(mediaId);

      const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.success === false)) {
        alert((data && data.message) ? data.message : 'Delete failed');
      } else {
        location.reload();
      }
    } catch (e) {
      console.error(e);
      alert('Delete failed');
    }
  }

  async function saveMediaMeta(mediaId) {
    const msg = document.getElementById('media-msg-' + mediaId);
    if (msg) msg.textContent = 'Saving...';

    const fileName    = document.getElementById('media-file-' + mediaId)?.value?.trim() ?? '';
    const alternative = document.getElementById('media-alt-' + mediaId)?.value?.trim() ?? '';
    const description = document.getElementById('media-desc-' + mediaId)?.value?.trim() ?? '';
    const nameVal     = document.getElementById('media-name-' + mediaId)?.value?.trim() ?? '';

    try {
      const url = '/admin/media/' + encodeURIComponent(mediaId);

      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          _method: 'PATCH',
          file_name: fileName,
          alternative: alternative,
          description: description,
          name: nameVal
        })
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.success === false)) {
        if (msg) msg.textContent = (data && data.message) ? data.message : 'Failed.';
        return;
      }

      if (data && (data.url || data.file_path)) {
        const copyBtn = document.querySelector('button.media-copy-btn[data-media-id="' + mediaId + '"]');
        if (copyBtn) copyBtn.setAttribute('data-copy-text', data.url ? data.url : data.file_path);
      }

      if (msg) msg.textContent = 'Saved.';
      setTimeout(() => { if (msg) msg.textContent = ''; }, 1200);
    } catch (e) {
      console.error(e);
      if (msg) msg.textContent = 'Failed.';
    }
  }

  function setupFilesDnD(container) {
    if (container.dataset.dndBound === '1') return;
    container.dataset.dndBound = '1';

    let dragged = null;

    container.addEventListener('dragstart', (e) => {
        dragged = e.target.closest('.draggable');
        if (!dragged) return;
        dragged.classList.add('opacity-50');
    });

    container.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.target.closest('.draggable');
        if (!dragged || !target || target === dragged) return;

        const rect = target.getBoundingClientRect();
        const next = (e.clientY - rect.top) > rect.height / 2;
        container.insertBefore(dragged, next ? target.nextSibling : target);
    });

    container.addEventListener('dragend', async () => {
        if (!dragged) return;
        dragged.classList.remove('opacity-50');

        const instanceId = container.getAttribute('data-instance-id');
        const ids = Array.from(container.querySelectorAll('.draggable'))
        .map(el => parseInt(el.getAttribute('data-media-id'), 10));

        const url = '/admin/input-instances/' + encodeURIComponent(instanceId) + '/files/reorder';

        try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || (data && data.success === false)) {
            alert((data && data.message) ? data.message : 'Reorder files failed');
        }
        } catch (e) {
        console.error(e);
        alert('Reorder files failed');
        }

        dragged = null;
    });
    }

    async function uploadInstanceFiles(instanceType, instanceId) {
    const fileId = 'files-upload-' + instanceType + '-' + instanceId;
    const keepId = 'files-keep-' + instanceType + '-' + instanceId;
    const msgId  = 'files-msg-' + instanceType + '-' + instanceId;

    const input = document.getElementById(fileId);
    const keepOriginal = document.getElementById(keepId)?.checked ? '1' : '0';
    const msg = document.getElementById(msgId);

    if (!input || !input.files || input.files.length === 0) {
        if (msg) msg.textContent = 'Choose files first.';
        return;
    }

    if (msg) msg.textContent = 'Uploading...';

    const fd = new FormData();
    for (const f of input.files) fd.append('files[]', f);
    fd.append('keep_original_name', keepOriginal);

    try {
        const url = '/admin/input-instances/' + encodeURIComponent(instanceId) + '/files/upload';

        const res = await fetch(url, {
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

        if (msg) msg.textContent = 'Uploaded.';
        location.reload();
    } catch (e) {
        console.error(e);
        if (msg) msg.textContent = 'Upload failed.';
    }
    }

    async function detachInstanceFile(instanceType, instanceId, mediaId) {
    if (!confirm('Remove this file from this input?')) return;

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


  async function copyTextFromBtn(btn, msgId) {
    const text = btn.getAttribute('data-copy-text') || '';
    const msg = document.getElementById(msgId);

    if (!text) {
      if (msg) msg.textContent = 'Nothing to copy.';
      return;
    }

    try {
      await navigator.clipboard.writeText(text);
      if (msg) msg.textContent = 'Copied.';
      setTimeout(() => { if (msg) msg.textContent = ''; }, 1200);
    } catch (e) {
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);

      if (msg) msg.textContent = 'Copied.';
      setTimeout(() => { if (msg) msg.textContent = ''; }, 1200);
    }
  }

    async function deleteInstance(instanceId) {
        window.confirmAction({
          action: `/admin/${OWNER_TYPE}/inputs/${encodeURIComponent(instanceId)}`,
          method: 'DELETE',
          title: 'Delete input',
          body: 'Are you sure you want to delete',
          name: `#${instanceId}`,
          danger: true,
          submitText: 'Yes, delete'
        });

        const url = `/admin/${OWNER_TYPE}/inputs/${encodeURIComponent(instanceId)}`;

        const res = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        // your controller returns redirect/back, not JSON, so:
        location.reload();
    }
</script>


<script>
  document.addEventListener('DOMContentLoaded', () => {
    const contentExtras = document.getElementById('content-tab-extras');

    // Your x-nav-link renders <a ...>, so listen on clicks that have data-url
    document.querySelectorAll('[data-url]').forEach(el => {
      el.addEventListener('click', () => {
        const url = el.getAttribute('data-url');
        if (!url) return;
        history.replaceState(null, '', url);

        // Show/hide content-tab-extras based on which tab is clicked
        const tab = el.getAttribute('data-tab');
        if (contentExtras) {
          if (tab === 'content') {
            contentExtras.classList.remove('d-none');
          } else {
            contentExtras.classList.add('d-none');
          }
        }
      });
    });
  });
</script>

<script>
  document.addEventListener('click', async (e) => {
    const el = e.target.closest('.copy-variable');
    if (!el) return;

    e.preventDefault();

    const raw = el.getAttribute('data-copy-text') || '""';
    let text = '';

    try {
      text = JSON.parse(raw);
    } catch (e) {
      text = raw;
    }

    const msgId = el.getAttribute('data-message-id');
    const msgEl = msgId ? document.getElementById(msgId) : null;

    if (!text) return;

    const showCopied = () => {
      if (!msgEl) return;
      msgEl.classList.remove('d-none');
      setTimeout(() => msgEl.classList.add('d-none'), 1200);
    };

    try {
      await navigator.clipboard.writeText(text);
      showCopied();
    } catch (err) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      showCopied();
    }
  });
</script>


<script>
  async function moveInstanceInstant(instanceId, direction) {
    const url = `/admin/${OWNER_TYPE}/inputs/${encodeURIComponent(instanceId)}/move`;

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ direction })
      });

      // Try JSON, but don't hard-fail if controller returned HTML
      const data = await res.json().catch(() => null);

      if (!res.ok || (data && data.success === false)) {
        alert((data && data.message) ? data.message : 'Move failed');
        return;
      }

      location.reload();
    } catch (e) {
      console.error(e);
      alert('Move failed');
    }
  }
</script>

<script>
    function slugify(text) {
        return text
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-');
    }

    document.getElementById('slugifyButton').addEventListener('click', function () {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (slugInput.value.trim() !== '') {
            slugInput.value = slugify(slugInput.value);
        } else if (nameInput.value.trim() !== '') {
            slugInput.value = slugify(nameInput.value);
        }
    });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const chk = document.getElementById('is_redirect');
    const url = document.getElementById('redirect_url');
    const type = document.getElementById('redirect_type');
    const ntab = document.getElementById('redirect_new_tab');
    const test = document.getElementById('redirect_test_btn');

    const sync = () => {
      const on = !!chk?.checked;

      [url, type, ntab].forEach(el => {
        if (!el) return;
        el.disabled = !on;
      });

      if (test) {
        const v = (url?.value || '').trim();
        test.classList.toggle('disabled', !on || !v);
        test.setAttribute('href', (on && v) ? v : 'javascript:void(0);');
      }
    };

    chk?.addEventListener('change', sync);
    url?.addEventListener('input', sync);

    sync();
  });
</script>


@endpush



</x-app-layout>
