<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\PaymentProvider;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting;
use App\Models\Ecommerce\ShippingMethod;
use App\Models\Ecommerce\Coupon;
use App\Models\Ecommerce\TaxRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EcommerceDashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index']),
        ];
    }

    public function index()
    {
        $orderStatusCounts = Order::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $revenueThisMonth = Order::query()
            ->whereIn('status', ['processing', 'completed'])
            ->whereYear('placed_at', now()->year)
            ->whereMonth('placed_at', now()->month)
            ->sum('grand_total');

        $lastMonth = now()->subMonthNoOverflow();
        $revenueLastMonth = Order::query()
            ->whereIn('status', ['processing', 'completed'])
            ->whereYear('placed_at', $lastMonth->year)
            ->whereMonth('placed_at', $lastMonth->month)
            ->sum('grand_total');

        $recentOrders = Order::orderByDesc('id')->limit(7)->get();

        $productCount   = Product::count();
        $outOfStock     = Product::where('manage_stock', true)->where('stock', '<=', 0)->count();

        $paymentProviders = PaymentProvider::orderBy('driver')->get();

        $shippingEnabled  = ShippingMethod::where('enabled', true)->count();
        $shippingTotal    = ShippingMethod::count();

        $activeCoupons    = Coupon::where('enabled', true)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->count();

        $taxRuleCount = TaxRule::count();

        $shopSettings = Setting::pluck('value', 'key');

        return view('pages.admin.ecommerce.index', compact(
            'orderStatusCounts',
            'revenueThisMonth',
            'revenueLastMonth',
            'recentOrders',
            'productCount',
            'outOfStock',
            'paymentProviders',
            'shippingEnabled',
            'shippingTotal',
            'activeCoupons',
            'taxRuleCount',
            'shopSettings',
        ));
    }
}
