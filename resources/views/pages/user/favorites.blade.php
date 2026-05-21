@extends('layouts.user')

@section('user-content')

    <div class="card">
        <div class="card-body text-center py-5">
            <i class="material-icons-outlined text-body-secondary mb-3" style="font-size:3rem;display:block;">favorite</i>
            <p class="fw-semibold mb-1">No favorites yet</p>
            <p class="text-body-secondary small mb-4">Products you save will appear here.</p>
            @if(($settings['enable_ecommerce'] ?? '0') == '1')
                <a href="{{ route('shop.home') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="material-icons-outlined" style="font-size:16px;">storefront</i> Browse Shop
                </a>
            @endif
        </div>
    </div>

@endsection
