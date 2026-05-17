<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Ecommerce\Attribute;
use App\Models\Ecommerce\ProductVariant;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Tag;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index', 'show']),
            new Middleware('can:manage ecommerce', only: [
                'create', 'store', 'edit', 'update', 'destroy', 'generateVariants',
            ]),
        ];
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'published'); // published|draft|all|trash
        $q   = trim((string) $request->get('q', ''));

        $base = Product::query();

        // Tab filter
        if ($tab === 'published') {
            $base->whereNull('deleted_at')->where('status', 'published');
        } elseif ($tab === 'draft') {
            $base->whereNull('deleted_at')->where('status', 'draft');
        } elseif ($tab === 'trash') {
            $base->onlyTrashed();
        } else { // all
            $base->withTrashed();
        }

        // Search
        $this->applyProductSearch($base, $q);

        // List
        $products = $base
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Counts (respect search too)
        $counts = $this->productTabCounts($q);

        return view('pages.admin.ecommerce.products.index', compact('products', 'tab', 'counts', 'q'));
    }

    /**
     * Searches by: id (exact if numeric) + partial matches in name/slug/type/status
     */
    private function applyProductSearch(\Illuminate\Database\Eloquent\Builder $query, string $q): void
    {
        if ($q === '') return;

        $query->where(function ($qq) use ($q) {
            if (ctype_digit($q)) {
                $qq->orWhere('id', (int) $q);
            }

            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q).'%';

            $qq->orWhere('name', 'like', $like)
            ->orWhere('slug', 'like', $like)
            ->orWhere('product_type', 'like', $like)
            ->orWhere('status', 'like', $like);
        });
    }

    private function productTabCounts(string $q): array
    {
        $make = function (\Illuminate\Database\Eloquent\Builder $builder) use ($q) {
            $this->applyProductSearch($builder, $q);
            return $builder->count();
        };

        return [
            'published' => $make(Product::query()->whereNull('deleted_at')->where('status', 'published')),
            'draft'     => $make(Product::query()->whereNull('deleted_at')->where('status', 'draft')),
            'all'       => $make(Product::query()->withTrashed()),
            'trash'     => $make(Product::query()->onlyTrashed()),
        ];
    }


    public function show(Product $product)
    {
        return redirect()->route('ecommerce.products.edit', $product);
    }

    public function create()
    {
        $allCategories = Category::orderBy('name')->get();
        $allTags       = Tag::orderBy('name')->get();

        return view('pages.admin.ecommerce.products.create', compact('allCategories', 'allTags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],

            'status' => ['required','in:draft,published,archived'],
            'product_type' => ['required','in:simple,variable,digital,service'],

            'short_description' => ['nullable','string'],
            'description' => ['nullable','string'],

            'base_price' => ['nullable','numeric','min:0'],
            'sale_price' => ['nullable','numeric','min:0'],

            // inventory
            'sku' => ['nullable','string','max:100'],
            'stock' => ['nullable','integer','min:0'],

            // SEO
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            'meta_keywords' => ['nullable','string','max:255'],
            'canonical_url' => ['nullable','string','max:2048'],
        ]);

        $validated['slug'] = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);

        $product = Product::create([
            ...$validated,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $product->categories()->sync($request->input('categories', []));
        $product->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('success', __('Product created.'));
    }

    public function edit(Product $product)
    {
        $product->load([
            'variants.attributeValues.attribute',
            'variants.image',
            'categories',
            'tags',
        ]);

        $attributes    = Attribute::with('values')->orderBy('name')->get();
        $allCategories = Category::orderBy('name')->get();
        $allTags       = Tag::orderBy('name')->get();

        $mediaFiles = \App\Models\Media::orderByDesc('id')->limit(80)->get();

        return view('pages.admin.ecommerce.products.edit', compact(
            'product', 'attributes', 'mediaFiles', 'allCategories', 'allTags'
        ));
    }


    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes','required','string','max:255'],
            'slug' => ['sometimes','nullable','string','max:255'],

            'status' => ['sometimes','required','in:draft,published,archived'],
            'product_type' => ['sometimes','required','in:simple,variable,digital,service'],

            'short_description' => ['sometimes','nullable','string'],
            'description' => ['sometimes','nullable','string'],

            'base_price' => ['sometimes','nullable','numeric','min:0'],
            'sale_price' => ['sometimes','nullable','numeric','min:0'],

            // inventory
            'sku' => ['sometimes','nullable','string','max:100'],
            'stock' => ['sometimes','nullable','integer','min:0'],

            // SEO
            'meta_title' => ['sometimes','nullable','string','max:255'],
            'meta_description' => ['sometimes','nullable','string'],
            'meta_keywords' => ['sometimes','nullable','string','max:255'],
            'canonical_url' => ['sometimes','nullable','string','max:2048'],
        ]);

        //  only generate slug when name/slug is being changed OR missing
        if (array_key_exists('name', $validated) || array_key_exists('slug', $validated)) {
            $nameForSlug = $validated['name'] ?? $product->name;
            $validated['slug'] = ($validated['slug'] ?? null) ?: Str::slug((string) $nameForSlug);
        }

        $product->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        if ($request->has('sync_organisation')) {
            $product->categories()->sync($request->input('categories', []));
            $product->tags()->sync($request->input('tags', []));
        }

        return back()->with('success', __('Product updated.'));
    }

    public function generateVariants(Request $request, Product $product)
    {
        if ($product->product_type !== 'variable') {
            return back()->withErrors(['product_type' => 'Product must be variable to generate variants.']);
        }

        $valuesByAttribute = $request->input('values', []);
        // keep only non-empty arrays
        $valuesByAttribute = array_filter($valuesByAttribute, fn($arr) => is_array($arr) && count($arr));

        if (count($valuesByAttribute) === 0) {
            return back()->withErrors(['values' => 'Select at least one attribute value.']);
        }

        // cartesian product of selected values
        $combinations = [[]];
        foreach ($valuesByAttribute as $attrId => $valueIds) {
            $next = [];
            foreach ($combinations as $combo) {
                foreach ($valueIds as $vid) {
                    $c = $combo;
                    $c[] = (int)$vid;
                    $next[] = $c;
                }
            }
            $combinations = $next;
        }

        foreach ($combinations as $valueIds) {
            sort($valueIds); // stable signature
            $signature = implode('-', $valueIds);

            // requires signature column + unique(product_id, signature) ideally
            $variant = ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'signature' => $signature],
                [
                    'is_default' => false,
                    'track_inventory' => true,
                    'stock_status' => 'in_stock',
                ]
            );

            $variant->attributeValues()->sync($valueIds);
        }

        return back()->with('success', 'Variants generated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.ecommerce.products.index')
            ->with('success', __('Product moved to trash.'));
    }
}
