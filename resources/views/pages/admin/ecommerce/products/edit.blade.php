<x-app-layout>
@php
  $activeTab = request('tab', 'general');
@endphp

<div class="card mb-4">
  <div class="card-body">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <div>
          <h4 class="mb-2">{{ __('Edit product') }}: {{ $product->name }}</h4>
        </div>

        <span class="badge
          @if($product->deleted_at) text-bg-danger
          @elseif($product->status === 'published') text-bg-success
          @elseif($product->status === 'archived') text-bg-warning
          @else text-bg-secondary
          @endif
        ">
          {{ $product->deleted_at ? __('Trashed') : ucfirst($product->status ?? 'draft') }}
        </span>

        <small class="text-muted">/{{ $product->slug }}</small>
      </div>

      <div class="d-flex align-items-center gap-2">
        {{-- Quick publish/draft toggle --}}
        @if(!$product->deleted_at)
          <form action="{{ route('admin.ecommerce.products.update', $product) }}" method="POST" class="d-flex align-items-center gap-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ $product->status === 'published' ? 'draft' : 'published' }}">

            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" role="switch" id="publishSwitch"
                    {{ $product->status === 'published' ? 'checked' : '' }}
                    onchange="this.form.submit()">
              <label class="form-check-label small" for="publishSwitch">
                {{ $product->status === 'published' ? __('Published') : __('Draft') }}
              </label>
            </div>
          </form>
        @endif

        <a href="{{ url('/shop/'.$product->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
        </a>

        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.ecommerce.products.index') }}">
          <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
        </a>
      </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-general"
          :active="$activeTab === 'general'"
          id="tab-button-general"
          icon="bi-box"
          data-tab="general"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=general"
        >
          {{ __('General') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-organisation"
          :active="$activeTab === 'organisation'"
          id="tab-button-organisation"
          icon="bi-tags"
          data-tab="organisation"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=organisation"
        >
          {{ __('Organisation') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-variants"
          :active="$activeTab === 'variants'"
          id="tab-button-variants"
          icon="bi-diagram-3"
          data-tab="variants"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=variants"
        >
          {{ __('Variants') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-pricing"
          :active="$activeTab === 'pricing'"
          id="tab-button-pricing"
          icon="bi-cash-coin"
          data-tab="pricing"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=pricing"
        >
          {{ __('Pricing') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-media"
          :active="$activeTab === 'media'"
          id="tab-button-media"
          icon="bi-images"
          data-tab="media"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=media"
        >
          {{ __('Media') }}
        </x-nav-link>
      </li>

      <li class="nav-item" role="presentation">
        <x-nav-link
          href="#tab-seo"
          :active="$activeTab === 'seo'"
          id="tab-button-seo"
          icon="bi-graph-up"
          data-tab="seo"
          data-url="{{ route('admin.ecommerce.products.edit', $product) }}?tab=seo"
        >
          {{ __('SEO') }}
        </x-nav-link>
      </li>
    </ul>

    {{-- Unified product form --}}
    <form method="POST" action="{{ route('admin.ecommerce.products.update', $product) }}" id="product-edit-form">
      @csrf
      @method('PATCH')

      <div class="tab-content">

        {{-- GENERAL --}}
        <div id="tab-general" class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('Name') }}</label>
              <input name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">{{ __('Slug') }}</label>
              <div class="input-group">
                <input name="slug" id="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
                <button type="button" class="btn btn-outline-secondary" id="slugifyButton" title="{{ __('Generate from name') }}">
                  <i class="bi bi-magic"></i>
                </button>
              </div>
            </div>

            <div class="col-md-3">
              <label class="form-label">{{ __('Status') }}</label>
              <select name="status" class="form-select">
                @foreach(['draft','published','archived'] as $s)
                  <option value="{{ $s }}" @selected(old('status', $product->status)===$s)>{{ $s }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">{{ __('Type') }}</label>
              <select name="product_type" class="form-select">
                @foreach(['simple','variable','digital','service'] as $t)
                  <option value="{{ $t }}" @selected(old('product_type', $product->product_type)===$t)>{{ $t }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">{{ __('Short description') }}</label>
              <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">{{ __('Description') }}</label>
              <textarea name="description" class="form-control" rows="8">{{ old('description', $product->description) }}</textarea>
            </div>
          </div>
        </div>

        {{-- ORGANISATION --}}
        <div id="tab-organisation" class="tab-pane fade {{ $activeTab === 'organisation' ? 'show active' : '' }}">

          {{-- sentinel so update() knows to sync --}}
          <input type="hidden" name="sync_organisation" value="1">

          <div class="row g-4">

            {{-- Categories --}}
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span class="fw-semibold"><i class="bi bi-folder2 me-1"></i>{{ __('Categories') }}</span>
                  <a href="{{ route('admin.ecommerce.categories.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    {{ __('Manage') }}
                  </a>
                </div>
                <div class="card-body" style="max-height:340px; overflow-y:auto;">
                  @if($allCategories->isEmpty())
                    <p class="text-muted small mb-0">{{ __('No categories yet.') }}</p>
                  @else
                    @foreach($allCategories as $cat)
                      <div class="form-check mb-2">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          name="categories[]"
                          value="{{ $cat->id }}"
                          id="cat_{{ $cat->id }}"
                          @checked($product->categories->contains($cat->id))
                        >
                        <label class="form-check-label" for="cat_{{ $cat->id }}">
                          {{ $cat->name }}
                          @if($cat->parent_id)
                            <span class="text-muted small ms-1">/ {{ $allCategories->firstWhere('id', $cat->parent_id)?->name }}</span>
                          @endif
                        </label>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            </div>

            {{-- Tags --}}
            <div class="col-md-6">
              <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span class="fw-semibold"><i class="bi bi-tag me-1"></i>{{ __('Tags') }}</span>
                  <a href="{{ route('admin.ecommerce.tags.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    {{ __('Manage') }}
                  </a>
                </div>
                <div class="card-body" style="max-height:340px; overflow-y:auto;">
                  @if($allTags->isEmpty())
                    <p class="text-muted small mb-0">{{ __('No tags yet.') }}</p>
                  @else
                    <div class="d-flex flex-wrap gap-2">
                      @foreach($allTags as $tag)
                        <div class="form-check m-0">
                          <input
                            class="form-check-input visually-hidden"
                            type="checkbox"
                            name="tags[]"
                            value="{{ $tag->id }}"
                            id="tag_{{ $tag->id }}"
                            @checked($product->tags->contains($tag->id))
                          >
                          <label
                            class="badge rounded-pill border cursor-pointer user-select-none px-3 py-2"
                            style="font-size: .8rem; font-weight: 500;"
                            for="tag_{{ $tag->id }}"
                          >
                            {{ $tag->name }}
                          </label>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- VARIANTS --}}
        <div id="tab-variants" class="tab-pane fade {{ $activeTab === 'variants' ? 'show active' : '' }}">

          @if($product->product_type !== 'variable')
            <div class="alert alert-warning border">
              <div class="fw-semibold">{{ __('This product is not variable') }}</div>
              <div class="text-muted small">{{ __('Set Type = variable in General tab to manage size/color variants.') }}</div>
            </div>
          @else

            {{-- Generate variants (NO nested form) --}}
            <div class="card mb-3">
              <div class="card-body">
                <h6 class="mb-2">{{ __('Generate variants') }}</h6>
                <div class="text-muted small mb-3">
                  {{ __('Select values (e.g. Color: Red/Blue, Size: S/M/L) and generate combinations.') }}
                </div>

                <div class="row g-3">
                  @foreach($attributes as $attr)
                    <div class="col-12 col-lg-6">
                      <div class="border rounded-4 p-3">
                        <div class="fw-semibold mb-2">{{ $attr->name }}</div>

                        <div class="d-flex flex-wrap gap-2">
                          @foreach($attr->values as $val)
                            <label class="form-check form-check-inline m-0">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                name="values[{{ $attr->id }}][]"
                                value="{{ $val->id }}"
                                form="variants-generate-form"
                              >
                              <span class="form-check-label">{{ $val->value }}</span>
                            </label>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="d-flex gap-2 mt-3">
                  <button class="btn btn-outline-primary" type="submit" form="variants-generate-form">
                    <i class="bi bi-magic me-1"></i>{{ __('Generate') }}
                  </button>
                </div>
              </div>
            </div>

            {{-- Add variant manually --}}
            <div class="card mb-3">
              <div class="card-body">
                <h6 class="mb-2">{{ __('Add variant manually') }}</h6>
                <div class="text-muted small mb-3">
                  {{ __('Pick one value per attribute (e.g. Color=Red, Size=M), then set SKU/price/stock and (optional) image.') }}
                </div>

                <form method="POST" action="{{ route('admin.ecommerce.products.variants.store', $product) }}">
                  @csrf

                  <div class="row g-3">

                    <div class="col-12">
                      <div class="row g-2">
                        @foreach($attributes as $attr)
                          <div class="col-12 col-lg-6">
                            <div class="border rounded-4 p-3">
                              <div class="fw-semibold mb-2">{{ $attr->name }}</div>
                              <select class="form-select" name="attribute_value_ids[]" required>
                                <option value="">{{ __('Select') }} {{ $attr->name }}</option>
                                @foreach($attr->values as $val)
                                  <option value="{{ $val->id }}">{{ $val->value }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        @endforeach
                      </div>

                      <div class="form-text text-muted">
                        {{ __('One dropdown per attribute. This creates or updates the variant combination safely (signature).') }}
                      </div>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">{{ __('SKU') }}</label>
                      <input class="form-control" name="sku" value="{{ old('sku') }}">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">{{ __('Price') }}</label>
                      <input class="form-control" name="price" value="{{ old('price') }}">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">{{ __('Sale price') }}</label>
                      <input class="form-control" name="sale_price" value="{{ old('sale_price') }}">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">{{ __('Stock qty') }}</label>
                      <input class="form-control" type="number" min="0" name="stock_qty" value="{{ old('stock_qty') }}">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">{{ __('Stock status') }}</label>
                      <select class="form-select" name="stock_status" required>
                        <option value="in_stock" @selected(old('stock_status')==='in_stock')>in_stock</option>
                        <option value="out_of_stock" @selected(old('stock_status')==='out_of_stock')>out_of_stock</option>
                        <option value="backorder" @selected(old('stock_status')==='backorder')>backorder</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">{{ __('Variant image') }}</label>
                      <select class="form-select" name="image_media_id">
                        <option value="">{{ __('No image') }}</option>
                        @foreach(($mediaFiles ?? collect()) as $m)
                          <option value="{{ $m->id }}">
                            #{{ $m->id }} — {{ $m->name ?? $m->file_name ?? $m->file_path ?? 'media' }}
                          </option>
                        @endforeach
                      </select>
                      <div class="form-text text-muted">{{ __('Select an existing Media item. We can build a nicer picker later.') }}</div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="variantDefault">
                        <label class="form-check-label" for="variantDefault">
                          {{ __('Make default variant') }}
                        </label>
                      </div>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('Add variant') }}
                      </button>
                    </div>

                  </div>
                </form>
              </div>
            </div>

            {{-- Existing variants list --}}
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="mb-0">{{ __('Variants') }}</h6>
                  <span class="text-muted small">{{ $product->variants->count() }} {{ __('items') }}</span>
                </div>

                @if($product->variants->isEmpty())
                  <div class="text-muted">{{ __('No variants yet.') }}</div>
                @else
                  <div class="table-responsive">
                    <table class="table align-middle">
                      <thead>
                        <tr>
                          <th>{{ __('Variant') }}</th>
                          <th>{{ __('SKU') }}</th>
                          <th>{{ __('Price') }}</th>
                          <th>{{ __('Stock') }}</th>
                          <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($product->variants as $variant)
                          <tr>
                            <td class="fw-semibold">
                              {{ $variant->title ?? ('#'.$variant->id) }}
                              <div class="small text-muted">
                                {{ $variant->attributeValues->map(fn($v) => $v->attribute->name.': '.$v->value)->implode(' • ') }}
                              </div>
                            </td>
                            <td>{{ $variant->sku }}</td>
                            <td>{{ $variant->price }}</td>
                            <td>{{ $variant->stock_qty }}</td>
                            <td class="text-end">
                              <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.variants.edit', $variant) }}">
                                  {{ __('Edit') }}
                                </a>

                                <form method="POST" action="{{ route('admin.ecommerce.variants.destroy', $variant) }}"
                                      onsubmit="return confirm('{{ __('Delete variant?') }}')">
                                  @csrf
                                  @method('DELETE')
                                  <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                </form>
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif
              </div>
            </div>
          @endif
        </div>

        {{-- PRICING --}}
        <div id="tab-pricing" class="tab-pane fade {{ $activeTab === 'pricing' ? 'show active' : '' }}">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">{{ __('Base price') }}</label>
              <input name="base_price" class="form-control" value="{{ old('base_price', $product->base_price) }}">
              <div class="form-text text-muted">{{ __('Store as decimal (e.g. 19.99).') }}</div>
            </div>

            <div class="col-md-3">
              <label class="form-label">{{ __('Sale price') }}</label>
              <input name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">{{ __('SKU') }}</label>
              <input name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">{{ __('Stock') }}</label>
              <input name="stock" class="form-control" value="{{ old('stock', $product->stock ?? '') }}">
              <div class="form-text text-muted">
                {{ __('For variable products, use variant stock instead.') }}
              </div>
            </div>

            <div class="col-12">
              <div class="alert alert-light border mb-0">
                <div class="fw-semibold mb-1">{{ __('Tip') }}</div>
                <div class="text-muted small">
                  {{ __('If product type is "variable", price/stock should be set per variant.') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- MEDIA --}}
        <div id="tab-media" class="tab-pane fade {{ $activeTab === 'media' ? 'show active' : '' }}">
          <div class="row g-3">
            <div class="col-12">
              <div class="alert alert-light border">
                <div class="fw-semibold mb-1">{{ __('Product media') }}</div>
                <div class="text-muted small">
                  {{ __('Here we’ll plug in your existing gallery/media component (thumbnail + gallery).') }}
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="border rounded-4 p-3">
                <div class="fw-semibold mb-2"><i class="bi bi-image me-1"></i>{{ __('Thumbnail') }}</div>
                <div class="text-muted small">{{ __('Attach one main image.') }}</div>
                <div class="mt-3" style="height: 220px; border: 2px dashed var(--bs-border-color); border-radius: 12px;"></div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="border rounded-4 p-3">
                <div class="fw-semibold mb-2"><i class="bi bi-images me-1"></i>{{ __('Gallery') }}</div>
                <div class="text-muted small">{{ __('Multiple images with reorder (DnD)') }}</div>
                <div class="mt-3" style="height: 220px; border: 2px dashed var(--bs-border-color); border-radius: 12px;"></div>
              </div>
            </div>
          </div>
        </div>

        {{-- SEO --}}
        <div id="tab-seo" class="tab-pane fade {{ $activeTab === 'seo' ? 'show active' : '' }}">

          @if(\App\Models\Setting::bool('ai_seo_enabled'))
            <div class="d-flex justify-content-end mb-3">
              <button type="button"
                      class="btn btn-outline-primary btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#aiSeoModal-product-{{ $product->id }}">
                <i class="bi bi-stars me-1"></i> {{ __('Generate SEO') }}
              </button>
            </div>
          @endif

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">{{ __('Meta title') }}</label>
              <input class="form-control" name="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">{{ __('Canonical') }}</label>
              <input class="form-control" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url ?? '') }}">
            </div>

            <div class="col-12">
              <label class="form-label">{{ __('Meta description') }}</label>
              <textarea class="form-control" name="meta_description" rows="4">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">{{ __('Keywords') }}</label>
              <input class="form-control" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}">
            </div>
          </div>
        </div>

      </div>
    </form>

    @include('pages.admin.partials.ai-seo-modal', [
        'contentType' => 'product',
        'contentId'   => $product->id,
    ])

  </div>
</div>

{{-- Hidden generate form (prevents nested form bug) --}}
<form id="variants-generate-form"
      method="POST"
      action="{{ route('admin.ecommerce.products.variants.generate', $product) }}"
      class="d-none">
  @csrf
</form>

{{-- Sticky bottom save bar --}}
<div class="sticky-bottom-bar bg-body border-top shadow-sm p-2">
  <div class="container-fluid">
    <div class="d-flex justify-content-end align-items-center gap-2">
      <button type="submit" form="product-edit-form" class="btn btn-primary">
        <i class="bi bi-save me-1"></i> {{ __('Save All Changes') }}
      </button>
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Tag pill toggle: checked = primary fill, unchecked = light outline */
  .form-check-input:not(:checked) + .badge { background: var(--bs-light); color: var(--bs-body-color); border-color: var(--bs-border-color) !important; }
  .form-check-input:checked + .badge { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary) !important; }
  .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-url]').forEach(el => {
      el.addEventListener('click', () => {
        const url = el.getAttribute('data-url');
        if (url) history.replaceState(null, '', url);
      });
    });
  });

  function slugify(text) {
    return text
      .toString()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-');
  }

  document.getElementById('slugifyButton')?.addEventListener('click', function () {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (!slugInput || !nameInput) return;

    if (slugInput.value.trim() !== '') {
      slugInput.value = slugify(slugInput.value);
    } else if (nameInput.value.trim() !== '') {
      slugInput.value = slugify(nameInput.value);
    }
  });
</script>
@endpush
</x-app-layout>
