<x-app-layout>

@php
  $activeTab = request('tab', 'content'); // content|settings|seo
@endphp

@php
  $ownerType = 'article';
  $ownerId   = $article->id;

  $selectedIds = $article->categories->pluck('id')->map(fn($v)=>(string)$v)->toArray();
@endphp

<div class="card mb-4">
  <div class="card-body">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-2">{{ __('Edit Article') }}: {{ $article->title }}</h4>
        <div class="text-muted small">{{ __('Site') }}: {{ $site->name }}</div>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- Status badge --}}
        <span class="badge
          @if($article->deleted_at) text-bg-danger
          @elseif(($article->status ?? 'draft') === 'published') text-bg-success
          @elseif(($article->status ?? 'draft') === 'scheduled') text-bg-warning
          @else text-bg-secondary
          @endif
        ">
          {{ $article->deleted_at ? __('Trashed') : ucfirst($article->status ?? 'draft') }}
        </span>

        {{-- Quick publish/draft --}}
        @can('manage articles')
          @if(!$article->deleted_at)
            <form action="{{ route('admin.articles.update', [$site->id, $article->id]) }}" method="POST" class="m-0">
              @csrf
              @method('PATCH')

              <input type="hidden" name="status"
                    value="{{ ($article->status ?? 'draft') === 'published' ? 'draft' : 'published' }}">

              <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" role="switch" id="publishSwitch"
                      {{ ($article->status ?? 'draft') === 'published' ? 'checked' : '' }}
                      onchange="this.form.submit()">
                <label class="form-check-label small" for="publishSwitch">
                  {{ ($article->status ?? 'draft') === 'published' ? __('Published') : __('Draft') }}
                </label>
              </div>
            </form>
          @endif
        @endcan

        {{-- Preview --}}
        <a href="{{ url('/'.$site->slug.'/'.$article->categories->first()?->name.'/'.$article->id.'/'.\Illuminate\Support\Str::slug($article->title)) }}"
          target="_blank"
          class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-eye me-1"></i> {{ __('Preview') }}
        </a>

        {{-- Back --}}
        <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> {{ __('Back to site') }}
        </a>
      </div>

    </div>

    {{-- <x-notification /> --}}

    <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <x-nav-link href="#tab-content"
                    :active="$activeTab === 'content'"
                    id="tab-button-content"
                    icon="bi-layers"
                    data-url="{{ route('admin.articles.edit', [$site->id, $article->id]) }}?tab=content">
          {{ __('Content') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link href="#tab-settings"
                    :active="$activeTab === 'settings'"
                    id="tab-button-settings"
                    icon="bi-gear"
                    data-url="{{ route('admin.articles.edit', [$site->id, $article->id]) }}?tab=settings">
          {{ __('Settings') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link href="#tab-seo"
                    :active="$activeTab === 'seo'"
                    id="tab-button-seo"
                    icon="bi-search"
                    data-url="{{ route('admin.articles.edit', [$site->id, $article->id]) }}?tab=seo">
          {{ __('SEO') }}
        </x-nav-link>
      </li>
    </ul>


    {{-- =========================================================
        UNIFIED FORM - saves all tabs at once
    ========================================================== --}}
    <form action="{{ route('admin.articles.update', [$site->id, $article->id]) }}" method="POST" id="article-edit-form">
      @csrf
      @method('PATCH')

      <div class="tab-content">

        {{-- =========================================================
            CONTENT TAB (Inputs)
        ========================================================== --}}
        <div id="tab-content" class="tab-pane fade {{ $activeTab === 'content' ? 'show active' : '' }}" role="tabpanel">

          <div id="input-container">
            @foreach($data['inputs'] as $index => $input)
              @php
                $fieldType = $input->field?->field_type;

                $viewMap = [
                  'short_text'  => 'short_text',
                  'number'      => 'number',
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
                        $bladeSnippet = "{{ \$data['{$input->variable}']->value ?? '' }}";
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
                      <span class="badge bg-warning text-dark ms-1">locked</span>
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
                            class="btn btn-sm btn-outline-danger {{ $input->is_default ? 'disabled' : '' }}"
                            onclick="deleteInstance({{ $input->id }})">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>

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
        <div id="tab-settings" class="tab-pane fade {{ $activeTab === 'settings' ? 'show active' : '' }}" role="tabpanel">

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $article->title) }}" class="form-control" required>
          </div>

          {{-- Categories --}}
          <div class="row g-3 mb-3">
            <div class="col-lg-6">
              <div class="fw-semibold mb-2">Available Categories</div>
              <div class="category-picker-box">
                <ul class="list-group" id="availableCategories">
                  @foreach($data['categories'] as $cat)
                    @if(!in_array((string)$cat->id, $selectedIds, true))
                      <li class="list-group-item d-flex align-items-center justify-content-between gap-2"
                          data-id="{{ $cat->id }}">
                        <span class="me-auto">{{ $cat->name }}</span>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-add" title="Add">
                          <i class="bi bi-arrow-right"></i>
                        </button>
                      </li>
                    @endif
                  @endforeach
                </ul>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="fw-semibold mb-2">Selected Categories</div>
              <div class="category-picker-box">
                <ul class="list-group" id="selectedCategories">
                  @foreach($article->categories as $cat)
                    <li class="list-group-item d-flex align-items-center justify-content-between gap-2"
                        data-id="{{ $cat->id }}">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Remove">
                        <i class="bi bi-arrow-left"></i>
                      </button>
                      <span class="me-auto">{{ $cat->name }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
              <div class="form-text text-muted mt-2">Changes apply after Save.</div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="template">{{ __('Article template') }}</label>

              <select class="form-select" name="template" id="template">
                @foreach(($data['frontendArticleTemplates'] ?? collect()) as $tpl)
                  <option value="{{ $tpl }}" {{ old('template', $article->template ?? 'index') === $tpl ? 'selected' : '' }}>
                    {{ $tpl }}
                  </option>
                @endforeach
              </select>

              <div class="form-text text-muted">
                {{ __('Template file from resources/views/pages/frontend/articles/') }}
              </div>
            </div>

          </div>

          {{-- IMPORTANT: your controller expects selectedCategoryIds (string) --}}
          <input type="hidden"
                 name="selectedCategoryIds"
                 id="selectedCategoryIds"
                 value="{{ implode(',', $article->categories->pluck('id')->toArray()) }}">

        </div>

        {{-- =========================================================
            SEO TAB
        ========================================================== --}}
        <div id="tab-seo" class="tab-pane fade {{ $activeTab === 'seo' ? 'show active' : '' }}" role="tabpanel">

          @if(\App\Models\Setting::bool('ai_seo_enabled'))
            <div class="d-flex justify-content-end mb-3">
              <button type="button"
                      class="btn btn-outline-primary btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#aiSeoModal-article-{{ $article->id }}">
                <i class="bi bi-stars me-1"></i> {{ __('Generate SEO') }}
              </button>
            </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Meta title</label>
            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $article->meta_title) }}">
          </div>

          <div class="mb-3">
            <label class="form-label">Meta description</label>
            <textarea name="meta_description" class="form-control" rows="4">{{ old('meta_description', $article->meta_description) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Meta keywords</label>
            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $article->meta_keywords) }}">
          </div>

        </div>
      </div>

    </form>

    @include('pages.admin.partials.ai-seo-modal', [
        'contentType' => 'article',
        'contentId'   => $article->id,
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
          <form action="{{ route('admin.inputs.store', ['type' => 'article', 'id' => $article->id]) }}" method="POST">
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

{{-- =========================================================
    STICKY BOTTOM SAVE BAR
========================================================== --}}
<div class="sticky-bottom-bar bg-body border-top shadow-sm p-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-end align-items-center gap-3">
            <button type="submit" form="article-edit-form" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> {{ __('Save All Changes') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
  const OWNER_TYPE = 'article';

  document.addEventListener("DOMContentLoaded", function () {
    const available = document.getElementById("availableCategories");
    const selected  = document.getElementById("selectedCategories");
    const hidden    = document.getElementById("selectedCategoryIds");

    if (!available || !selected || !hidden) return;

    function updateHidden() {
      const ids = Array.from(selected.querySelectorAll("li"))
        .map(li => li.dataset.id)
        .filter(Boolean);
      const csv = ids.join(",");
      hidden.value = csv;
    }

    function renderAvailable(li, name) {
      li.className = "list-group-item d-flex align-items-center justify-content-between gap-2";
      li.innerHTML = `
        <span class="me-auto"></span>
        <button type="button" class="btn btn-sm btn-outline-primary btn-add" title="Add">
          <i class="bi bi-arrow-right"></i>
        </button>
      `;
      li.querySelector("span").textContent = name;
    }

    function renderSelected(li, name) {
      li.className = "list-group-item d-flex align-items-center justify-content-between gap-2";
      li.innerHTML = `
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Remove">
          <i class="bi bi-arrow-left"></i>
        </button>
        <span class="me-auto"></span>
      `;
      li.querySelector("span").textContent = name;
    }

    function getName(li) {
      const s = li.querySelector("span");
      return (s ? s.textContent : "").trim();
    }

    function moveToSelected(li) {
      const name = getName(li);
      li.classList.add('fade-out');
      setTimeout(() => {
        renderSelected(li, name);
        selected.appendChild(li);
        li.classList.add('fade-in');
        requestAnimationFrame(() => {
          li.classList.remove('fade-out', 'fade-in');
        });
        updateHidden();
      }, 150);
    }

    function moveToAvailable(li) {
      const name = getName(li);
      li.classList.add('fade-out');
      setTimeout(() => {
        renderAvailable(li, name);
        available.appendChild(li);
        li.classList.add('fade-in');
        requestAnimationFrame(() => {
          li.classList.remove('fade-out', 'fade-in');
        });
        updateHidden();
      }, 150);
    }

    available.addEventListener("click", (e) => {
      if (e.target.closest(".btn-add")) moveToSelected(e.target.closest("li"));
    });

    selected.addEventListener("click", (e) => {
      if (e.target.closest(".btn-remove")) moveToAvailable(e.target.closest("li"));
    });

    updateHidden();
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const contentExtras = document.getElementById('content-tab-extras');

    document.querySelectorAll('[data-url]').forEach(el => {
      el.addEventListener('click', () => {
        const url = el.getAttribute('data-url');
        if (!url) return;
        history.replaceState(null, '', url);

        // Show/hide content-tab-extras based on which tab is clicked
        const tab = el.getAttribute('href')?.replace('#tab-', '') || '';
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

    location.reload();
  }
</script>


@endpush

</x-app-layout>
