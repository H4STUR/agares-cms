<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TwoFactorAdminController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TwoFactorService $service)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('can:manage users'),
        ];
    }

    public function reset(User $user): RedirectResponse
    {
        $this->service->disable($user, byAdmin: true);

        return back()->with('success', __('Two-factor authentication has been reset for :name.', [
            'name' => $user->name ?: $user->email,
        ]));
    }
}
