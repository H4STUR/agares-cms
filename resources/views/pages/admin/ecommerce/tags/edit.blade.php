<x-app-layout>
  <h4 class="mb-3">Edit tag: {{ $tag->name }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.tags.update', $tag) }}">
        @csrf
        @method('PATCH')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $tag->name) }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input class="form-control" name="slug" value="{{ old('slug', $tag->slug) }}">
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Save</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.tags.index') }}">Back</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
