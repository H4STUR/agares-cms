{{-- AI SEO Generator modal.

  Usage:
    @include('pages.admin.partials.ai-seo-modal', [
        'contentType' => 'article',  // article | product | category
        'contentId'   => $article->id,
    ])

  The modal reads current form values for meta_title / meta_description / slug from the
  closest enclosing form (`#article-edit-form` / product / category form) and posts them
  to /admin/ai-seo/generate. Per-field "Accept" writes the suggested value into the
  matching `name=""` input/textarea on the page — it never saves anything itself.
--}}

@php
    $aiSeoEnabled = \App\Models\Setting::bool('ai_seo_enabled');
@endphp

@if($aiSeoEnabled)
<div
    class="modal fade"
    id="aiSeoModal-{{ $contentType }}-{{ $contentId }}"
    tabindex="-1"
    aria-hidden="true"
    x-data="aiSeoModal({
        contentType: '{{ $contentType }}',
        contentId:   {{ (int) $contentId }},
        endpoint:    '{{ route('admin.ai-seo.generate') }}',
        csrf:        '{{ csrf_token() }}',
        fieldMap:    @json($fieldMap ?? null),
    })"
>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-magic me-2 text-primary"></i>
                    {{ __('AI SEO Generator') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                {{-- ── Controls ── --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-medium mb-1">{{ __('Mode') }}</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" :id="`aiseo-mode-generate-${contentId}`" value="generate" x-model="mode">
                            <label class="btn btn-outline-primary" :for="`aiseo-mode-generate-${contentId}`">
                                {{ __('Generate from scratch') }}
                            </label>

                            <input type="radio" class="btn-check" :id="`aiseo-mode-improve-${contentId}`" value="improve" x-model="mode">
                            <label class="btn btn-outline-primary" :for="`aiseo-mode-improve-${contentId}`">
                                {{ __('Improve existing') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-medium mb-1">{{ __('Focus keyword') }} <span class="text-muted fw-normal">({{ __('optional') }})</span></label>
                        <input type="text" class="form-control" x-model="focusKeyword" placeholder="{{ __('e.g. wedding photographer Krakow') }}">
                    </div>

                    <div class="col-md-2 d-grid">
                        <label class="form-label mb-1" style="visibility:hidden">_</label>
                        <button type="button" class="btn btn-primary" @click="run()" :disabled="loading">
                            <template x-if="!loading"><span><i class="bi bi-stars me-1"></i>{{ __('Generate') }}</span></template>
                            <template x-if="loading">
                                <span>
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    {{ __('Working…') }}
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

                {{-- ── Error banner ── --}}
                <div class="alert alert-danger" x-show="error" x-cloak x-transition>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span x-text="error"></span>
                </div>

                {{-- ── Warnings banner ── --}}
                <div class="alert alert-warning" x-show="warnings.length" x-cloak x-transition>
                    <i class="bi bi-info-circle me-1"></i>
                    <ul class="mb-0 ps-3">
                        <template x-for="w in warnings" :key="w">
                            <li x-text="w"></li>
                        </template>
                    </ul>
                </div>

                {{-- ── Empty state ── --}}
                <div class="text-center text-muted py-4" x-show="!loading && !hasResults && !error" x-cloak>
                    <i class="bi bi-search" style="font-size:2rem"></i>
                    <p class="mb-0 mt-2">
                        {{ __('Pick a mode and click Generate to see AI-suggested SEO metadata.') }}
                    </p>
                    <small>{{ __('Suggestions never save automatically — you accept them one by one.') }}</small>
                </div>

                {{-- ── Results table ── --}}
                <div x-show="hasResults" x-cloak>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ __('Suggestions') }}</strong>
                            <small class="text-muted ms-2" x-show="model" x-cloak>
                                <i class="bi bi-cpu me-1"></i><span x-text="model"></span>
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" @click="acceptAll()">
                            <i class="bi bi-check2-all me-1"></i> {{ __('Accept all') }}
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 border">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 18%">{{ __('Field') }}</th>
                                    <th style="width: 36%">{{ __('Current') }}</th>
                                    <th style="width: 36%">{{ __('Suggested') }}</th>
                                    <th style="width: 10%" class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in rows" :key="row.key">
                                    <tr>
                                        <td>
                                            <div class="fw-medium" x-text="row.label"></div>
                                            <small class="text-muted" x-show="row.lengthInfo" x-text="row.lengthInfo"></small>
                                            <small class="d-block text-warning" x-show="row.overflow">{{ __('Over recommended length') }}</small>
                                            <small class="d-block text-muted" x-show="!row.targetExists">{{ __('No matching field on this page') }}</small>
                                        </td>
                                        <td>
                                            <code class="small text-body-secondary" x-text="row.current || '—'"></code>
                                        </td>
                                        <td>
                                            <code class="small" x-text="row.suggested || '—'"></code>
                                        </td>
                                        <td class="text-end">
                                            <button type="button"
                                                    class="btn btn-sm"
                                                    :class="row.accepted ? 'btn-success' : 'btn-outline-success'"
                                                    :disabled="!row.targetExists || !row.suggested"
                                                    @click="accept(row)">
                                                <template x-if="row.accepted"><i class="bi bi-check2"></i></template>
                                                <template x-if="!row.accepted"><span>{{ __('Accept') }}</span></template>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('Accepting writes the value into the form. You still need to save the form to persist changes.') }}
                    </small>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
            </div>

        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        (function () {
            // Field metadata: label + how to format the "length" info under each row.
            const FIELD_LABELS = {
                meta_title:       { label: 'Meta title',       max: 60  },
                meta_description: { label: 'Meta description', max: 160 },
                slug:             { label: 'Slug',             max: null },
                og_title:         { label: 'OG title',         max: null },
                og_description:  { label: 'OG description',   max: null },
                schema_jsonld:    { label: 'Schema.org JSON-LD', max: null },
                image_alt:        { label: 'Image alt text',   max: null },
            };

            // Default mapping suggestion keys → form input "name" attribute.
            // image_alt has no first-class input in the article form; rows without a matching
            // input on the page are still shown but the Accept button is disabled.
            // Site edit uses non-`meta_*` names — overridden via the `fieldMap` modal prop.
            const DEFAULT_FIELD_TO_INPUT_NAME = {
                meta_title:       'meta_title',
                meta_description: 'meta_description',
                slug:             'slug',
                og_title:         'og_title',
                og_description:   'og_description',
                schema_jsonld:    'schema_jsonld',
                image_alt:        'image_alt',
            };

            function findInputByName(name) {
                if (!name) return null;
                // Prefer inputs/textareas within the currently visible page form, fall back to any match.
                return document.querySelector(`[name="${name}"]`);
            }

            function readCurrent(name) {
                const el = findInputByName(name);
                if (!el) return '';
                return (el.value ?? '').toString();
            }

            function writeValue(name, value) {
                const el = findInputByName(name);
                if (!el) return false;
                el.value = value;
                // Notify any Alpine / framework listeners.
                el.dispatchEvent(new Event('input',  { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            }

            window.aiSeoModal = function (cfg) {
                const fieldMap = Object.assign({}, DEFAULT_FIELD_TO_INPUT_NAME, cfg.fieldMap || {});

                return {
                    contentType:  cfg.contentType,
                    contentId:    cfg.contentId,
                    endpoint:     cfg.endpoint,
                    csrf:         cfg.csrf,
                    fieldMap:     fieldMap,

                    mode:         'generate',
                    focusKeyword: '',

                    loading:  false,
                    error:    '',
                    warnings: [],
                    model:    '',
                    rows:     [],

                    get hasResults() { return this.rows.length > 0; },

                    async run() {
                        this.loading  = true;
                        this.error    = '';
                        this.warnings = [];
                        this.rows     = [];
                        this.model    = '';

                        const payload = {
                            content_type:             this.contentType,
                            content_id:               this.contentId,
                            mode:                     this.mode,
                            focus_keyword:            this.focusKeyword || null,
                            current_meta_title:       readCurrent(this.fieldMap.meta_title),
                            current_meta_description: readCurrent(this.fieldMap.meta_description),
                            current_slug:             readCurrent(this.fieldMap.slug),
                        };

                        try {
                            const resp = await fetch(this.endpoint, {
                                method:  'POST',
                                headers: {
                                    'Content-Type':     'application/json',
                                    'Accept':           'application/json',
                                    'X-CSRF-TOKEN':     this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(payload),
                                credentials: 'same-origin',
                            });

                            const data = await resp.json().catch(() => ({}));

                            if (!resp.ok) {
                                this.error = data.error || data.message || `Request failed (${resp.status}).`;
                                return;
                            }

                            this.model    = data.model || '';
                            this.warnings = Array.isArray(data.warnings) ? data.warnings : [];
                            this.rows     = this.buildRows(data.suggestions || {});
                        } catch (e) {
                            this.error = e.message || 'Unexpected error.';
                        } finally {
                            this.loading = false;
                        }
                    },

                    buildRows(suggestions) {
                        const rows = [];
                        for (const key of Object.keys(FIELD_LABELS)) {
                            const meta = FIELD_LABELS[key];
                            const inputName = this.fieldMap[key];
                            const sugg = suggestions[key];
                            if (sugg === undefined || sugg === null) continue;

                            const suggValue = this.formatSuggested(key, sugg);
                            const length = typeof sugg.length === 'number' ? sugg.length : suggValue.length;
                            const max    = typeof sugg.max    === 'number' ? sugg.max    : meta.max;
                            const lengthInfo = (max !== null && max !== undefined)
                                ? `${length}/${max} chars`
                                : `${length} chars`;

                            rows.push({
                                key,
                                label:         meta.label,
                                inputName,
                                current:       readCurrent(inputName),
                                suggested:     suggValue,
                                lengthInfo,
                                overflow:      (max !== null && max !== undefined) ? (length > max) : false,
                                targetExists:  !!findInputByName(inputName),
                                accepted:      false,
                            });
                        }
                        return rows;
                    },

                    formatSuggested(key, sugg) {
                        const v = sugg.value;
                        if (v === undefined || v === null) return '';
                        if (key === 'schema_jsonld' && typeof v === 'object') {
                            try { return JSON.stringify(v, null, 2); } catch (_) { return String(v); }
                        }
                        return String(v);
                    },

                    accept(row) {
                        if (!row.suggested) return;
                        if (writeValue(row.inputName, row.suggested)) {
                            row.accepted = true;
                            row.current  = row.suggested;
                        }
                    },

                    acceptAll() {
                        for (const row of this.rows) {
                            if (row.targetExists && row.suggested && !row.accepted) {
                                this.accept(row);
                            }
                        }
                    },
                };
            };
        })();
    </script>
    @endpush
@endonce
@endif
