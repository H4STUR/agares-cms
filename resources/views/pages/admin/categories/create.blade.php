{{-- resources/views/pages/admin/categories/create.blade.php --}}
<x-app-layout>

<div class="card mb-4">
  <div class="card-body">

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
      <div>
        <h5 class="mb-1">{{ __('Add Category') }}</h5>
        <div class="text-muted small">
          {{ __('Add a new category to') }} <strong>{{ $site->name }}</strong>
        </div>
      </div>

      <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> {{ __('Back to site') }}
      </a>
    </div>

    {{-- <x-notification /> --}}

    <form action="{{ route('admin.categories.store', $site->id) }}" method="POST" class="mt-3">
      @csrf

      <div class="mb-3">
        <label class="form-label" for="name">{{ __('Category name') }}</label>
        <input
          type="text"
          name="name"
          id="name"
          value="{{ old('name') }}"
          class="form-control @error('name') is-invalid @enderror"
          required
          autofocus
        >
        @error('name')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check2-circle me-1"></i> {{ __('Save Category') }}
        </button>

        <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-outline-secondary">
          {{ __('Cancel') }}
        </a>
      </div>
    </form>

  </div>
</div>

</x-app-layout>
