<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Attributes</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.attributes.create') }}">Add attribute</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Type</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attributes as $a)
              <tr>
                <td class="fw-semibold">{{ $a->name }}</td>
                <td class="text-muted">{{ $a->slug }}</td>
                <td>{{ $a->type }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.ecommerce.attributes.values.index', $a) }}">Values</a>
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.attributes.edit', $a) }}">Edit</a>
                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.attributes.destroy', $a) }}"
                        onsubmit="return confirm('Delete attribute?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">No attributes yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
