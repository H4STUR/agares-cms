<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the maintenance mode setting is active
        $maintenanceMode = Setting::where('key', 'maintenance_mode')->value('value');

        // If maintenance mode is on and the user is not authenticated, show the maintenance page
        if ($maintenanceMode && !Auth::check()) {
            return response()->view('maintenance');
        }

        // Allow access if the user is logged in or maintenance mode is off
        return $next($request);
    }
}
