<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Product;
use App\Services\Ecommerce\ProductImportExportService;
use Illuminate\Http\Request;

class ProductImportExportController extends Controller
{
    public function __construct(private ProductImportExportService $service) {}

    /**
     * Export all products, optionally filtered by current tab/search (mirrors index query).
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $q   = trim((string) $request->get('q', ''));

        $query = Product::query();

        if ($tab === 'published') {
            $query->whereNull('deleted_at')->where('status', 'published');
        } elseif ($tab === 'draft') {
            $query->whereNull('deleted_at')->where('status', 'draft');
        } else {
            $query->whereNull('deleted_at'); // never export trashed by default
        }

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                if (ctype_digit($q)) {
                    $qq->orWhere('id', (int) $q);
                }
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
                $qq->orWhere('name', 'like', $like)
                   ->orWhere('sku', 'like', $like)
                   ->orWhere('product_type', 'like', $like);
            });
        }

        return $this->service->exportStream(null, $query);
    }

    /**
     * Export a specific set of product IDs (POST, for future checkbox-based selection).
     */
    public function exportSelected(Request $request)
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        return $this->service->exportStream($request->input('ids'));
    }

    /**
     * Download blank CSV template with example rows.
     */
    public function template()
    {
        return $this->service->templateStream();
    }

    public function importForm()
    {
        return view('pages.admin.ecommerce.products.import');
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file'        => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $update  = $request->boolean('update_existing', true);

        $result = $this->service->import($content, $update);

        return back()
            ->with('import_result', $result)
            ->with(
                $result->hasErrors() ? 'warning' : 'success',
                "Import complete — {$result->created} created, {$result->updated} updated, {$result->skipped} skipped."
            );
    }
}
