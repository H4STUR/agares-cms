<x-app-layout>
  <h4 class="mb-3">Create shipping method</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.shipping-methods.store') }}">
        @csrf

        @if($errors->any())
          <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control @error('name') is-invalid @enderror" name="name"
                   value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Pricing type</label>
            <select class="form-select" name="pricing_type">
              @foreach(['flat','weight','price'] as $t)
                <option value="{{ $t }}" @selected(old('pricing_type', 'flat') === $t)>{{ ucfirst($t) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Price</label>
            <input class="form-control @error('price') is-invalid @enderror" name="price" type="number"
                   step="0.01" min="0" value="{{ old('price') }}">
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Enabled</label>
            <select class="form-select" name="enabled">
              <option value="1" @selected(old('enabled', 1) == 1)>Yes</option>
              <option value="0" @selected(old('enabled', 1) == 0)>No</option>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Create</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.shipping-methods.index') }}">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
