<x-app-layout>
  <h4 class="mb-3">{{ __('Edit list') }}: {{ $list->name }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.newsletter.lists.update', $list) }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $list->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Slug') }}</label>
            <input class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $list->slug) }}">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">{{ __('Description') }}</label>
            <textarea class="form-control" name="description" rows="3">{{ old('description', $list->description) }}</textarea>
          </div>

          <div class="col-12">
            <div class="form-check">
              <input type="hidden" name="is_default" value="0">
              <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1"
                     @checked(old('is_default', $list->is_default))>
              <label class="form-check-label" for="is_default">{{ __('Default list (auto-assigned to public signups)') }}</label>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">{{ __('Save') }}</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.lists.index') }}">{{ __('Back') }}</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
