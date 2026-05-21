<x-app-layout>

@push('styles')
<style>
  .form-check-input:not(:checked) + .badge { background: var(--bs-light); color: var(--bs-body-color); border-color: var(--bs-border-color) !important; }
  .form-check-input:checked + .badge { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary) !important; }
  .cursor-pointer { cursor: pointer; }
</style>
@endpush

  <h4 class="mb-3">{{ __('Create product') }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.products.store') }}">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Slug') }}</label>
            <input name="slug" class="form-control" value="{{ old('slug') }}">
            <div class="form-text">{{ __('Leave empty to auto-generate.') }}</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">{{ __('Status') }}</label>
            <select name="status" class="form-select">
              @foreach(['draft','published','archived'] as $s)
                <option value="{{ $s }}" @selected(old('status','draft')===$s)>{{ $s }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">{{ __('Type') }}</label>
            <select name="product_type" class="form-select">
              @foreach(['simple','variable','digital','service'] as $t)
                <option value="{{ $t }}" @selected(old('product_type','simple')===$t)>{{ $t }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">{{ __('Base price') }}</label>
            <input name="base_price" class="form-control" value="{{ old('base_price') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">{{ __('SKU') }}</label>
            <input name="sku" class="form-control" value="{{ old('sku') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">{{ __('Stock') }}</label>
            <input
              name="stock"
              type="number"
              min="0"
              class="form-control"
              value="{{ old('stock') }}"
            >
            <div class="form-text">
              {{ __('Leave empty to disable stock tracking.') }}
            </div>
          </div>


          <div class="col-12">
            <label class="form-label">{{ __('Short description') }}</label>
            <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
          </div>

          <div class="col-12">
            <label class="form-label">{{ __('Description') }}</label>
            <textarea name="description" class="form-control" rows="6">{{ old('description') }}</textarea>
          </div>

          {{-- Categories --}}
          @if($allCategories->isNotEmpty())
          <div class="col-md-6">
            <label class="form-label">{{ __('Categories') }}</label>
            <div class="border rounded-3 p-3" style="max-height:200px; overflow-y:auto;">
              @foreach($allCategories as $cat)
                <div class="form-check mb-1">
                  <input class="form-check-input" type="checkbox" name="categories[]"
                         value="{{ $cat->id }}" id="cc_{{ $cat->id }}"
                         @checked(in_array($cat->id, old('categories', [])))>
                  <label class="form-check-label" for="cc_{{ $cat->id }}">{{ $cat->name }}</label>
                </div>
              @endforeach
            </div>
          </div>
          @endif

          {{-- Tags --}}
          @if($allTags->isNotEmpty())
          <div class="col-md-6">
            <label class="form-label">{{ __('Tags') }}</label>
            <div class="border rounded-3 p-3 d-flex flex-wrap gap-2" style="max-height:200px; overflow-y:auto;">
              @foreach($allTags as $tag)
                <div class="form-check m-0">
                  <input class="form-check-input visually-hidden" type="checkbox" name="tags[]"
                         value="{{ $tag->id }}" id="ct_{{ $tag->id }}"
                         @checked(in_array($tag->id, old('tags', [])))>
                  <label class="badge rounded-pill border cursor-pointer user-select-none px-3 py-2"
                         style="font-size:.8rem;font-weight:500;" for="ct_{{ $tag->id }}">
                    {{ $tag->name }}
                  </label>
                </div>
              @endforeach
            </div>
          </div>
          @endif
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">{{ __('Create') }}</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.ecommerce.products.index') }}">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
