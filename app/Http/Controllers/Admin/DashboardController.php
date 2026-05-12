<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ga4AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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

        // dd([
        //     'GA4_PROPERTY_ID' => config('services.ga4.property_id'),
        //     'GA4_CREDENTIALS_PATH' => config('services.ga4.credentials_path'),
        //     'file_exists' => file_exists(base_path(config('services.ga4.credentials_path'))),
        //     ]);


        $analytics = $ga4->summaryLast7Days();

        // Timeline chart (last 30 days)
        $trafficTimeline = $ga4->trafficTimelineLast12Months();

        // Realtime active users (last 30 minutes window)
        $realtime = $ga4->realtimeActiveUsers();

        return view('pages.admin.dashboard', compact('analytics', 'trafficTimeline', 'realtime'));
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
