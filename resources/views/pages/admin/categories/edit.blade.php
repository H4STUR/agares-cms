<x-app-layout>
@php
  $activeTab = request('tab', 'content'); // content|settings|article-fields
@endphp

@php
    $ownerType = 'category';
    $ownerId   = $category->id;

    // For the "Article fields" tab
    $globalArticleItems   = $data['globalArticleTemplateItems'] ?? collect();
    $catArticleTemplate   = $data['categoryArticleTemplate'] ?? null;
    $catArticleItems      = $data['categoryArticleTemplateItems'] ?? collect();

    // Helpful prefix to avoid variable collisions for multi-category articles
    $catVarPrefix = 'cat_' . $category->id . '_';
@endphp

<div class="card mb-4">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-0">{{ __('Edit Category') }}: {{ $category->name }}</h4>
                <div class="text-muted small">{{ __('Site') }}: {{ $site->name }}</div>
            </div>

            <div class="d-flex gap-2">
                {{-- Back should go to site show --}}
                <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to site
                </a>
            </div>
        </div>

        {{-- <x-notification /> --}}

        {{-- Tabs --}}
        <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
  <li class="nav-item" role="presentation">
    <x-nav-link href="#tab-content"
                :active="$activeTab === 'content'"
                id="tab-button-content"
                icon="bi-layers"
                data-url="{{ route('admin.categories.edit', [$site->id, $category->id]) }}?tab=content">
      {{ __('Content') }}
    </x-nav-link>
  </li>

  <li class="nav-item" role="presentation">
    <x-nav-link href="#tab-settings"
                :active="$activeTab === 'settings'"
                id="tab-button-settings"
                icon="bi-gear"
                data-url="{{ route('admin.categories.edit', [$site->id, $category->id]) }}?tab=settings">
      {{ __('Settings') }}
    </x-nav-link>
  </li>

  <li class="nav-item" role="presentation">
    <x-nav-link href="#tab-article-fields"
                :active="$activeTab === 'article-fields'"
                id="tab-button-article-fields"
                icon="bi-journal-text"
                data-url="{{ route('admin.categories.edit', [$site->id, $category->id]) }}?tab=article-fields">
      {{ __('Article fields') }}
    </x-nav-link>
  </li>
