<x-guest-layout>
  <div class="card my-5 col-xl-9 col-xxl-8 mx-auto rounded-4 overflow-hidden p-4">
    <div class="row g-4 align-items-center">

      {{-- Left: form --}}
      <div class="col-lg-6 d-flex">
        <div class="card-body">
          {{-- <img src="{{ asset('assets/admin/images/agares-logo.png') }}" class="mb-4" width="145" alt="Agares logo"> --}}

          <h4 class="fw-bold">{{ __('Generate New Password') }}</h4>
          <p class="mb-0">
            {{ __('We received your password reset request. Please enter your new password.') }}
          </p>

          <div class="form-body mt-4">
            <form method="POST" action="{{ route('password.store') }}" class="row g-3">
              @csrf

              {{-- Token --}}
              <input type="hidden" name="token" value="{{ $request->route('token') }}">

              {{-- Email (required by Laravel password broker) --}}
              <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

              {{-- New password --}}
              <div class="col-12">
                <label class="form-label" for="password">{{ __('New Password') }}</label>

                <div class="input-group" id="show_hide_password">
                  <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="{{ __('Enter new password') }}"
                  >
                  <a href="javascript:;" class="input-group-text bg-transparent" aria-label="{{ __('Show/Hide password') }}">
                    <i class="bi bi-eye-slash-fill"></i>
                  </a>
                </div>

                @error('password')
                  <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                @enderror
              </div>

              {{-- Confirm --}}
              <div class="col-12">
                <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>

                <input
                  type="password"
                  class="form-control @error('password_confirmation') is-invalid @enderror"
                  id="password_confirmation"
                  name="password_confirmation"
                  required
                  autocomplete="new-password"
                  placeholder="{{ __('Confirm password') }}"
                >

                @error('password_confirmation')
                  <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                @enderror
              </div>

              {{-- Actions --}}
              <div class="col-12">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">
                    {{ __('Change Password') }}
                  </button>

                  <a href="{{ route('login') }}" class="btn btn-light">
                    {{ __('Back to Login') }}
                  </a>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

      {{-- Right: image --}}
      <div class="col-lg-6 d-lg-flex d-none">
        <div class="p-3 rounded-4 w-100 d-flex align-items-center justify-content-center bg-light">
          <img src="{{ asset('assets/admin/theme/assets/images/auth/reset-password1.png') }}" class="img-fluid" alt="">
        </div>
      </div>

    </div>
  </div>
</x-guest-layout>
