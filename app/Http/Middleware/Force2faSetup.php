<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Force2faSetup
{
    public function __construct(private readonly TwoFactorService $service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Always allow the enrolment flow and the logout endpoint.
        if ($request->routeIs('two-factor.*', 'logout')) {
            return $next($request);
        }

        $mustEnrol   = $this->service->mustEnrol($user);
        $mustReEnrol = !$mustEnrol && $this->service->mustReEnrol($user);

        if (!$mustEnrol && !$mustReEnrol) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Two-factor authentication setup is required.');
        }

        $message = $mustReEnrol
            ? __('Two-factor settings have changed. Please set up your account again with an allowed method.')
            : __('Two-factor authentication is required for your account. Please complete setup to continue.');

        return redirect()
            ->route('two-factor.setup')
            ->with('warning', $message);
    }
}
