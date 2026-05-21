<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Tags</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.tags.create') }}">Add tag</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Products</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tags as $t)
              <tr>
                <td class="fw-semibold">{{ $t->name }}</td>
                <td class="text-muted font-monospace small">{{ $t->slug }}</td>
                <td><span class="badge text-bg-secondary">{{ $t->products_count }}</span></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.tags.edit', $t) }}">Edit</a>
                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.tags.destroy', $t) }}"
                        onsubmit="return confirm('Delete tag?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-4">No tags yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($tags->hasPages())
    <div class="mt-3">{{ $tags->links() }}</div>
  @endif
</x-app-layout>
