<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Manage Roles & Permissions') }}</div>
    </div>

    <div class="container-fluid">
        {{-- <x-notification /> --}}

        @php
            // Group permissions once
            $groupedPermissions = $permissions->groupBy('category');
        @endphp

        {{-- Add New Role --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.roles.create') }}" method="POST" class="row g-3 align-items-center">
                    @csrf
                    <div class="col">
                        <input type="text" name="name" placeholder="New role name" class="form-control" required>
                    </div>
                    <div class="col-auto">
                        <x-primary-button type="submit">{{ __('Add Role') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Roles Accordion --}}
        <div class="accordion" id="rolesAccordion">
            @foreach ($roles as $index => $role)
                @php
                    // Spatie stores roles in pivot table model_has_roles, NOT users.role_id
                    $usersWithRole = \App\Models\User::query()
                        ->role($role->name)
                        ->orderBy('name')
                        ->get();


                    $headingId  = "heading-role-{$role->id}";
                    $collapseId = "collapse-role-{$role->id}";

                    $isFirst = $index === 0;
                @endphp

                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="{{ $headingId }}">
                        <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                aria-controls="{{ $collapseId }}">
                            {{ ucfirst($role->name) }}
                        </button>
                    </h2>

                    <div id="{{ $collapseId }}"
                         class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                         aria-labelledby="{{ $headingId }}">
                         {{-- NOTE: no data-bs-parent => accordions won't auto-close others --}}
                        <div class="accordion-body">

                            <div class="d-flex justify-content-end mb-3">
                                <form action="{{ route('admin.roles.delete', $role->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button>{{ __('Delete Role') }}</x-danger-button>
                                </form>
                            </div>

                            {{-- Users with this role --}}
                            @if($usersWithRole->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="mb-2">{{ __('Users with this role:') }}</h6>

                                    <div class="list-group">
                                        @foreach($usersWithRole as $user)
                                            <a href="{{ route('admin.user.profile', $user->id) }}"
                                               class="list-group-item list-group-item-action">
                                                {{ $user->username ?? '' }} @if(!empty($user->username))|@endif {{ $user->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">{{ __('No users are assigned to this role.') }}</p>
                            @endif

                            {{-- Permissions Form --}}
                            <form action="{{ route('admin.permissions.assign') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role_id" value="{{ $role->id }}">

                                {{-- Permission Tabs --}}
                                <ul class="nav nav-tabs mb-3" id="perm-tabs-{{ $role->id }}" role="tablist">
                                    @foreach ($groupedPermissions as $category => $group)
                                        @php
                                            $safeCategory = \Illuminate\Support\Str::slug($category);
                                            $tabId = "tab-{$role->id}-{$safeCategory}";
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
                                    @foreach ($groupedPermissions as $category => $group)
                                        @php
                                            $safeCategory = \Illuminate\Support\Str::slug($category);
                                            $tabId = "tab-{$role->id}-{$safeCategory}";
                                        @endphp

                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                             id="{{ $tabId }}"
                                             role="tabpanel"
                                             aria-labelledby="{{ $tabId }}-btn">

                                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                                                @foreach ($group as $permission)
                                                    @php
                                                        $checkboxId = "perm_{$role->id}_{$permission->id}";
                                                    @endphp

                                                    <div class="col">
                                                        <div class="form-check">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="permissions[]"
                                                                   value="{{ $permission->name }}"
                                                                   id="{{ $checkboxId }}"
                                                                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{ $checkboxId }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <x-primary-button type="submit">{{ __('Save Permissions') }}</x-primary-button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Add New Permission --}}
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title">{{ __('Add New Permission') }}</h5>
                <form action="{{ route('admin.permissions.create') }}" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="col">
                        <input type="text" name="name" class="form-control" placeholder="Permission name" required>
                    </div>
                    <div class="col-auto">
                        <select name="category" class="form-select">
                            <option value="general">General</option>
                            <option value="portal">Portal</option>
                            <option value="users">Users</option>
                            <option value="edit">Edit</option>
                            <option value="media">Media</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <x-primary-button type="submit">{{ __('Add') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
