<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PaymentProviderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index']),
            new Middleware('can:manage ecommerce', only: ['edit', 'update']),
        ];
    }

    private const DEFAULTS = [
        [
            'driver'  => 'stripe',
            'enabled' => false,
            'config'  => ['publishable_key' => '', 'secret_key' => '', 'webhook_secret' => '', 'mode' => 'test'],
        ],
        [
            'driver'  => 'payu',
            'enabled' => false,
            'config'  => ['pos_id' => '', 'client_id' => '', 'client_secret' => '', 'md5_key' => '', 'sandbox' => '1'],
        ],
        [
            'driver'  => 'p24',
            'enabled' => false,
            'config'  => ['merchant_id' => '', 'pos_id' => '', 'crc_key' => '', 'sandbox' => '1'],
        ],
        [
            'driver'  => 'paypal',
            'enabled' => false,
            'config'  => ['client_id' => '', 'client_secret' => '', 'mode' => 'sandbox'],
        ],
        [
            'driver'  => 'cod',
            'enabled' => false,
            'config'  => [],
        ],
    ];

    public function index()
    {
        if (PaymentProvider::count() === 0) {
            foreach (self::DEFAULTS as $d) {
                PaymentProvider::create($d);
            }
        }

        $providers = PaymentProvider::orderBy('id')->get();

        return view('pages.admin.ecommerce.payment-providers.index', compact('providers'));
    }

    public function edit(PaymentProvider $paymentProvider)
    {
        return view('pages.admin.ecommerce.payment-providers.edit', compact('paymentProvider'));
    }

    public function update(Request $request, PaymentProvider $paymentProvider)
    {
        // Toggle-only call from index (no config fields submitted)
        if ($request->has('enabled') && ! $request->has('config')) {
            $paymentProvider->update(['enabled' => (bool) $request->input('enabled')]);

            return back()->with('success', __('Provider updated.'));
        }

        // Full config-form save from edit page
        $existing = $paymentProvider->config ?? [];
        $incoming = $request->input('config', []);

        // Strip keys not relevant to this driver (only keep keys that exist in defaults)
        $merged = array_merge($existing, array_filter($incoming, fn ($v) => $v !== null));

        $paymentProvider->update([
            'enabled' => (bool) $request->input('enabled', $paymentProvider->enabled),
            'config'  => $merged,
        ]);

        return redirect()->route('admin.ecommerce.payment-providers.index')
            ->with('success', __('Payment provider saved.'));
    }
}
