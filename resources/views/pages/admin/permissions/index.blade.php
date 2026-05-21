<x-app-layout>

    @php
        $isOwner = auth()->check() && auth()->user()->hasRole('owner');
    @endphp


    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Manage Roles & Permissions') }}</div>
    </div>

    <div class="container-fluid">
        {{-- <x-notification /> --}}

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
                @if($role->name === 'owner' && !$isOwner)
                    @continue
                @endif

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
                        {{-- Header row with: accordion toggle + edit permissions + delete --}}
                        <div class="d-flex align-items-center gap-2">

                            <button class="accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                    aria-controls="{{ $collapseId }}">
                                <div class="d-flex align-items-center justify-content-between w-100 pe-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold">{{ ucfirst($role->name) }}</span>
                                        <span class="badge bg-secondary">{{ $usersWithRole->count() }}</span>
                                    </div>
                                    <small class="text-muted d-none d-md-inline">
                                        {{ __('Users in role') }}
                                    </small>
                                </div>
                            </button>

                            <div class="d-flex align-items-center gap-2 pe-2">
                                @if($role->name !== 'owner')
                                    <a href="{{ route('admin.permissions.roles.edit', $role->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                        {{ __('Edit') }}
                                    </a>
                                @endif

                                @if($role->name !== 'owner')
                                    <form action="{{ route('admin.roles.delete', $role->id) }}"
                                        method="POST"
                                        class="ms-1"
                                        onsubmit="return confirm('Delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>


                        </div>
                    </h2>

                    <div id="{{ $collapseId }}"
                        class="accordion-collapse collapse"
                        aria-labelledby="{{ $headingId }}">
                        {{-- NOTE: no data-bs-parent => accordions won't auto-close others --}}
                        <div class="accordion-body">

                            {{-- Users with this role --}}
                            @if($usersWithRole->isEmpty())
                                <p class="text-muted mb-0">{{ __('No users are assigned to this role.') }}</p>
                            @else
                                <div class="row g-3">
                                    @foreach($usersWithRole as $user)
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="card h-100">
                                                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="fw-semibold text-truncate">
                                                            {{ $user->name }}
                                                        </div>

                                                        <div class="text-muted small text-truncate">
                                                            @if(!empty($user->username))
                                                                {{ '@'.$user->username }}
                                                            @endif

                                                            @if(!empty($user->email))
                                                                @if(!empty($user->username)) • @endif
                                                                {{ $user->email }}
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.user.profile', $user->id) }}"
                                                           class="btn btn-sm btn-outline-secondary"
                                                           title="{{ __('Edit user') }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
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