</ul>


        <div class="tab-content">

            {{-- =========================================================
                CONTENT TAB (Category Inputs bulk update + gallery/files)
            ========================================================== --}}
            <div id="tab-content" class="tab-pane fade {{ $activeTab === 'content' ? 'show active' : '' }}" role="tabpanel">


                <form action="{{ route('admin.inputInstances.bulkUpdate') }}" method="POST" id="category-content-form">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                    <input type="hidden" name="owner_id" value="{{ $ownerId }}">

                    <div id="input-container">
                        @foreach($data['inputs'] as $index => $input)
                            @php
                                $fieldType = $input->field?->field_type;

                                $viewMap = [
                                    'short_text'   => 'short_text',
                                    'number'       => 'number',
                                    'text'         => 'text_editor',
                                    'text_editor'  => 'text_editor',
                                    'wysiwyg'      => 'text_editor',
                                    'textarea'     => 'textarea',
                                    'code'         => 'code',
                                    'file'         => 'file',
                                    'image'        => 'file',
                                    'gallery'      => 'gallery',
                                    'contact_form' => 'contact_form',
                                    'faq'          => 'faq',
                                ];

                                $partial = $viewMap[$fieldType] ?? 'textarea';
                            @endphp

                            <div class="input-item mb-4 p-3 border rounded" data-instance-id="{{ $input->id }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">
                                        {{ $input->label ?? ($input->description ?? 'No label') }}

                                        @if($input->variable)
                                            @php
                                                $bladeSnippet = "{{ input_value('{$input->variable}', \$site ?? null, \$category ?? null, \$article ?? null) }}";
                                            @endphp

                                            <span class="text-muted small ms-2 copy-variable"
                                                data-variable="{{ $input->variable }}"
                                                data-message-id="copy-message-{{ $input->id }}"
                                                style="cursor:pointer;"
                                                title="Click to copy Blade snippet">
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
                                        {{-- Move input instances (same logic you already use in sites) --}}
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

                </form>
            </div>

            {{-- =========================================================
                SETTINGS TAB
            ========================================================== --}}
            <div id="tab-settings" class="tab-pane fade {{ $activeTab === 'settings' ? 'show active' : '' }}" role="tabpanel">

                <form action="{{ route('admin.categories.update', [$site->id, $category->id]) }}" method="POST" id="category-settings-form">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label" for="name">{{ __('Name') }}</label>
                        <input class="form-control"
                               type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $category->name) }}"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="template">{{ __('Category template') }}</label>

                        <select class="form-select" name="template" id="template">
                            @foreach(($data['frontendCategoryTemplates'] ?? collect()) as $tpl)
                            <option value="{{ $tpl }}" {{ old('template', $category->template) === $tpl ? 'selected' : '' }}>
                                {{ $tpl }}
                            </option>
                            @endforeach
                        </select>

                        <div class="form-text text-muted">
                            {{ __('Template file from resources/views/pages/frontend/categories/') }}
                        </div>
                    </div>

                    <hr>
                
                    <div class="mb-4">
                        <label class="form-label" for="default_article_template">{{ __('Default article template') }}</label>

                        <select class="form-select" name="default_article_template" id="default_article_template">
                            @foreach(($data['frontendArticleTemplates'] ?? collect()) as $tpl)
                            <option value="{{ $tpl }}" {{ old('default_article_template', $category->default_article_template ?? 'index') === $tpl ? 'selected' : '' }}>
                                {{ $tpl }}
                            </option>
                            @endforeach
                        </select>

                        <div class="form-text text-muted">
                            {{ __('Template file from resources/views/pages/frontend/articles/. New articles will use this template (taken from the first selected category).') }}
                        </div>
                </div>

                </form>
            </div>

            {{-- =========================================================
                ARTICLE FIELDS TAB (Category → Article defaults + custom)
            ========================================================== --}}
            <div id="tab-article-fields" class="tab-pane fade {{ $activeTab === 'article-fields' ? 'show active' : '' }}" role="tabpanel">

                {{-- Global defaults (from seeder) --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i>
                            Global default article fields
                        </div>
                        <div class="text-muted small">
                            These apply to every article (recommended baseline).
                        </div>
                    </div>

                    @if($globalArticleItems->count() === 0)
                        <div class="text-muted">No global default items found.</div>
                    @else
                        <div class="row g-2">
                            @foreach($globalArticleItems as $it)
                                <div class="col-md-4">
                                    <div class="border rounded p-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-semibold">
                                                {{ $it->label ?? $it->variable }}
                                            </div>
                                            <span class="badge bg-secondary">
                                                {{ $it->field?->field_type ?? 'field' }}
                                            </span>
                                        </div>
                                        <div class="text-muted small">
                                            variable: <code>{{ $it->variable }}</code>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <hr class="my-4">

                {{-- Category-scoped fields --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="fw-semibold">
                            <i class="bi bi-tags me-1"></i>
                            Fields added by this category
                        </div>
                        <div class="text-muted small">
                            These are additive. For multi-category articles we strongly recommend variables start with:
                            <code>{{ $catVarPrefix }}</code>
                        </div>
                    </div>

                    {{-- Add new category field --}}
                    <div class="border rounded p-3 mb-3">
                        <form action="{{ route('admin.categories.articleTemplate.items.store', [$site->id, $category->id]) }}" method="POST" class="row g-2 align-items-end">
                            @csrf

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Field type</label>
                                <select name="input_field_id" class="form-select" required>
                                    @foreach($data['inputTypes'] as $field)
                                        <option value="{{ $field->id }}">{{ ucfirst($field->name) }} ({{ $field->field_type }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    Type of input
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Variable</label>
                                <input type="text" name="variable" class="form-control"
                                    value="{{ old('variable') }}"
                                    placeholder="e.g. specs (prefix added automatically)">

                                <div class="form-text text-muted">
                                    Must be unique. Prefer prefix <code>{{ $catVarPrefix }}</code>.
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small mb-1">Label</label>
                                <input type="text"
                                       name="label"
                                       class="form-control"
                                       value="{{ old('label') }}"
                                       placeholder="e.g. Specs / FAQ / Download">

                                <div class="form-text text-muted">
                                    Name that shows in edit page.
                                </div>
                            </div>

                            {{-- <div class="col-md-1 d-flex gap-2">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_locked" value="1" id="catFieldLocked">
                                    <label class="form-check-label small" for="catFieldLocked">lock</label>
                                </div>
                            </div> --}}

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Add field
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- List + reorder --}}
                    @if($catArticleItems->count() === 0)
                        <div class="text-muted">No category-specific fields yet.</div>
                    @else
                        <div class="text-muted small mb-2">Drag & drop to reorder (order will be used when showing category sections inside Article edit later).</div>

                        <div id="category-article-fields-list"
                             class="list-group"
                             data-reorder-url="{{ route('admin.categories.articleTemplate.items.reorder', [$site->id, $category->id]) }}">
                            @foreach($catArticleItems as $it)
                                <div class="list-group-item draggable d-flex justify-content-between align-items-center"
                                     draggable="true"
                                     data-item-id="{{ $it->id }}">
                                    <div class="d-flex flex-column">
                                        <div class="fw-semibold">
                                            {{ $it->label ?? $it->variable }}
                                            @if($it->is_locked)
                                                <span class="badge bg-warning text-dark ms-1">locked</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            <span class="badge bg-secondary me-1">{{ $it->field?->field_type ?? 'field' }}</span>
                                            variable: <code>{{ $it->variable }}</code>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                            <form action="{{ route('admin.categories.articleTemplate.items.delete', [$site->id, $category->id, $it->id]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Remove this field from category defaults? This does NOT delete existing article values unless you choose to implement cleanup.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>

                                        <span class="btn btn-sm btn-outline-secondary disabled" title="Drag to reorder">
                                            <i class="bi bi-grip-vertical"></i>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-muted small mt-2" id="cat-article-fields-msg"></div>
                    @endif
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
            {{-- Content tab save button --}}
            <button type="submit" form="category-content-form" class="btn btn-primary category-save-btn" data-tab="content" style="{{ $activeTab !== 'content' ? 'display:none;' : '' }}">
                <i class="bi bi-save me-1"></i> {{ __('Save Content') }}
            </button>
            {{-- Settings tab save button --}}
            <button type="submit" form="category-settings-form" class="btn btn-primary category-save-btn" data-tab="settings" style="{{ $activeTab !== 'settings' ? 'display:none;' : '' }}">
                <i class="bi bi-save me-1"></i> {{ __('Save Settings') }}
            </button>
            {{-- Article fields tab has no main save (individual forms) --}}
            <span class="text-muted category-save-btn" data-tab="article-fields" style="{{ $activeTab !== 'article-fields' ? 'display:none;' : '' }}">
                {{ __('Use individual save buttons above') }}
            </span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // DnD reorder for category article fields
    const list = document.getElementById('category-article-fields-list');
    if (list) setupSimpleDnDReorder(list);
});

function setupSimpleDnDReorder(list) {
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

        const msg = document.getElementById('cat-article-fields-msg');
        if (msg) msg.textContent = 'Saving order...';

        const url = list.getAttribute('data-reorder-url');
        const ids = Array.from(list.querySelectorAll('.draggable'))
            .map(el => parseInt(el.getAttribute('data-item-id'), 10))
            .filter(n => Number.isFinite(n));

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
}
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-url]').forEach(el => {
    el.addEventListener('click', () => {
      const url = el.getAttribute('data-url');
      if (url) history.replaceState(null, '', url);

      // Update sticky save bar button visibility based on active tab
      const href = el.getAttribute('href') || '';
      const tab = href.replace('#tab-', '');
      document.querySelectorAll('.category-save-btn').forEach(btn => {
        btn.style.display = btn.dataset.tab === tab ? '' : 'none';
      });
    });
  });
});
</script>

@endpush

</x-app-layout>
