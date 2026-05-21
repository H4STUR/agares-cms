@extends('layouts.user')

@section('user-content')

    {{-- Profile Information --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Profile Information</h5>

            <form method="POST" action="{{ route('admin.user.settings.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                {{-- Avatar --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $user->avatar_url }}" alt="Avatar"
                         class="rounded-circle border" style="width:72px;height:72px;object-fit:cover;">
                    <div>
                        <label for="avatar" class="form-label fw-semibold small text-body-secondary text-uppercase mb-1" style="letter-spacing:.04em;">Change avatar</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="form-control form-control-sm">
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="remove_avatar" value="1" id="remove_avatar">
                            <label class="form-check-label small text-body-secondary" for="remove_avatar">Remove current avatar</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Full name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Bio</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $user->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Background image</label>
                    @if($user->background_image)
                        <div class="mb-2">
                            <img src="{{ $user->background_image_url }}" alt="Background"
                                 class="rounded border w-100" style="max-height:100px;object-fit:cover;">
                        </div>
                    @endif
                    <input type="file" name="background_image" accept="image/*" class="form-control">
                    @if($user->background_image)
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="remove_background" value="1" id="remove_background">
                            <label class="form-check-label small text-body-secondary" for="remove_background">Remove background image</label>
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="material-icons-outlined" style="font-size:16px;">check</i> Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Change Password</h5>

            <form method="POST" action="{{ route('admin.user.settings.password', $user->id) }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Current password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">New password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="material-icons-outlined" style="font-size:16px;">lock</i> Update Password
                </button>
            </form>
        </div>
    </div>

    {{-- Delete Account --}}
    <div class="card border-danger">
        <div class="card-body">
            <h5 class="card-title text-danger mb-2">Delete Account</h5>
            <p class="text-body-secondary small mb-4">
                Once deleted, your account and all associated data will be permanently removed. This cannot be undone.
            </p>

            <form method="POST" action="{{ route('admin.user.delete', $user->id) }}">
                @csrf
                @method('DELETE')

                <div class="mb-3">
                    <label for="delete_password" class="form-label fw-semibold small text-body-secondary text-uppercase" style="letter-spacing:.04em;">Confirm your password to continue</label>
                    <input type="password" id="delete_password" name="password" class="form-control border-danger" required>
                </div>

                <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2"
                        onclick="return confirm('Are you sure? This cannot be undone.')">
                    <i class="material-icons-outlined" style="font-size:16px;">delete</i> Delete Account
                </button>
            </form>
        </div>
    </div>

@endsection
