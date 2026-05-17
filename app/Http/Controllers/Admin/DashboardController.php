<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcomSetting;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ga4AnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage dashboard', only: ['index', 'realtimeUsers']),
            new Middleware('can:view API', only: ['documentationAPI']),
        ];
    }

    public function index(Ga4AnalyticsService $ga4)
    {
        $analytics = $ga4->summaryLast7Days();
        $trafficTimeline = $ga4->trafficTimelineLast12Months();
        $realtime = $ga4->realtimeActiveUsers();

        $ecommerce = Setting::bool('enable_ecommerce') ? $this->ecommerceMetrics() : null;

        return view('pages.admin.dashboard', compact('analytics', 'trafficTimeline', 'realtime', 'ecommerce'));
    }

    /**
     * Aggregates everything the ecommerce section of the dashboard needs.
     */
    private function ecommerceMetrics(): array
    {
        $revenueStatuses = ['processing', 'completed'];
        $now = CarbonImmutable::now();

        $shopSettings = EcomSetting::pluck('value', 'key');
        $currency = $shopSettings->get('currency', 'USD');
        $salesGoal = (float) ($shopSettings->get('sales_goal', 0));

        // --- KPI strip ---------------------------------------------------------
        $totalOrders   = Order::count();
        $totalIncome   = (float) Order::whereIn('status', $revenueStatuses)->sum('grand_total');
        $pendingOrders = Order::where('status', 'pending_payment')->count();
        $totalPayments = (float) Payment::where('status', 'captured')->sum('amount');

        // --- Average Weekly Sales (last 7d vs prior 7d) + sparkline ------------
        $weekStart = $now->subDays(6)->startOfDay();
        $prevStart = $now->subDays(13)->startOfDay();
        $prevEnd   = $now->subDays(7)->endOfDay();

        $revenueLast7 = (float) Order::whereIn('status', $revenueStatuses)
            ->whereBetween('placed_at', [$weekStart, $now])
            ->sum('grand_total');
        $revenuePrev7 = (float) Order::whereIn('status', $revenueStatuses)
            ->whereBetween('placed_at', [$prevStart, $prevEnd])
            ->sum('grand_total');

        $avgWeeklySales = $revenueLast7 / 7.0;
        $weekDelta = $revenuePrev7 > 0
            ? (($revenueLast7 - $revenuePrev7) / $revenuePrev7) * 100
            : null;

        $weeklySparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->subDays($i);
            $weeklySparkline[] = (float) Order::whereIn('status', $revenueStatuses)
                ->whereDate('placed_at', $day->toDateString())
                ->sum('grand_total');
        }

        // --- Total Users / Active Users (= customers with orders) --------------
        $totalUsers = User::count();
        $totalUsersPrevMonth = User::where('created_at', '<', $now->subMonthNoOverflow()->startOfMonth())->count();
        $usersDelta = $totalUsersPrevMonth > 0
            ? (($totalUsers - $totalUsersPrevMonth) / $totalUsersPrevMonth) * 100
            : null;

        $activeCustomers = Order::whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $activeCustomersPrev = Order::whereNotNull('user_id')
            ->where('placed_at', '<', $now->subMonthNoOverflow()->startOfMonth())
            ->distinct('user_id')->count('user_id');
        $activeCustomersDelta = $activeCustomers - $activeCustomersPrev;

        // --- Sales this year + goal --------------------------------------------
        $salesYTD = (float) Order::whereIn('status', $revenueStatuses)
            ->whereYear('placed_at', $now->year)
            ->sum('grand_total');
        $salesYTDPrev = (float) Order::whereIn('status', $revenueStatuses)
            ->whereYear('placed_at', $now->year - 1)
            ->sum('grand_total');
        $salesYTDDelta = $salesYTDPrev > 0
            ? (($salesYTD - $salesYTDPrev) / $salesYTDPrev) * 100
            : null;
        $goalPercent = $salesGoal > 0 ? min(100, ($salesYTD / $salesGoal) * 100) : 0;
        $goalRemaining = max(0, $salesGoal - $salesYTD);

        // --- Sales & Orders chart (last 9 months) ------------------------------
        $months = [];
        $monthlyRevenue = [];
        $monthlyOrders  = [];
        for ($i = 8; $i >= 0; $i--) {
            $m = $now->subMonthsNoOverflow($i);
            $months[] = $m->format('M');
            $monthlyRevenue[] = (float) Order::whereIn('status', $revenueStatuses)
                ->whereYear('placed_at', $m->year)
                ->whereMonth('placed_at', $m->month)
                ->sum('grand_total');
            $monthlyOrders[] = (int) Order::whereYear('placed_at', $m->year)
                ->whereMonth('placed_at', $m->month)
                ->count();
        }
        $monthlyTotal     = (int) array_sum($monthlyOrders);
        $monthlyRevTotal  = (float) array_sum($monthlyRevenue);
        $yearlyTotal      = (int) Order::whereYear('placed_at', $now->year)->count();
        $yearlyRevTotal   = $salesYTD;

        // --- Recent transactions (latest payments) -----------------------------
        $recentPayments = Payment::with('provider')
            ->whereIn('status', ['captured', 'authorized', 'refunded'])
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        // --- Popular products (top sellers by qty) -----------------------------
        $popularProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(qty) as sold'), DB::raw('AVG(unit_price) as avg_price'), DB::raw('MAX(name) as item_name'))
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('sold')
            ->limit(7)
            ->get();

        $productIds = $popularProducts->pluck('product_id')->all();
        $products = Product::with(['defaultVariant.image'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return [
            'currency'              => $currency,
            'totalOrders'           => $totalOrders,
            'totalIncome'           => $totalIncome,
            'pendingOrders'         => $pendingOrders,
            'totalPayments'         => $totalPayments,

            'avgWeeklySales'        => $avgWeeklySales,
            'weekDelta'             => $weekDelta,
            'weeklySparkline'       => $weeklySparkline,

            'totalUsers'            => $totalUsers,
            'usersDelta'            => $usersDelta,
            'activeCustomers'       => $activeCustomers,
            'activeCustomersDelta'  => $activeCustomersDelta,

            'salesYTD'              => $salesYTD,
            'salesYTDDelta'         => $salesYTDDelta,
            'salesGoal'             => $salesGoal,
            'goalPercent'           => $goalPercent,
            'goalRemaining'         => $goalRemaining,

            'months'                => $months,
            'monthlyRevenue'        => $monthlyRevenue,
            'monthlyOrders'         => $monthlyOrders,
            'monthlyTotal'          => $monthlyTotal,
            'monthlyRevTotal'       => $monthlyRevTotal,
            'yearlyTotal'           => $yearlyTotal,
            'yearlyRevTotal'        => $yearlyRevTotal,

            'recentPayments'        => $recentPayments,
            'popularProducts'       => $popularProducts,
            'products'              => $products,
        ];
    }

    // Ajax endpoint for live counter refresh
    public function realtimeUsers(Ga4AnalyticsService $ga4): JsonResponse
    {
        return response()->json($ga4->realtimeActiveUsers());
    }

    /**
     * Display CMS documentation for admins and developers
     */
    public function infoCMS()
    {
        return view('pages.admin.documentation.info');
    }

    public function documentationCMS()
    {
        return view('pages.admin.documentation.cms');
    }

    public function documentationAPI()
    {
        return view('pages.admin.documentation.api');
    }
}
