<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">{{ __('Configure') }}: <span class="text-capitalize">{{ $paymentProvider->driver }}</span></h4>
      <div class="text-muted small">{{ __('Update API credentials and gateway settings.') }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.payment-providers.index') }}">{{ __('Back') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.payment-providers.update', $paymentProvider) }}">
        @csrf
        @method('PATCH')

        @if($errors->any())
          <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        {{-- Enabled toggle --}}
        <div class="mb-4">
          <label class="form-label fw-semibold">{{ __('Status') }}</label>
          <select class="form-select w-auto" name="enabled">
            <option value="1" @selected(old('enabled', $paymentProvider->enabled) == '1')>{{ __('Enabled') }}</option>
            <option value="0" @selected(old('enabled', $paymentProvider->enabled) == '0')>{{ __('Disabled') }}</option>
          </select>
        </div>

        {{-- Stripe --}}
        @if($paymentProvider->driver === 'stripe')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('Publishable Key') }}</label>
              <input class="form-control font-monospace" name="config[publishable_key]"
                     value="{{ old('config.publishable_key', $paymentProvider->config['publishable_key'] ?? '') }}"
                     placeholder="pk_test_...">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Secret Key') }}</label>
              <input class="form-control font-monospace" name="config[secret_key]" type="password" autocomplete="off"
                     value="{{ old('config.secret_key', $paymentProvider->config['secret_key'] ?? '') }}"
                     placeholder="sk_test_...">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Webhook Secret') }}</label>
              <input class="form-control font-monospace" name="config[webhook_secret]" type="password" autocomplete="off"
                     value="{{ old('config.webhook_secret', $paymentProvider->config['webhook_secret'] ?? '') }}"
                     placeholder="whsec_...">
            </div>
            <div class="col-md-3">
              <label class="form-label">{{ __('Mode') }}</label>
              <select class="form-select" name="config[mode]">
                <option value="test" @selected(old('config.mode', $paymentProvider->config['mode'] ?? 'test') === 'test')>{{ __('Test') }}</option>
                <option value="live" @selected(old('config.mode', $paymentProvider->config['mode'] ?? 'test') === 'live')>{{ __('Live') }}</option>
              </select>
            </div>
          </div>
        @endif

        {{-- PayU --}}
        @if($paymentProvider->driver === 'payu')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('POS ID') }}</label>
              <input class="form-control font-monospace" name="config[pos_id]"
                     value="{{ old('config.pos_id', $paymentProvider->config['pos_id'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Client ID') }}</label>
              <input class="form-control font-monospace" name="config[client_id]"
                     value="{{ old('config.client_id', $paymentProvider->config['client_id'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Client Secret') }}</label>
              <input class="form-control font-monospace" name="config[client_secret]" type="password" autocomplete="off"
                     value="{{ old('config.client_secret', $paymentProvider->config['client_secret'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('MD5 Key') }}</label>
              <input class="form-control font-monospace" name="config[md5_key]" type="password" autocomplete="off"
                     value="{{ old('config.md5_key', $paymentProvider->config['md5_key'] ?? '') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">{{ __('Environment') }}</label>
              <select class="form-select" name="config[sandbox]">
                <option value="1" @selected(old('config.sandbox', $paymentProvider->config['sandbox'] ?? '1') == '1')>{{ __('Sandbox') }}</option>
                <option value="0" @selected(old('config.sandbox', $paymentProvider->config['sandbox'] ?? '1') == '0')>{{ __('Production') }}</option>
              </select>
            </div>
          </div>
        @endif

        {{-- P24 --}}
        @if($paymentProvider->driver === 'p24')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('Merchant ID') }}</label>
              <input class="form-control font-monospace" name="config[merchant_id]"
                     value="{{ old('config.merchant_id', $paymentProvider->config['merchant_id'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('POS ID') }}</label>
              <input class="form-control font-monospace" name="config[pos_id]"
                     value="{{ old('config.pos_id', $paymentProvider->config['pos_id'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('CRC Key') }}</label>
              <input class="form-control font-monospace" name="config[crc_key]" type="password" autocomplete="off"
                     value="{{ old('config.crc_key', $paymentProvider->config['crc_key'] ?? '') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">{{ __('Environment') }}</label>
              <select class="form-select" name="config[sandbox]">
                <option value="1" @selected(old('config.sandbox', $paymentProvider->config['sandbox'] ?? '1') == '1')>{{ __('Sandbox') }}</option>
                <option value="0" @selected(old('config.sandbox', $paymentProvider->config['sandbox'] ?? '1') == '0')>{{ __('Production') }}</option>
              </select>
            </div>
          </div>
        @endif

        {{-- PayPal --}}
        @if($paymentProvider->driver === 'paypal')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('Client ID') }}</label>
              <input class="form-control font-monospace" name="config[client_id]"
                     value="{{ old('config.client_id', $paymentProvider->config['client_id'] ?? '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Client Secret') }}</label>
              <input class="form-control font-monospace" name="config[client_secret]" type="password" autocomplete="off"
                     value="{{ old('config.client_secret', $paymentProvider->config['client_secret'] ?? '') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">{{ __('Mode') }}</label>
              <select class="form-select" name="config[mode]">
                <option value="sandbox" @selected(old('config.mode', $paymentProvider->config['mode'] ?? 'sandbox') === 'sandbox')>{{ __('Sandbox') }}</option>
                <option value="live"    @selected(old('config.mode', $paymentProvider->config['mode'] ?? 'sandbox') === 'live')>{{ __('Live') }}</option>
              </select>
            </div>
          </div>
        @endif

        {{-- COD --}}
        @if($paymentProvider->driver === 'cod')
          <p class="text-muted">{{ __('Cash on Delivery requires no configuration. Just enable or disable it above.') }}</p>
        @endif

        <div class="d-flex gap-2 mt-4">
          <button class="btn btn-primary">{{ __('Save') }}</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.payment-providers.index') }}">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
