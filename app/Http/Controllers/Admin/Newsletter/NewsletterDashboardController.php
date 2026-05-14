<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterCampaign;
use App\Models\Newsletter\NewsletterList;
use App\Models\Newsletter\NewsletterSubscriber;
use App\Models\Newsletter\NewsletterTemplate;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class NewsletterDashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter', only: ['index']),
        ];
    }

    public function index()
    {
        $totalSubscribers        = NewsletterSubscriber::count();
        $activeSubscribers       = NewsletterSubscriber::active()->count();
        $pendingSubscribers      = NewsletterSubscriber::pending()->count();
        $unsubscribedSubscribers = NewsletterSubscriber::unsubscribed()->count();
        $totalLists              = NewsletterList::count();

        $totalTemplates  = NewsletterTemplate::count();
        $activeTemplates = NewsletterTemplate::active()->count();

        $totalCampaigns  = NewsletterCampaign::count();
        $draftCampaigns  = NewsletterCampaign::draft()->count();

        $recentSubscribers = NewsletterSubscriber::query()
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $recentCampaigns = NewsletterCampaign::query()
            ->with('template:id,name')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $recentTemplates = NewsletterTemplate::query()
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('pages.admin.newsletter.index', compact(
            'totalSubscribers',
            'activeSubscribers',
            'pendingSubscribers',
            'unsubscribedSubscribers',
            'totalLists',
            'totalTemplates',
            'activeTemplates',
            'totalCampaigns',
            'draftCampaigns',
            'recentSubscribers',
            'recentCampaigns',
            'recentTemplates',
        ));
    }
}
