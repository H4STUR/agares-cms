<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ __('Orders') }}</h4>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Order') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Total') }}</th>
              <th>{{ __('Placed') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orders as $o)
              <tr>
                <td class="fw-semibold">{{ $o->order_number }}</td>
                <td><span class="badge text-bg-secondary">{{ $o->status }}</span></td>
                <td>{{ $o->grand_total }} {{ $o->currency }}</td>
                <td>{{ optional($o->placed_at)->format('Y-m-d H:i') }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.orders.show', $o) }}">{{ __('View') }}</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No orders yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $orders->links() }}
    </div>
  </div>
</x-app-layout>
