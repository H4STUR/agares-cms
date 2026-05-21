<x-app-layout>
  <h4 class="mb-3">Edit coupon: {{ $coupon->code }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.coupons.update', $coupon) }}"
            x-data="{ type: '{{ old('type', $coupon->type) }}', generateCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                this.$refs.code.value = Array.from({length: 8}, () => chars[Math.floor(Math.random() * chars.length)]).join('');
            }}">
        @csrf
        @method('PATCH')

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <div class="input-group">
              <input class="form-control @error('code') is-invalid @enderror" name="code" x-ref="code"
                     value="{{ old('code', $coupon->code) }}" required>
              <button type="button" class="btn btn-outline-secondary" @click="generateCode()" title="Generate random code">
                <i class="material-icons-outlined" style="font-size:18px;vertical-align:middle">casino</i>
              </button>
              @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select class="form-select @error('type') is-invalid @enderror" name="type" x-model="type">
              <option value="percent">Percent (%)</option>
              <option value="fixed">Fixed amount</option>
              <option value="free_shipping">Free shipping</option>
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4" x-show="type !== 'free_shipping'" x-cloak>
            <label class="form-label">Value</label>
            <input class="form-control @error('value') is-invalid @enderror" name="value"
                   type="number" step="0.01" min="0" value="{{ old('value', $coupon->value) }}">
            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Min order value</label>
            <input class="form-control @error('min_order_value') is-invalid @enderror" name="min_order_value"
                   type="number" step="0.01" min="0" value="{{ old('min_order_value', $coupon->min_order_value) }}">
            @error('min_order_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Max uses (total)</label>
            <input class="form-control @error('max_uses') is-invalid @enderror" name="max_uses"
                   type="number" min="1" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Unlimited">
            @error('max_uses')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Max uses per customer</label>
            <input class="form-control @error('max_uses_per_customer') is-invalid @enderror" name="max_uses_per_customer"
                   type="number" min="1" value="{{ old('max_uses_per_customer', $coupon->max_uses_per_customer) }}" placeholder="Unlimited">
            @error('max_uses_per_customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Starts at</label>
            <input class="form-control @error('starts_at') is-invalid @enderror" name="starts_at"
                   type="datetime-local"
                   value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
            @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Expires at</label>
            <input class="form-control @error('ends_at') is-invalid @enderror" name="ends_at"
                   type="datetime-local"
                   value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}">
            @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Enabled</label>
            <select class="form-select" name="enabled">
              <option value="1" @selected(old('enabled', $coupon->enabled) == 1)>Yes</option>
              <option value="0" @selected(old('enabled', $coupon->enabled) == 0)>No</option>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Save</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.coupons.index') }}">Back</a>
        </div>
      </form>

      @if($coupon->redemptions_count > 0)
        <hr>
        <p class="text-muted mb-0">This coupon has been redeemed {{ $coupon->redemptions_count }} time(s).</p>
      @endif
    </div>
  </div>
</x-app-layout>
