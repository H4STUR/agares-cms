<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Shipping methods</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.shipping-methods.create') }}">Add shipping method</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Name</th>
              <th>Pricing type</th>
              <th>Price</th>
              <th>Enabled</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($methods as $m)
              <tr>
                <td class="fw-semibold">{{ $m->name }}</td>
                <td>{{ ucfirst($m->pricing_type) }}</td>
                <td>{{ $m->price !== null ? number_format($m->price, 2) : '-' }}</td>
                <td>
                  <div x-data="{ enabled: {{ $m->enabled ? 'true' : 'false' }} }">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch"
                             x-model="enabled"
                             @change="fetch('{{ route('admin.ecommerce.shipping-methods.toggle', $m) }}', {
                               method: 'PATCH',
                               headers: {
                                 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                 'Content-Type': 'application/json'
                               },
                               body: JSON.stringify({ enabled: enabled })
                             })">
                    </div>
                  </div>
                </td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.shipping-methods.edit', $m) }}">Edit</a>
                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.shipping-methods.destroy', $m) }}"
                        onsubmit="return confirm('Delete shipping method?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No shipping methods yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $methods->links() }}
    </div>
  </div>
</x-app-layout>
