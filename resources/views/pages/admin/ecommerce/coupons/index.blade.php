<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Coupons</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.coupons.create') }}">Add coupon</a>
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
              <th>Code</th>
              <th>Type</th>
              <th>Value</th>
              <th>Uses</th>
              <th>Expires</th>
              <th>Enabled</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($coupons as $c)
              <tr>
                <td class="fw-semibold">{{ $c->code }}</td>
                <td>{{ str_replace('_', ' ', $c->type) }}</td>
                <td>
                  @if($c->type === 'percent') {{ $c->value }}%
                  @elseif($c->type === 'fixed') {{ $c->value }}
                  @else —
                  @endif
                </td>
                <td>{{ $c->redemptions_count }}{{ $c->max_uses ? ' / '.$c->max_uses : '' }}</td>
                <td>{{ $c->ends_at ? $c->ends_at->format('Y-m-d') : '—' }}</td>
                <td>
                  <div x-data="{ enabled: {{ $c->enabled ? 'true' : 'false' }} }">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch"
                             x-model="enabled"
                             @change="fetch('{{ route('admin.ecommerce.coupons.toggle', $c) }}', {
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
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.coupons.edit', $c) }}">Edit</a>
                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.coupons.destroy', $c) }}"
                        onsubmit="return confirm('Delete coupon?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted py-4">No coupons yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $coupons->links() }}
    </div>
  </div>
</x-app-layout>
