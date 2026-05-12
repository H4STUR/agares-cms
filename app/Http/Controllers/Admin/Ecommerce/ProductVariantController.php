<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Product;
use Illuminate\Http\Request;
use App\Models\Ecommerce\ProductVariant;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductVariantController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage ecommerce', only: ['store', 'edit', 'update', 'destroy']),
        ];
    }


    public function store(Request $request, Product $product)
    {
        if ($product->product_type !== 'variable') {
            return back()->withErrors(['product_type' => 'Product must be variable to add variants.']);
        }

        $validated = $request->validate([
            'attribute_value_ids' => ['required','array','min:1'],
            'attribute_value_ids.*' => ['integer','exists:ecommerce_attribute_values,id'],

            'sku' => ['nullable','string','max:100'],
            'barcode' => ['nullable','string','max:255'],

            'price' => ['nullable','numeric','min:0'],
            'sale_price' => ['nullable','numeric','min:0'],

            'stock_qty' => ['nullable','integer','min:0'],
            'stock_status' => ['required','in:in_stock,out_of_stock,backorder'],

            'image_media_id' => ['nullable','integer','exists:media,id'], // adjust table name if different
            'is_default' => ['nullable','boolean'],
        ]);

        $valueIds = array_map('intval', $validated['attribute_value_ids']);
        sort($valueIds);
        $signature = implode('-', $valueIds);

        // ensure only one default
        if (!empty($validated['is_default'])) {
            $product->variants()->update(['is_default' => false]);
        }

        $variant = ProductVariant::updateOrCreate(
            ['product_id' => $product->id, 'signature' => $signature],
            [
                'sku' => $validated['sku'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'price' => $validated['price'] ?? null,
                'sale_price' => $validated['sale_price'] ?? null,
                'stock_qty' => $validated['stock_qty'] ?? null,
                'stock_status' => $validated['stock_status'],
                'image_media_id' => $validated['image_media_id'] ?? null,
                'is_default' => !empty($validated['is_default']),
                'track_inventory' => true,
            ]
        );

        $variant->attributeValues()->sync($valueIds);

        return back()->with('success', __('Variant saved.'));
    }


    public function edit(ProductVariant $variant)
    {
        $variant->load('attributeValues.attribute');

        return view('pages.admin.ecommerce.variants.edit', compact('variant'));
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => ['nullable','string','max:100'],
            'price' => ['nullable','numeric','min:0'],
            'sale_price' => ['nullable','numeric','min:0'],
            'stock_qty' => ['nullable','integer','min:0'],
            'stock_status' => ['required','in:in_stock,out_of_stock,backorder'],
            'is_default' => ['nullable','boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $variant->product->variants()->update(['is_default' => false]);
        }

        $variant->update($validated);

        return back()->with('success', __('Variant updated.'));
    }

    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return back()->with('success', __('Variant deleted.'));
    }
}
