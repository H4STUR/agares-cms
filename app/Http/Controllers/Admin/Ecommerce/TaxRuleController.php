<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\TaxRule;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TaxRuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index', 'show']),
            new Middleware('can:manage ecommerce', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function index()
    {
        $rules = TaxRule::orderBy('priority', 'desc')->orderBy('country')->paginate(25);
        return view('pages.admin.ecommerce.tax-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('pages.admin.ecommerce.tax-rules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country'           => ['nullable', 'string', 'size:2'],
            'region'            => ['nullable', 'string', 'max:255'],
            'rate'              => ['required', 'numeric', 'min:0', 'max:100'],
            'prices_include_tax' => ['boolean'],
            'priority'          => ['nullable', 'integer', 'min:0'],
            'enabled'           => ['boolean'],
        ]);

        if (isset($data['country'])) {
            $data['country'] = strtoupper($data['country']);
        }

        TaxRule::create($data);

        return redirect()->route('admin.ecommerce.tax-rules.index')->with('success', 'Tax rule created.');
    }

    public function show(TaxRule $taxRule)
    {
        return redirect()->route('ecommerce.tax-rules.edit', $taxRule);
    }

    public function edit(TaxRule $taxRule)
    {
        return view('pages.admin.ecommerce.tax-rules.edit', ['rule' => $taxRule]);
    }

    public function update(Request $request, TaxRule $taxRule)
    {
        $data = $request->validate([
            'country'            => ['nullable', 'string', 'size:2'],
            'region'             => ['nullable', 'string', 'max:255'],
            'rate'               => ['required', 'numeric', 'min:0', 'max:100'],
            'prices_include_tax' => ['boolean'],
            'priority'           => ['nullable', 'integer', 'min:0'],
            'enabled'            => ['boolean'],
        ]);

        if (isset($data['country'])) {
            $data['country'] = strtoupper($data['country']);
        }

        $taxRule->update($data);

        return back()->with('success', 'Tax rule updated.');
    }

    public function destroy(TaxRule $taxRule)
    {
        $taxRule->delete();
        return redirect()->route('admin.ecommerce.tax-rules.index')->with('success', 'Tax rule deleted.');
    }
}
