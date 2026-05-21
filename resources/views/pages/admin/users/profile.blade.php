<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ $user->name }}'s Profile</div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('admin.user.settings', $user->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i>Settings
            </a>
            <a href="{{ route('admin.user.profile', $user) }}?view=public" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i>Public view
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-lg-8 col-xl-9">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="position-relative text-center">

                            <div class="ratio" style="--bs-aspect-ratio: calc(5 / 18 * 100%)">
                                <img src="{{ $user->background_image_url }}" class="img-fluid rounded object-fit-cover w-100 h-100" alt="">
                            </div>

                            <div class="position-absolute top-100 start-50 translate-middle">
                                <img src="{{ $user->avatar_url }}" width="110" height="110" class="rounded-circle raised p-1 bg-white object-fit-cover" alt="{{ __('User avatar') }}" >
                            </div>

                        </div>

                        
                        <div class="mt-5 d-flex align-items-start justify-content-between">
                            <div>
                                <h3 class="mb-2">{{ $user->username }}</h3>
                                <p class="mb-1">{{ $user->name }} ({{ $user->email }})</p>
                                <p>Role: <strong>{{ $user->role->name ?? 'N/A' }}</strong></p>
                                <p>Joined on {{ $user->created_at->format('Y-m-d') }}</p>
                            </div>
                            <div>
                                <a href="mailto:{{ $user->email }}" class="btn btn-primary"><i class="bi bi-envelope me-2"></i>Send Email</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                @if($user->description != '')
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-2">About</h4>
                            <p class="mb-0 text-muted">{{ $user->description }}</p>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Contact</h5>
                        <p class="mb-1"><i class="bi bi-geo-alt-fill me-2"></i>Location Unknown</p>
                        <p class="mb-0"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Activity</h5>
                        <p class="mb-1">Last login: <span class="text-muted">N/A</span></p>
                        <p class="mb-0">Status: <span class="text-success">Active</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
