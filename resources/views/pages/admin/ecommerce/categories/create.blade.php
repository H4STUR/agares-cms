<x-app-layout>
  <h4 class="mb-3">Create category</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.categories.store') }}">
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

          <div class="col-md-6">
            <label class="form-label">Parent category</label>
            <select class="form-select" name="parent_id">
              <option value="">— None —</option>
              @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id') == $p->id)>{{ $p->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Sort order</label>
            <input class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Create</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.categories.index') }}">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
