<x-app-layout>
  <h4 class="mb-3">Create tag</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.tags.store') }}">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name') }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug') }}">
            <div class="form-text">Leave empty to auto-generate.</div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Create</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.tags.index') }}">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
