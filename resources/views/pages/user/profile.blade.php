@extends('layouts.user')

@section('user-content')

    @if($user->description)
        <div class="card mb-3">
            <div class="card-body">
                <p class="text-uppercase fw-bold text-body-secondary mb-2" style="font-size:.75rem;letter-spacing:.06em;">About</p>
                <p class="mb-0 lh-base">{{ $user->description }}</p>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-uppercase fw-bold text-body-secondary mb-3" style="font-size:.75rem;letter-spacing:.06em;">Contact</p>
                    <div class="d-flex align-items-center gap-2 text-body" style="font-size:.875rem;">
                        <i class="material-icons-outlined text-body-secondary" style="font-size:1rem;">email</i>
                        @auth
                            @if(auth()->id() === $user->id || auth()->user()->can('view admin panel'))
                                <span>{{ $user->email }}</span>
                            @else
                                <span class="text-body-secondary">Hidden</span>
                            @endif
                        @else
                            <span class="text-body-secondary">Hidden</span>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-uppercase fw-bold text-body-secondary mb-3" style="font-size:.75rem;letter-spacing:.06em;">Activity</p>
                    <div class="d-flex flex-column gap-2" style="font-size:.875rem;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="material-icons-outlined text-body-secondary" style="font-size:1rem;">calendar_today</i>
                            <span>Joined {{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="material-icons-outlined text-success" style="font-size:.9rem;">circle</i>
                            <span>Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
