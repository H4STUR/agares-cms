<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Users') }}</div>
    </div>

    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-primary mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-all-users" role="tab">
                            <i class="bi bi-people me-1"></i> {{ __('All Users') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-add-user" role="tab">
                            <i class="bi bi-person-plus me-1"></i> {{ __('Add User') }}
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- All Users -->
                    <div class="tab-pane fade show active" id="tab-all-users" role="tabpanel">
                        @foreach($data['users'] as $user)
                            <div class="list-group mb-2">
                                <a href="{{ route('admin.user.profile', $user->id) }}"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ isset($data['user']) && $data['user']->id === $user->id ? 'active' : '' }}">
                                    <span>
                                        {{ $user->username }} [ {{ $user->name }} ] [ {{ $user->getRoleNames()->first() ?? '-' }} ]
                                    </span>
                                    <span>
                                        @if ($user->hasTwoFactorEnabled())
                                            @php
                                                $methodLabel = $user->two_factor_method === 'email' ? __('Email') : __('TOTP');
                                            @endphp
                                            <span class="badge bg-success" title="{{ __('Two-factor authentication enabled') }} ({{ $methodLabel }})">
                                                <i class="bi bi-shield-check me-1"></i>2FA · {{ $methodLabel }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary" title="{{ __('Two-factor authentication not enabled') }}">
                                                <i class="bi bi-shield me-1"></i>{{ __('No 2FA') }}
                                            </span>
                                        @endif
                                    </span>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Add User -->
                    <div class="tab-pane fade" id="tab-add-user" role="tabpanel">
                        <h5 class="mb-3">{{ __('Create New User') }}</h5>
                        <form action="{{ route('admin.users.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">{{ __('Username') }}</label>
                                    <input type="text" id="username" name="username" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('Name') }}</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="surname" class="form-label">{{ __('Surname') }}</label>
                                    <input type="text" id="surname" name="surname" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">{{ __('Password') }}</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                </div>

                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary" id="generatePassword">
                                        <i class="bi bi-shuffle me-1"></i>{{ __('Generate Password') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                    
                                </div>

                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">{{ __('Role') }}</label>
                                    <select name="role_id" id="role_id" class="form-select" required>
                                        @foreach($data['roles'] as $role)
                                            @if(!$loop->first)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <x-primary-button type="submit">{{ __('Create User') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('password_confirmation');
            const eyeIcon = document.getElementById('eyeIcon');
    
            togglePassword?.addEventListener('click', () => {
                const type = passwordField.type === 'password' ? 'text' : 'password';
                passwordField.type = type;
                confirmPasswordField.type = type;
    
                // Toggle icon class
                eyeIcon.classList.toggle('bi-eye');
                eyeIcon.classList.toggle('bi-eye-slash');
            });
    
            // Generate password
            document.getElementById('generatePassword')?.addEventListener('click', () => {
                const pwd = Math.random().toString(36).slice(-10);
                passwordField.value = pwd;
                confirmPasswordField.value = pwd;
            });
        });
    </script>
    @endpush
    
</x-app-layout>
