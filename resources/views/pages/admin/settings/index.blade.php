<x-app-layout>

    @php
        $activeTab = request('tab', 'general'); // default
        $tabs = ['general','security','social_media','seo','add-ons','custom'];
        if (!in_array($activeTab, $tabs, true)) $activeTab = 'general';
    @endphp

    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Settings') }}</div>
    </div>

    <div class="container-fluid">

        <!-- Main Settings Card -->
        <div class="card">
            <div class="card-body">

                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
                    @foreach(['general', 'security', 'social_media', 'seo', 'add-ons', 'custom'] as $category)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link tab-button {{ $activeTab === $category ? 'active' : '' }}"
                                data-bs-toggle="tab"
                                href="#tab-{{ $category }}"
                                role="tab"
                                id="tab-button-{{ $category }}"
                                data-tab="{{ $category }}"
                                data-url="{{ route('admin.settings') }}?tab={{ $category }}">
                                {{ ucwords(str_replace('_', ' ', $category)) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- =========================================================
                    UNIFIED FORM - saves all tabs at once
                ========================================================== --}}
                <form action="{{ route('admin.settings.update') }}" method="POST" id="settings-form">
                    @csrf
                    @method('PATCH')

                    <div class="tab-content">

                        {{-- =========================================================
                            GENERAL TAB
                        ========================================================== --}}
                        <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general" role="tabpanel">

                            <h5 class="mb-4">{{ __('General Settings') }}</h5>

                            @foreach($settings->where('category', 'general') as $setting)
                                <div class="mb-4">
                                    @if($setting->type === 'boolean')
                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                        <x-toggle-switch name="settings[{{ $setting->key }}]" :checked="$setting->value" id="toggle-{{ $setting->id }}" />
                                    @endif
                                    <label class="form-label" @if($setting->type === 'boolean') for="toggle-{{ $setting->id }}" @endif>
                                        {{ $setting->description ?? $setting->key }}

                                        <span
                                            class="text-muted small ms-2 copy-setting-key"
                                            data-key="{{ $setting->key }}"
                                            data-message-id="copy-setting-message-{{ $setting->id }}"
                                            style="cursor:pointer;"
                                            title="Click to copy setting key"
                                        >
                                            ({{ $setting->key }})
                                        </span>

                                        <span id="copy-setting-message-{{ $setting->id }}" class="text-success small ms-2 d-none">
                                            Copied!
                                        </span>
                                    </label>

                                    @if(in_array($setting->type, ['string', 'integer']))
                                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                                    @elseif($setting->type === 'json')
                                        <textarea name="settings[{{ $setting->key }}]" rows="3" class="form-control">{{ $setting->value }}</textarea>
                                    @endif
                                </div>
                            @endforeach

                        </div>

                        {{-- =========================================================
                            OTHER CATEGORY TABS (security, social_media, seo, add-ons, forum, API)
                        ========================================================== --}}
                        @foreach(['security', 'social_media', 'seo', 'add-ons'] as $category)
                            <div class="tab-pane fade {{ $activeTab === $category ? 'show active' : '' }}" id="tab-{{ $category }}" role="tabpanel">

                                <h5 class="mb-4">{{ ucwords(str_replace('_', ' ', $category)) }} Settings</h5>

                                @foreach($settings->where('category', $category) as $setting)
                                    <div class="mb-4">
                                        @if($setting->type === 'boolean')
                                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                            <x-toggle-switch name="settings[{{ $setting->key }}]" :checked="$setting->value" id="toggle-{{ $setting->id }}" />
                                        @endif
                                        <label class="form-label" @if($setting->type === 'boolean') for="toggle-{{ $setting->id }}" @endif>
                                            {{ $setting->description ?? $setting->key }}

                                            <span
                                                class="text-muted small ms-2 copy-setting-key"
                                                data-key="{{ $setting->key }}"
                                                data-message-id="copy-setting-message-{{ $setting->id }}"
                                                style="cursor:pointer;"
                                                title="Click to copy setting key"
                                            >
                                                ({{ $setting->key }})
                                            </span>

                                            <span id="copy-setting-message-{{ $setting->id }}" class="text-success small ms-2 d-none">
                                                Copied!
                                            </span>
                                        </label>

                                        @if(in_array($setting->type, ['string', 'integer']))
                                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                                        @elseif($setting->type === 'json')
                                            <textarea name="settings[{{ $setting->key }}]" rows="3" class="form-control">{{ $setting->value }}</textarea>
                                        @endif
                                    </div>
                                @endforeach

                            </div>
                        @endforeach

                        {{-- =========================================================
                            CUSTOM TAB
                        ========================================================== --}}
                        <div class="tab-pane fade {{ $activeTab === 'custom' ? 'show active' : '' }}" id="tab-custom" role="tabpanel">

                            <h5 class="mb-4">Custom Settings</h5>

                            @foreach($settings->where('category', 'custom') as $setting)
                                <div class="mb-4">
                                    <label class="form-label" @if($setting->type === 'boolean') for="toggle-{{ $setting->id }}" @endif>
                                        {{ $setting->description ?? $setting->key }}

                                        <span
                                            class="text-muted small ms-2 copy-setting-key"
                                            data-key="{{ $setting->key }}"
                                            data-message-id="copy-setting-message-{{ $setting->id }}"
                                            style="cursor:pointer;"
                                            title="Click to copy setting key"
                                        >
                                            ({{ $setting->key }})
                                        </span>

                                        <span id="copy-setting-message-{{ $setting->id }}" class="text-success small ms-2 d-none">
                                            Copied!
                                        </span>
                                    </label>

                                    @if($setting->type === 'boolean')
                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                        <x-toggle-switch name="settings[{{ $setting->key }}]" :checked="$setting->value" id="toggle-{{ $setting->id }}" />
                                    @elseif(in_array($setting->type, ['string', 'integer']))
                                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                                    @elseif($setting->type === 'json')
                                        <textarea name="settings[{{ $setting->key }}]" rows="3" class="form-control">{{ $setting->value }}</textarea>
                                    @endif

                                    <div class="mt-2">
                                        <small class="text-muted">Key: <code>{{ $setting->key }}</code> • Type: <code>{{ $setting->type }}</code></small>
                                    </div>
                                </div>
                            @endforeach

                        </div>

                    </div>

                    {{-- =========================================================
                        UNIFIED SAVE BUTTON
                    ========================================================== --}}
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> {{ __('Save All Settings') }}
                        </button>
                    </div>
                </form>

                {{-- =========================================================
                    EXTRAS OUTSIDE THE MAIN FORM
                ========================================================= --}}

                {{-- General tab extras --}}
                <div id="general-tab-extras" class="{{ $activeTab !== 'general' ? 'd-none' : '' }} mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">{{ __('Cache Management') }}</h6>
                            <form action="{{ route('admin.cache.clear') }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-trash"></i> {{ __('Clear cache') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- SEO tab extras (robots.txt & sitemap) --}}
                <div id="seo-tab-extras" class="{{ $activeTab !== 'seo' ? 'd-none' : '' }} mt-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">robots.txt</h5>
                            <p class="text-muted mb-3">
                                {{ __('Edit robots.txt in your public folder. If it does not exist, it will be created when you save.') }}
                            </p>

                            <form action="{{ route('admin.settings.robots.save') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <textarea name="robots" rows="12" class="form-control"
                                            style="white-space: pre; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono','Courier New', monospace;">{{ $robotsContent ?? '' }}</textarea>
                                </div>

                                <div class="text-end">
                                    <x-primary-button type="submit">{{ __('Save robots.txt') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">sitemap.xml</h5>
                            <p class="text-muted mb-3">
                                {{ __('Generate sitemap.xml into your public folder and preview its content below.') }}
                            </p>

                            <form action="{{ route('admin.settings.sitemap.generate') }}" method="POST" class="mb-4">
                                @csrf
                                <x-primary-button type="submit">{{ __('Generate sitemap') }}</x-primary-button>
                            </form>

                            <h6 class="mb-2">{{ __('Current sitemap.xml content') }}</h6>

                            @if(!empty($sitemapContent))
                                <p style="white-space: pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono','Courier New', monospace;">
                                    {{ $sitemapContent }}
                                </p>
                            @else
                                <p class="text-muted mb-0">{{ __('No sitemap.xml found in public folder.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Custom tab extras (Add new custom setting) --}}
                <div id="custom-tab-extras" class="{{ $activeTab !== 'custom' ? 'd-none' : '' }} mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">{{ __('Add New Custom Setting') }}</h6>

                            <form action="{{ route('admin.settings.storeCustom') }}" method="POST" class="row g-3 align-items-end">
                                @csrf

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Key') }}</label>
                                    <input type="text" name="new_key" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Value') }}</label>
                                    <input type="text" name="new_value" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('Type') }}</label>
                                    <select name="new_type" class="form-select" required>
                                        <option value="string">String</option>
                                        <option value="integer">Integer</option>
                                        <option value="boolean">Boolean</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <input type="text" name="new_description" class="form-control">
                                </div>

                                <div class="col-md-1 d-grid">
                                    <x-primary-button type="submit">{{ __('Add') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Copy setting key functionality
        document.querySelectorAll('.copy-setting-key').forEach(el => {
            el.addEventListener('click', async (event) => {
                event.stopPropagation();
                event.preventDefault();
                const key = el.getAttribute('data-key') || '';
                if (!key) return;

                try {
                    await navigator.clipboard.writeText(key);
                } catch (e) {
                    // Fallback for older browsers / non-HTTPS
                    const tmp = document.createElement('textarea');
                    tmp.value = key;
                    tmp.style.position = 'fixed';
                    tmp.style.opacity = '0';
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand('copy');
                    document.body.removeChild(tmp);
                }

                const msgId = el.getAttribute('data-message-id');
                if (msgId) {
                    const msg = document.getElementById(msgId);
                    if (msg) {
                        msg.classList.remove('d-none');
                        clearTimeout(msg._t);
                        msg._t = setTimeout(() => msg.classList.add('d-none'), 1200);
                    }
                }
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const generalExtras = document.getElementById('general-tab-extras');
        const seoExtras = document.getElementById('seo-tab-extras');
        const customExtras = document.getElementById('custom-tab-extras');

        document.querySelectorAll('[data-tab][data-url]').forEach(el => {
            el.addEventListener('click', () => {
                const url = el.getAttribute('data-url');
                if (!url) return;
                history.replaceState(null, '', url);

                // Show/hide tab extras based on which tab is clicked
                const tab = el.getAttribute('data-tab');

                // Hide all extras first
                if (generalExtras) generalExtras.classList.add('d-none');
                if (seoExtras) seoExtras.classList.add('d-none');
                if (customExtras) customExtras.classList.add('d-none');

                // Show the relevant extras
                if (tab === 'general' && generalExtras) {
                    generalExtras.classList.remove('d-none');
                } else if (tab === 'seo' && seoExtras) {
                    seoExtras.classList.remove('d-none');
                } else if (tab === 'custom' && customExtras) {
                    customExtras.classList.remove('d-none');
                }
            });
        });
    });
</script>
@endpush

</x-app-layout>
