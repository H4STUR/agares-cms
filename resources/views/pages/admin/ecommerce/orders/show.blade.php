@php
  $statusColors = [
      'pending_payment' => 'warning',
      'processing'      => 'primary',
      'on_hold'         => 'secondary',
      'completed'       => 'success',
      'cancelled'       => 'danger',
      'refunded'        => 'info',
      'failed'          => 'dark',
  ];
  $paymentColors = [
      'unpaid'             => 'warning',
      'paid'               => 'success',
      'partially_refunded' => 'info',
      'refunded'           => 'secondary',
  ];
  $paymentStatusColors = [
      'pending'    => 'secondary',
      'authorized' => 'info',
      'captured'   => 'success',
      'failed'     => 'danger',
      'refunded'   => 'warning',
      'cancelled'  => 'dark',
  ];
@endphp

<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">{{ __('Order') }}: {{ $order->order_number }}</h4>
      <div class="text-muted small">
        {{ optional($order->placed_at)->format('Y-m-d H:i') }}
        @if($order->user_id)
          &mdash; {{ $order->user->name ?? __('User #').$order->user_id }}
        @else
          &mdash; {{ __('Guest') }}
        @endif
      </div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.orders.index') }}">{{ __('Back') }}</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row g-3">

    {{-- Left column: items + payments --}}
    <div class="col-lg-8">

      {{-- Order items --}}
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-3">{{ __('Items') }}</h6>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>{{ __('Name') }}</th>
                  <th class="text-end">{{ __('Unit price') }}</th>
                  <th class="text-end">{{ __('Qty') }}</th>
                  <th class="text-end">{{ __('Total') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($order->items as $item)
                  <tr>
                    <td>
                      <div class="fw-semibold">{{ $item->name }}</div>
                      @if($item->sku)
                        <div class="small text-muted">{{ $item->sku }}</div>
                      @endif
                    </td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }} {{ $order->currency }}</td>
                    <td class="text-end">{{ $item->qty }}</td>
                    <td class="text-end">{{ number_format($item->total, 2) }} {{ $order->currency }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-3 ms-auto" style="max-width:260px">
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">{{ __('Subtotal') }}</span>
              <span>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">{{ __('Tax') }}</span>
              <span>{{ number_format($order->tax_total, 2) }} {{ $order->currency }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">{{ __('Shipping') }}</span>
              <span>{{ number_format($order->shipping_total, 2) }} {{ $order->currency }}</span>
            </div>
            @if($order->discount_total > 0)
              <div class="d-flex justify-content-between py-1 text-success">
                <span>{{ __('Discount') }}</span>
                <span>-{{ number_format($order->discount_total, 2) }} {{ $order->currency }}</span>
              </div>
            @endif
            <hr class="my-1">
            <div class="d-flex justify-content-between py-1 fw-bold">
              <span>{{ __('Total') }}</span>
              <span>{{ number_format($order->grand_total, 2) }} {{ $order->currency }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Payments --}}
      @if($order->payments->count())
        <div class="card mb-3">
          <div class="card-body">
            <h6 class="mb-3">{{ __('Payments') }}</h6>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Provider') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th>{{ __('Ref') }}</th>
                    <th>{{ __('Date') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($order->payments as $payment)
                    <tr>
                      <td class="text-capitalize">{{ optional($payment->provider)->driver ?? '—' }}</td>
                      <td>
                        <span class="badge text-bg-{{ $paymentStatusColors[$payment->status] ?? 'secondary' }}">
                          {{ $payment->status }}
                        </span>
                      </td>
                      <td class="text-end">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                      <td class="small text-muted font-monospace">{{ $payment->provider_payment_id ?? '—' }}</td>
                      <td class="small text-muted">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif

      {{-- Status history timeline --}}
      @if($order->statusHistory->count())
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3">{{ __('Status history') }}</h6>
            <div class="position-relative ps-4">
              @foreach($order->statusHistory as $entry)
                <div class="mb-3 position-relative">
                  <span class="position-absolute start-0 translate-middle-x mt-1"
                        style="width:10px;height:10px;border-radius:50%;background:var(--bs-primary);display:inline-block;left:-4px"></span>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($entry->from_status)
                      <span class="badge text-bg-{{ $statusColors[$entry->from_status] ?? 'secondary' }}">
                        {{ $entry->from_status }}
                      </span>
                      <span class="text-muted">&rarr;</span>
                    @endif
                    <span class="badge text-bg-{{ $statusColors[$entry->to_status] ?? 'secondary' }}">
                      {{ $entry->to_status }}
                    </span>
                    <span class="text-muted small ms-auto">{{ $entry->created_at->format('Y-m-d H:i') }}</span>
                  </div>
                  @if($entry->comment)
                    <div class="small text-muted mt-1">{{ $entry->comment }}</div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Right column: status + addresses + update form --}}
    <div class="col-lg-4">

      {{-- Current status panel --}}
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">{{ __('Status') }}</h6>
          <div class="mb-2">
            <span class="badge text-bg-{{ $statusColors[$order->status] ?? 'secondary' }} me-1">
              {{ $order->status }}
            </span>
          </div>
          <div class="small mb-1">
            <span class="text-muted">{{ __('Payment:') }}</span>
            <span class="badge text-bg-{{ $paymentColors[$order->payment_status] ?? 'secondary' }}">
              {{ $order->payment_status }}
            </span>
          </div>
          <div class="small">
            <span class="text-muted">{{ __('Fulfillment:') }}</span>
            <span class="badge text-bg-secondary">{{ $order->fulfillment_status }}</span>
          </div>
        </div>
      </div>

      {{-- Update status form --}}
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-3">{{ __('Update status') }}</h6>
          <form method="POST" action="{{ route('admin.ecommerce.orders.updateStatus', $order) }}">
            @csrf
            @method('PATCH')

            @if($errors->any())
              <div class="alert alert-danger mb-3 py-2 small">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
              </div>
            @endif

            <div class="mb-3">
              <label class="form-label small">{{ __('New status') }}</label>
              <select class="form-select form-select-sm" name="status" required>
                @foreach(['pending_payment','processing','on_hold','completed','cancelled','refunded','failed'] as $s)
                  <option value="{{ $s }}" @selected(old('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small">{{ __('Comment') }} <span class="text-muted">({{ __('optional') }})</span></label>
              <textarea class="form-control form-control-sm" name="comment" rows="3"
                        placeholder="{{ __('Reason for change...') }}">{{ old('comment') }}</textarea>
            </div>

            <button class="btn btn-sm btn-primary w-100">{{ __('Update') }}</button>
          </form>
        </div>
      </div>

      {{-- Billing address --}}
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">{{ __('Billing address') }}</h6>
          @php $billing = $order->billing_address ?? []; @endphp
          @if($billing)
            <address class="mb-0 small">
              @if(!empty($billing['name']))      <div class="fw-semibold">{{ $billing['name'] }}</div>@endif
              @if(!empty($billing['company']))   <div>{{ $billing['company'] }}</div>@endif
              @if(!empty($billing['address1']))  <div>{{ $billing['address1'] }}</div>@endif
              @if(!empty($billing['address2']))  <div>{{ $billing['address2'] }}</div>@endif
              @if(!empty($billing['city']))
                <div>{{ $billing['postcode'] ?? '' }} {{ $billing['city'] }}</div>
              @endif
              @if(!empty($billing['country']))   <div>{{ $billing['country'] }}</div>@endif
              @if(!empty($billing['phone']))     <div class="text-muted mt-1">{{ $billing['phone'] }}</div>@endif
              @if(!empty($billing['email']))     <div class="text-muted">{{ $billing['email'] }}</div>@endif
            </address>
          @else
            <span class="text-muted small">—</span>
          @endif
        </div>
      </div>

      {{-- Shipping address --}}
      @if($order->shipping_address)
        <div class="card">
          <div class="card-body">
            <h6 class="mb-2">{{ __('Shipping address') }}</h6>
            @php $shipping = $order->shipping_address; @endphp
            <address class="mb-0 small">
              @if(!empty($shipping['name']))     <div class="fw-semibold">{{ $shipping['name'] }}</div>@endif
              @if(!empty($shipping['company']))  <div>{{ $shipping['company'] }}</div>@endif
              @if(!empty($shipping['address1'])) <div>{{ $shipping['address1'] }}</div>@endif
              @if(!empty($shipping['address2'])) <div>{{ $shipping['address2'] }}</div>@endif
              @if(!empty($shipping['city']))
                <div>{{ $shipping['postcode'] ?? '' }} {{ $shipping['city'] }}</div>
              @endif
              @if(!empty($shipping['country']))  <div>{{ $shipping['country'] }}</div>@endif
            </address>
          </div>
        </div>
      @endif
    </div>

  </div>
</x-app-layout>
