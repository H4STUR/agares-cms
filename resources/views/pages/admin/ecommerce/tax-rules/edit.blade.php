<x-app-layout>
  <h4 class="mb-3">Edit tax rule</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.tax-rules.update', $rule) }}">
        @csrf
        @method('PATCH')

        @if($errors->any())
          <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Country (2-letter code)</label>
            <input class="form-control @error('country') is-invalid @enderror" name="country"
                   value="{{ old('country', $rule->country) }}" placeholder="PL" maxlength="2">
            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Region</label>
            <input class="form-control @error('region') is-invalid @enderror" name="region"
                   value="{{ old('region', $rule->region) }}" placeholder="Optional">
            @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-2">
            <label class="form-label">Rate (%)</label>
            <input class="form-control @error('rate') is-invalid @enderror" name="rate" type="number"
                   step="0.01" min="0" max="100" value="{{ old('rate', $rule->rate) }}" required>
            @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-2">
            <label class="form-label">Priority</label>
            <input class="form-control @error('priority') is-invalid @enderror" name="priority" type="number"
                   min="0" value="{{ old('priority', $rule->priority) }}">
            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-2">
            <label class="form-label">Prices include tax</label>
            <select class="form-select" name="prices_include_tax">
              <option value="1" @selected(old('prices_include_tax', $rule->prices_include_tax) == 1)>Yes</option>
              <option value="0" @selected(old('prices_include_tax', $rule->prices_include_tax) == 0)>No</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Enabled</label>
            <select class="form-select" name="enabled">
              <option value="1" @selected(old('enabled', $rule->enabled) == 1)>Yes</option>
              <option value="0" @selected(old('enabled', $rule->enabled) == 0)>No</option>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">Save</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.tax-rules.index') }}">Back</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
