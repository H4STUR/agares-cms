<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('User Settings') }}</div>
    </div>

    <div class="container-fluid">

        <div class="row g-4">
            <!-- Update Profile Info -->
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3 text-primary">{{ __('Profile Information') }}</h5>
                        <form method="POST" action="{{ route('admin.user.settings.update', $user->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
            
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('Name') }}</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
            
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
            
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Profile Description') }}</label>
                                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $user->description) }}</textarea>
                            </div>
            
                            <div class="mt-4" style="margin-bottom: 50px;">

                                <div class="position-relative text-center">

                                    <div class="ratio" style="--bs-aspect-ratio: calc(5 / 18 * 100%)">
                                        <img src="{{ $user->background_image_url }}" class="img-fluid rounded object-fit-cover w-100 h-100" alt="">
                                    </div>

                                    <div class="position-absolute top-100 start-50 translate-middle">
                                        <img src="{{ $user->avatar_url }}" width="110" height="110" class="rounded-circle raised p-1 bg-white object-fit-cover" alt="{{ __('User avatar') }}">
                                    </div>

                                </div>


                            </div>

                            <div class="mb-3">
                                <label for="avatar" class="form-label">{{ __('Avatar') }}</label>
                                <input type="file" name="avatar" id="avatar" class="form-control">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_avatar" id="remove_avatar" value="1">
                                    <label class="form-check-label" for="remove_avatar">{{ __('Remove current avatar') }}</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="background_image" class="form-label">{{ __('Background Image') }}</label>
                                <input type="file" name="background_image" id="background_image" class="form-control">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_background" id="remove_background" value="1">
                                    <label class="form-check-label" for="remove_background">{{ __('Remove current background image') }}</label>
                                </div>
                            </div>
                            
            
                            <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
            

            <!-- Change Password -->
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3 text-warning">{{ __('Change Password') }}</h5>
                        <form method="POST" action="{{ route('admin.user.settings.password', $user->id) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('New Password') }}</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>

                            <x-primary-button type="submit">{{ __('Update Password') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="col-12">
                <div class="card border border-danger">
                    <div class="card-body">
                        <h5 class="mb-3 text-danger">{{ __('Delete Account') }}</h5>
                        <p class="text-muted">{{ __('Once deleted, your account and all associated data will be permanently removed.') }}</p>

                        <form method="POST" action="{{ route('admin.user.delete', $user->id) }}">
                            @csrf
                            @method('DELETE')

                            <div class="mb-3">
                                <label for="delete_password" class="form-label">{{ __('Confirm Your Password') }}</label>
                                <input type="password" id="delete_password" name="password" class="form-control" required>
                            </div>

                            <x-danger-button type="submit">{{ __('Delete Account') }}</x-danger-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
