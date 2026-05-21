<?php

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Attribute;
use App\Models\Ecommerce\AttributeValue;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductVariant;
use App\Models\Ecommerce\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * WooCommerce-compatible CSV format — supported by Allegro import tools.
 *
 * Row types:
 *   simple / digital / service  — one row per product
 *   variable                    — product header row (lists all possible attribute values)
 *   variation                   — child row under a variable parent (one specific combo)
 *
 * Attribute columns are dynamic:
 *   "Attribute 1 name", "Attribute 1 value(s)", "Attribute 1 visible", "Attribute 1 global"
 *   "Attribute 2 name", ...
 */
class ProductImportExportService
{
    private const BASE_HEADERS = [
        'ID', 'Type', 'SKU', 'Name', 'Published',
        'Short description', 'Description',
        'Date sale price starts', 'Date sale price ends',
        'Regular price', 'Sale price',
        'In stock?', 'Stock', 'Backorders allowed?',
        'Weight (kg)', 'Length (cm)', 'Width (cm)', 'Height (cm)',
        'Categories', 'Tags', 'Images',
        'Meta: title', 'Meta: description', 'Meta: keywords', 'Canonical URL',
        'Parent', 'Position',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Stream a CSV download. Pass null to export all, or an array of product IDs.
     */
    public function exportStream(?array $productIds = null, ?Builder $customQuery = null): StreamedResponse
    {
        $filename = 'products-export-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($productIds, $customQuery) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM — required by Excel and Allegro importer
            fwrite($out, "\xEF\xBB\xBF");

            $products = $this->loadProducts($productIds, $customQuery);
            [$headers, $attrNames] = $this->buildHeaders($products);

            fputcsv($out, $headers);

            foreach ($products as $product) {
                foreach ($this->productToRows($product, $attrNames) as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Download a blank template with two example rows showing simple + variable/variation formats.
     */
    public function templateStream(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $headers = array_merge(self::BASE_HEADERS, [
                'Attribute 1 name', 'Attribute 1 value(s)', 'Attribute 1 visible', 'Attribute 1 global',
                'Attribute 2 name', 'Attribute 2 value(s)', 'Attribute 2 visible', 'Attribute 2 global',
            ]);
            fputcsv($out, $headers);

            // Example: simple product
            fputcsv($out, [
                '', 'simple', 'PROD-001', 'Example Simple Product', '1',
                'Short description here', 'Full product description here',
                '', '',             // sale_from, sale_to
                '99.99', '79.99',  // regular, sale
                '1', '100', '0',   // in_stock, stock, backorders
                '0.5', '30', '20', '10',            // weight, length, width, height
                'Category 1 > Subcategory', 'tag1|tag2', '',  // categories, tags, images
                'Meta title', 'Meta description', 'meta,keywords', '',  // SEO
                '', '',            // parent, position
                '', '', '1', '1', // attr 1 (empty for simple)
                '', '', '1', '1', // attr 2 (empty for simple)
            ]);

            // Example: variable product header
            fputcsv($out, [
                '', 'variable', 'VAR-001', 'Example Variable Product', '1',
                'Comes in multiple colors and sizes', 'Full variable product description.',
                '', '', '', '',
                '1', '', '0',
                '', '', '', '',
                'Electronics > Phones', 'promo|new', '',
                '', '', '', '',
                '', '',
                'Color', 'Red|Blue|Green', '1', '1',
                'Size', 'S|M|L|XL', '1', '1',
            ]);

            // Example: variation child rows
            fputcsv($out, [
                '', 'variation', 'VAR-001-RED-S', 'Example Variable Product', '1',
                '', '',
                '', '', '89.99', '',
                '1', '50', '0',
                '0.3', '28', '18', '8',
                '', '', '',
                '', '', '', '',
                'VAR-001', '0',
                'Color', 'Red', '1', '1',
                'Size', 'S', '1', '1',
            ]);

            fputcsv($out, [
                '', 'variation', 'VAR-001-BLUE-M', 'Example Variable Product', '1',
                '', '',
                '', '', '89.99', '',
                '1', '45', '0',
                '0.3', '28', '18', '8',
                '', '', '',
                '', '', '', '',
                'VAR-001', '1',
                'Color', 'Blue', '1', '1',
                'Size', 'M', '1', '1',
            ]);

            fclose($out);
        }, 'products-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT
    // ─────────────────────────────────────────────────────────────────────────

    public function import(string $csvContent, bool $updateExisting = true): ImportResult
    {
        $result = new ImportResult();

        $csvContent = ltrim($csvContent, "\xEF\xBB\xBF"); // strip BOM

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csvContent);
        rewind($handle);

        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            $result->errors[] = ['row' => 0, 'message' => 'Could not parse CSV header row.'];
            fclose($handle);
            return $result;
        }

        $headers    = array_map('trim', $rawHeader);
        $attrGroups = $this->detectAttributeColumns($headers);
        $headerCount = count($headers);

        $rowNum       = 1;
        $parentSkuMap = []; // sku → Product (cache to avoid repeated DB hits)

        while (($rawRow = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Normalise row length
            while (count($rawRow) < $headerCount) {
                $rawRow[] = '';
            }
            if (count($rawRow) > $headerCount) {
                $rawRow = array_slice($rawRow, 0, $headerCount);
            }

            $row  = array_combine($headers, array_map('trim', $rawRow));
            $type = strtolower($row['Type'] ?? '');

            if ($type === '' || $type === 'type') {
                continue;
            }

            try {
                if ($type === 'variation') {
                    $this->importVariation($row, $attrGroups, $parentSkuMap, $updateExisting, $result, $rowNum);
                } else {
                    $product = $this->importProduct($row, $type, $updateExisting, $result, $rowNum);
                    // Cache variable parents by SKU so variation rows can resolve them
                    if ($product !== null && $type === 'variable') {
                        $sku = $product->sku ?? ($row['SKU'] ?? '');
                        if ($sku !== '') {
                            $parentSkuMap[$sku] = $product;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $result->errors[] = ['row' => $rowNum, 'message' => $e->getMessage()];
                $result->skipped++;
            }
        }

        fclose($handle);
        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private — export helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function loadProducts(?array $productIds, ?Builder $customQuery): Collection
    {
        $query = $customQuery
            ?? Product::query()->whereNull('deleted_at');

        $query->with([
            'variants.attributeValues.attribute',
            'categories.parent',
            'tags',
        ])->orderBy('id');

        if ($productIds !== null) {
            $query->whereIn('id', $productIds);
        }

        return $query->get();
    }

    /**
     * Collect all unique attribute names across all products, build full header row.
     *
     * @return array{0: string[], 1: string[]}
     */
    private function buildHeaders(Collection $products): array
    {
        $attrNames = [];

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                foreach ($variant->attributeValues as $av) {
                    $name = $av->attribute->name ?? null;
                    if ($name && !in_array($name, $attrNames, true)) {
                        $attrNames[] = $name;
                    }
                }
            }
        }

        $headers = self::BASE_HEADERS;
        foreach ($attrNames as $i => $name) {
            $n = $i + 1;
            $headers[] = "Attribute {$n} name";
            $headers[] = "Attribute {$n} value(s)";
            $headers[] = "Attribute {$n} visible";
            $headers[] = "Attribute {$n} global";
        }

        return [$headers, $attrNames];
    }

    /**
     * Build 1..N CSV rows for a product (1 product row + 1 per variant for variable type).
     */
    private function productToRows(Product $product, array $attrNames): array
    {
        $rows = [];

        $categoryPaths = $product->categories->map(function (Category $cat) {
            return $cat->parent ? ($cat->parent->name . ' > ' . $cat->name) : $cat->name;
        })->join('|');

        $tagList    = $product->tags->pluck('name')->join('|');
        $isVariable = $product->product_type === 'variable';

        // Collect all attribute values per attribute for the product header row
        $allAttrValues = [];
        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $av) {
                $name = $av->attribute->name ?? null;
                if ($name) {
                    $allAttrValues[$name][] = $av->value;
                }
            }
        }
        foreach ($allAttrValues as $k => $vals) {
            $allAttrValues[$k] = array_unique($vals);
        }

        // Product / simple row
        $row = [
            $product->id,
            $isVariable ? 'variable' : $product->product_type,
            $product->sku ?? '',
            $product->name,
            $product->status === 'published' ? '1' : '0',
            strip_tags($product->short_description ?? ''),
            strip_tags($product->description ?? ''),
            $product->sale_from?->format('Y-m-d') ?? '',
            $product->sale_to?->format('Y-m-d') ?? '',
            $product->base_price ?? '',
            $product->sale_price ?? '',
            $product->is_in_stock ? '1' : '0',
            $product->stock ?? '',
            '0', // backorders — map from manage_stock if needed
            '', '', '', '', // weight/dimensions live on variants
            $categoryPaths,
            $tagList,
            '',  // images
            $product->meta_title ?? '',
            $product->meta_description ?? '',
            $product->meta_keywords ?? '',
            $product->canonical_url ?? '',
            '', // parent
            '', // position
        ];

        foreach ($attrNames as $name) {
            $row[] = $name;
            $row[] = implode('|', $allAttrValues[$name] ?? []);
            $row[] = '1';
            $row[] = '1';
        }

        $rows[] = $row;

        // Variation rows
        if ($isVariable) {
            foreach ($product->variants as $position => $variant) {
                $variantAttrValues = [];
                foreach ($variant->attributeValues as $av) {
                    $variantAttrValues[$av->attribute->name ?? ''] = $av->value;
                }

                $varRow = [
                    '',
                    'variation',
                    $variant->sku ?? '',
                    $product->name,
                    $product->status === 'published' ? '1' : '0',
                    '', '',
                    $variant->sale_from?->format('Y-m-d') ?? '',
                    $variant->sale_to?->format('Y-m-d') ?? '',
                    $variant->price ?? '',
                    $variant->sale_price ?? '',
                    $variant->stock_status === 'in_stock' ? '1' : '0',
                    $variant->stock_qty ?? '',
                    $variant->stock_status === 'backorder' ? '1' : '0',
                    $variant->weight ?? '',
                    $variant->length ?? '',
                    $variant->width ?? '',
                    $variant->height ?? '',
                    '', '', '',   // categories, tags, images
                    '', '', '', '',  // SEO
                    $product->sku ?? '',  // parent SKU
                    $position,
                ];

                foreach ($attrNames as $name) {
                    $varRow[] = $name;
                    $varRow[] = $variantAttrValues[$name] ?? '';
                    $varRow[] = '1';
                    $varRow[] = '1';
                }

                $rows[] = $varRow;
            }
        }

        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private — import helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Find all "Attribute N name" / "Attribute N value(s)" column-pair groups.
     *
     * @return array<int, array{name_col: string, values_col: string}>
     */
    private function detectAttributeColumns(array $headers): array
    {
        $groups = [];
        foreach ($headers as $col) {
            if (preg_match('/^Attribute (\d+) name$/i', $col, $m)) {
                $n = (int) $m[1];
                $groups[$n] = [
                    'name_col'   => $col,
                    'values_col' => "Attribute {$n} value(s)",
                ];
            }
        }
        ksort($groups);
        return array_values($groups);
    }

    private function importProduct(
        array $row,
        string $type,
        bool $update,
        ImportResult $result,
        int $rowNum
    ): ?Product {
        $name = $row['Name'] ?? '';
        $sku  = $row['SKU']  ?? '';

        if ($name === '') {
            $result->errors[] = ['row' => $rowNum, 'message' => 'Row skipped: Name is required.'];
            $result->skipped++;
            return null;
        }

        $productType = match($type) {
            'variable' => 'variable',
            'digital'  => 'digital',
            'service'  => 'service',
            default    => 'simple',
        };

        // Find existing record: prefer SKU match, fall back to name+type
        $existing = null;
        if ($sku !== '') {
            $existing = Product::where('sku', $sku)->first();
        }
        if (!$existing) {
            $existing = Product::where('name', $name)->where('product_type', $productType)->first();
        }

        if ($existing && !$update) {
            $result->skipped++;
            return $existing;
        }

        $attrs = $this->buildProductAttributes($row, $productType);

        if ($existing) {
            $existing->update(array_merge($attrs, ['updated_by' => auth()->id()]));
            $product = $existing->fresh();
            $result->updated++;
        } else {
            $attrs['slug']       = $this->uniqueSlug(Str::slug($name ?: 'product'));
            $attrs['created_by'] = auth()->id();
            $attrs['updated_by'] = auth()->id();
            $product = Product::create($attrs);
            $result->created++;
        }

        $this->syncCategories($product, $row['Categories'] ?? '');
        $this->syncTags($product, $row['Tags'] ?? '');

        return $product;
    }

    private function buildProductAttributes(array $row, string $productType): array
    {
        $attrs = [
            'name'         => $row['Name'],
            'status'       => ($row['Published'] ?? '1') === '1' ? 'published' : 'draft',
            'product_type' => $productType,
        ];

        if (($row['SKU'] ?? '') !== '') {
            $attrs['sku'] = $row['SKU'];
        }

        // Nullable text fields
        $textMap = [
            'Short description' => 'short_description',
            'Description'       => 'description',
            'Meta: title'       => 'meta_title',
            'Meta: description' => 'meta_description',
            'Meta: keywords'    => 'meta_keywords',
            'Canonical URL'     => 'canonical_url',
        ];
        foreach ($textMap as $col => $field) {
            if (array_key_exists($col, $row)) {
                $attrs[$field] = $row[$col] !== '' ? $row[$col] : null;
            }
        }

        // Prices
        $basePrice = $this->decimal($row['Regular price'] ?? '');
        $salePrice = $this->decimal($row['Sale price'] ?? '');
        if ($basePrice !== null) $attrs['base_price'] = $basePrice;
        if ($salePrice !== null) $attrs['sale_price'] = $salePrice;

        $saleFrom = $this->parseDate($row['Date sale price starts'] ?? '');
        $saleTo   = $this->parseDate($row['Date sale price ends'] ?? '');
        if ($saleFrom !== null) $attrs['sale_from'] = $saleFrom;
        if ($saleTo   !== null) $attrs['sale_to']   = $saleTo;

        // Stock
        $attrs['is_in_stock'] = ($row['In stock?'] ?? '1') === '1';
        $stock = $this->integer($row['Stock'] ?? '');
        if ($stock !== null) {
            $attrs['stock']        = $stock;
            $attrs['manage_stock'] = true;
        }

        return $attrs;
    }

    private function importVariation(
        array $row,
        array $attrGroups,
        array &$parentSkuMap,
        bool $update,
        ImportResult $result,
        int $rowNum
    ): void {
        $parentSku = $row['Parent'] ?? '';
        if ($parentSku === '') {
            $result->errors[] = ['row' => $rowNum, 'message' => 'Variation skipped: missing Parent SKU.'];
            $result->skipped++;
            return;
        }

        // Resolve parent product
        if (!isset($parentSkuMap[$parentSku])) {
            $parent = Product::where('sku', $parentSku)->first();
            if (!$parent) {
                $result->errors[] = ['row' => $rowNum, 'message' => "Variation skipped: parent SKU '{$parentSku}' not found. Import the parent product first."];
                $result->skipped++;
                return;
            }
            $parentSkuMap[$parentSku] = $parent;
        }
        $parent = $parentSkuMap[$parentSku];

        // Resolve attribute values → value IDs → signature
        $valueIds = [];
        foreach ($attrGroups as $group) {
            $attrName  = trim($row[$group['name_col']]   ?? '');
            $attrValue = trim($row[$group['values_col']] ?? '');
            if ($attrName === '' || $attrValue === '') {
                continue;
            }

            $attr = Attribute::firstOrCreate(
                ['slug' => Str::slug($attrName)],
                ['name' => $attrName, 'type' => 'select']
            );

            $av = AttributeValue::firstOrCreate(
                ['attribute_id' => $attr->id, 'slug' => Str::slug($attrValue)],
                ['value' => $attrValue, 'sort_order' => 0]
            );

            $valueIds[] = $av->id;
        }

        if (empty($valueIds)) {
            $result->errors[] = ['row' => $rowNum, 'message' => 'Variation skipped: no attribute values found.'];
            $result->skipped++;
            return;
        }

        sort($valueIds);
        $signature = implode('-', $valueIds);

        $variantData = [
            'product_id'      => $parent->id,
            'signature'       => $signature,
            'track_inventory' => true,
            'stock_status'    => ($row['In stock?'] ?? '1') === '1' ? 'in_stock' : 'out_of_stock',
        ];

        $varSku = $row['SKU'] ?? '';
        if ($varSku !== '') $variantData['sku'] = $varSku;

        $price     = $this->decimal($row['Regular price'] ?? '');
        $salePrice = $this->decimal($row['Sale price']   ?? '');
        if ($price !== null)     $variantData['price']      = $price;
        if ($salePrice !== null) $variantData['sale_price'] = $salePrice;

        $saleFrom = $this->parseDate($row['Date sale price starts'] ?? '');
        $saleTo   = $this->parseDate($row['Date sale price ends']   ?? '');
        if ($saleFrom !== null) $variantData['sale_from'] = $saleFrom;
        if ($saleTo   !== null) $variantData['sale_to']   = $saleTo;

        $stock = $this->integer($row['Stock'] ?? '');
        if ($stock !== null) $variantData['stock_qty'] = $stock;

        foreach (['weight' => 'Weight (kg)', 'length' => 'Length (cm)', 'width' => 'Width (cm)', 'height' => 'Height (cm)'] as $field => $col) {
            $val = $this->decimal($row[$col] ?? '');
            if ($val !== null) $variantData[$field] = $val;
        }

        if ($update) {
            $variant = ProductVariant::updateOrCreate(
                ['product_id' => $parent->id, 'signature' => $signature],
                $variantData
            );
            $result->updated++;
        } else {
            $variant = ProductVariant::firstOrCreate(
                ['product_id' => $parent->id, 'signature' => $signature],
                $variantData
            );
            if ($variant->wasRecentlyCreated) {
                $result->created++;
            } else {
                $result->skipped++;
            }
        }

        $variant->attributeValues()->sync($valueIds);

        // Set as default if no default exists yet
        if (!$parent->variants()->where('is_default', true)->exists()) {
            $variant->update(['is_default' => true]);
        }
    }

    private function syncCategories(Product $product, string $categoryStr): void
    {
        if ($categoryStr === '') return;

        $ids = [];
        foreach (explode('|', $categoryStr) as $path) {
            $path = trim($path);
            if ($path === '') continue;

            $parentId = null;
            $category = null;
            foreach (array_map('trim', explode('>', $path)) as $part) {
                if ($part === '') continue;
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($part), 'parent_id' => $parentId],
                    ['name' => $part, 'sort_order' => 0]
                );
                $parentId = $category->id;
            }
            if ($category) {
                $ids[] = $category->id;
            }
        }

        if ($ids) {
            $product->categories()->sync($ids);
        }
    }

    private function syncTags(Product $product, string $tagStr): void
    {
        if ($tagStr === '') return;

        $ids = [];
        foreach (explode('|', $tagStr) as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $tag  = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            $ids[] = $tag->id;
        }

        if ($ids) {
            $product->tags()->sync($ids);
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'product';
        $i    = 1;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function decimal(string $val): ?float
    {
        $val = str_replace(',', '.', trim($val));
        return is_numeric($val) ? (float) $val : null;
    }

    private function integer(string $val): ?int
    {
        $val = trim($val);
        return ctype_digit($val) ? (int) $val : null;
    }

    private function parseDate(string $val): ?string
    {
        $val = trim($val);
        if ($val === '') return null;
        try {
            return \Carbon\Carbon::parse($val)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
