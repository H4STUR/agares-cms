<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Coupon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CouponController extends Controller implements HasMiddleware
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
        $coupons = Coupon::withCount('redemptions')->latest()->paginate(25);
        return view('pages.admin.ecommerce.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('pages.admin.ecommerce.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                  => ['required', 'string', 'max:50', 'unique:ecommerce_coupons,code'],
            'type'                  => ['required', 'in:percent,fixed,free_shipping'],
            'value'                 => ['nullable', 'numeric', 'min:0'],
            'min_order_value'       => ['nullable', 'numeric', 'min:0'],
            'max_uses'              => ['nullable', 'integer', 'min:1'],
            'max_uses_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at'             => ['nullable', 'date'],
            'ends_at'               => ['nullable', 'date', 'after_or_equal:starts_at'],
            'enabled'               => ['boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        Coupon::create($data);

        return redirect()->route('admin.ecommerce.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        $coupon->loadCount('redemptions');
        return view('pages.admin.ecommerce.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'                  => ['required', 'string', 'max:50', "unique:ecommerce_coupons,code,{$coupon->id}"],
            'type'                  => ['required', 'in:percent,fixed,free_shipping'],
            'value'                 => ['nullable', 'numeric', 'min:0'],
            'min_order_value'       => ['nullable', 'numeric', 'min:0'],
            'max_uses'              => ['nullable', 'integer', 'min:1'],
            'max_uses_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at'             => ['nullable', 'date'],
            'ends_at'               => ['nullable', 'date', 'after_or_equal:starts_at'],
            'enabled'               => ['boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $coupon->update($data);

        return back()->with('success', 'Coupon updated.');
    }

    public function toggleEnabled(Coupon $coupon)
    {
        $coupon->update(['enabled' => !$coupon->enabled]);
        return response()->json(['enabled' => $coupon->enabled]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.ecommerce.coupons.index')->with('success', 'Coupon deleted.');
    }
}
