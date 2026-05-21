<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Edit Permissions') }}</div>
        <div class="ps-3">
            <span class="text-muted">/</span>
            <span class="ms-2 fw-semibold">{{ ucfirst($role->name) }}</span>
        </div>
    </div>

    <div class="container-fluid">
        {{-- <x-notification /> --}}

        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h5 class="mb-0">{{ __('Role') }}: {{ ucfirst($role->name) }}</h5>
                <small class="text-muted">{{ __('Configure page-level and CMS-level permissions for this role.') }}</small>
            </div>

            <a href="{{ route('admin.permissions') }}" class="btn btn-outline-secondary">
                {{ __('Back') }}
            </a>
        </div>

        <form action="{{ route('admin.permissions.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PATCH')

            {{-- =========================
                 1) PAGE PERMISSIONS TABLE
                 ========================= --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Page permissions') }}</h6>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 280px;">{{ __('Site') }}</th>

                                    {{-- Equal-width permission cols --}}
                                    <th class="text-center" style="width: 110px;">{{ __('View') }}</th>
                                    <th class="text-center" style="width: 110px;">{{ __('Edit') }}</th>
                                    <th class="text-center" style="width: 130px;">{{ __('Categories') }}</th>
                                    <th class="text-center" style="width: 120px;">{{ __('Articles') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($sites as $site)
                                    @php
                                        // Row from role_site_permissions table if exists
                                        $row = $sitePerms[$site->id] ?? null;

                                        $canView       = (bool)($row->can_view ?? false);
                                        $canEdit       = (bool)($row->can_edit ?? false);
                                        $canCategories = (bool)($row->can_categories ?? false);
                                        $canArticles   = (bool)($row->can_articles ?? false);

                                        $cbViewId       = "siteperm_{$role->id}_{$site->id}_view";
                                        $cbEditId       = "siteperm_{$role->id}_{$site->id}_edit";
                                        $cbCatsId       = "siteperm_{$role->id}_{$site->id}_categories";
                                        $cbArticlesId   = "siteperm_{$role->id}_{$site->id}_articles";
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $site->name }}</div>
                                            <div class="text-muted small">({{ $site->slug }})</div>
                                        </td>

                                        {{-- VIEW --}}
                                        <td class="text-center">
                                            <input type="hidden" name="site_permissions[{{ $site->id }}][can_view]" value="0">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="{{ $cbViewId }}"
                                                   name="site_permissions[{{ $site->id }}][can_view]"
                                                   value="1"
                                                   {{ $canView ? 'checked' : '' }}>
                                        </td>

                                        {{-- EDIT --}}
                                        <td class="text-center">
                                            <input type="hidden" name="site_permissions[{{ $site->id }}][can_edit]" value="0">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="{{ $cbEditId }}"
                                                   name="site_permissions[{{ $site->id }}][can_edit]"
                                                   value="1"
                                                   {{ $canEdit ? 'checked' : '' }}>
                                        </td>

                                        {{-- CATEGORIES --}}
                                        <td class="text-center">
                                            <input type="hidden" name="site_permissions[{{ $site->id }}][can_categories]" value="0">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="{{ $cbCatsId }}"
                                                   name="site_permissions[{{ $site->id }}][can_categories]"
                                                   value="1"
                                                   {{ $canCategories ? 'checked' : '' }}>
                                        </td>

                                        {{-- ARTICLES --}}
                                        <td class="text-center">
                                            <input type="hidden" name="site_permissions[{{ $site->id }}][can_articles]" value="0">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="{{ $cbArticlesId }}"
                                                   name="site_permissions[{{ $site->id }}][can_articles]"
                                                   value="1"
                                                   {{ $canArticles ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted d-block mt-3">
                        {{ __('These permissions are stored per role + per site in role_site_permissions.') }}
                    </small>
                </div>
            </div>

            {{-- =========================
                 2) CMS-WIDE PERMISSIONS (Spatie role_has_permissions)
                 ========================= --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('CMS permissions') }}</h6>

                    @php
                        $grouped = $permissions->groupBy(fn($p) => $p->category ?? 'general');
                    @endphp

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        @foreach($grouped as $category => $group)
                            @php
                                $safe = \Illuminate\Support\Str::slug($category);
                                $tabId = "cms-tab-{$safe}";
                                $btnId = "{$tabId}-btn";
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="{{ $btnId }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $tabId }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="{{ $tabId }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ ucfirst($category) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($grouped as $category => $group)
                            @php
                                $safe = \Illuminate\Support\Str::slug($category);
                                $tabId = "cms-tab-{$safe}";
                            @endphp

                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="{{ $tabId }}"
                                 role="tabpanel"
                                 aria-labelledby="{{ $tabId }}-btn">

                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                                    @foreach($group as $permission)
                                        @php $id = "perm_{$role->id}_{$permission->id}"; @endphp
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permission->name }}"
                                                       id="{{ $id }}"
                                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4">
                        <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</x-app-layout>
