<x-app-layout>
  <h4 class="mb-3">Edit category: {{ $category->name }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.categories.update', $category) }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug', $category->slug) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Parent category</label>
            <select class="form-select" name="parent_id">
              <option value="">— None —</option>
              @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Sort order</label>
            <input class="form-control" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Save</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.categories.index') }}">Back</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
