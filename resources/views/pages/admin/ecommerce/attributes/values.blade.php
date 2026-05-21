<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Values: {{ $attribute->name }}</h4>
      <div class="text-muted small">{{ $attribute->slug }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.attributes.index') }}">Back</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.attributes.values.store', $attribute) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-4">
            <input class="form-control" name="value" placeholder="Value (e.g. Red)" required>
          </div>
          <div class="col-md-4">
            <input class="form-control" name="slug" placeholder="Slug (optional)">
          </div>
          <div class="col-md-2">
            <input class="form-control" name="sort_order" placeholder="Sort" value="0">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100">Add</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Value</th>
              <th>Slug</th>
              <th>Sort</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($values as $v)
              <tr>
                <td class="fw-semibold">{{ $v->value }}</td>
                <td class="text-muted">{{ $v->slug }}</td>
                <td>{{ $v->sort_order }}</td>
                <td class="text-end">
                  <form method="POST" action="{{ route('admin.ecommerce.attributes.values.destroy', [$attribute, $v]) }}"
                        onsubmit="return confirm('Delete value?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">No values yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
