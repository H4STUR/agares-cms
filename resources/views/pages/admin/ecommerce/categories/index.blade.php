<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Categories</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.categories.create') }}">Add category</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Parent</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($categories as $c)
              <tr>
                <td class="fw-semibold">{{ $c->name }}</td>
                <td class="text-muted">{{ $c->slug }}</td>
                <td>{{ $c->parent?->name ?? '-' }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.categories.edit', $c) }}">Edit</a>

                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.categories.destroy', $c) }}"
                        onsubmit="return confirm('Delete category?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
