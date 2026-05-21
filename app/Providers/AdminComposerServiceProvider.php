<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use App\Models\AdminNotification;

class AdminComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer(['pages.admin.*', 'includes.header'], function ($view) {
            $view->with('menus', Menu::with('sites')->get());

            $notifications = AdminNotification::latest()->limit(20)->get();
            $view->with('adminNotifications', $notifications);
            $view->with('notificationUnreadCount', $notifications->whereNull('read_at')->count());
        });
    }
}
