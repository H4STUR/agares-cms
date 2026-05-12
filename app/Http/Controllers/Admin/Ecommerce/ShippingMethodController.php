<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ShippingMethodController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index', 'show']),
            new Middleware('can:manage ecommerce', only: ['create', 'store', 'edit', 'update', 'destroy', 'toggleEnabled']),
        ];
    }

    public function index()
    {
        $methods = ShippingMethod::latest()->paginate(25);
        return view('pages.admin.ecommerce.shipping-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('pages.admin.ecommerce.shipping-methods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'pricing_type' => ['required', 'in:flat,weight,price'],
            'price'        => ['nullable', 'numeric', 'min:0'],
            'enabled'      => ['boolean'],
        ]);

        ShippingMethod::create($data);

        return redirect()->route('admin.ecommerce.shipping-methods.index')->with('success', 'Shipping method created.');
    }

    public function edit(ShippingMethod $shippingMethod)
    {
        return view('pages.admin.ecommerce.shipping-methods.edit', ['method' => $shippingMethod]);
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'pricing_type' => ['required', 'in:flat,weight,price'],
            'price'        => ['nullable', 'numeric', 'min:0'],
            'enabled'      => ['boolean'],
        ]);

        $shippingMethod->update($data);

        return back()->with('success', 'Shipping method updated.');
    }

    public function toggleEnabled(ShippingMethod $shippingMethod)
    {
        $shippingMethod->update(['enabled' => !$shippingMethod->enabled]);
        return response()->json(['enabled' => $shippingMethod->enabled]);
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();
        return redirect()->route('admin.ecommerce.shipping-methods.index')->with('success', 'Shipping method deleted.');
    }
}
