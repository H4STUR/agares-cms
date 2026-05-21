<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\PaymentProvider;
use App\Services\Payment\Gateways\PayPalGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\PaymentTransitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentReturnController extends Controller
{
    public function handle(string $driver, Request $request): RedirectResponse
    {
        $orderNumber = $request->query('order');
        $order       = Order::where('order_number', $orderNumber)
            ->with(['payments.provider'])
            ->firstOrFail();

        $provider = PaymentProvider::where('driver', $driver)
            ->where('enabled', true)
            ->firstOrFail();

        try {
            match ($driver) {
                'stripe' => $this->handleStripe($request, $order, $provider),
                'paypal' => $this->handlePayPal($request, $order, $provider),
                default  => null, // PayU / P24 confirm via webhook; just redirect
            };
        } catch (Throwable $e) {
            Log::error("Payment return error [{$driver}]", ['message' => $e->getMessage(), 'order' => $orderNumber]);
        }

        // Only grant session access to the confirmation page if the caller owns the order.
        // Authenticated orders: user must match. Guest orders (user_id null): any visitor
        // landing on the return URL is considered the purchaser (no better token available).
        if ($order->user_id !== null) {
            abort_if(
                ! auth()->check() || (int) $order->user_id !== auth()->id(),
                403
            );
        }

        session()->put('confirmed_order_' . $orderNumber, true);

        return redirect()->route('shop.order.confirmation', $orderNumber);
    }

    // -------------------------------------------------------------------------

    private function handleStripe(Request $request, Order $order, PaymentProvider $provider): void
    {
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return;
        }

        $session = app(StripeGateway::class)->retrieveSession($sessionId, $provider);

        if ($session->payment_status !== 'paid') {
            return;
        }

        // The webhook fires `payment_intent.succeeded` shortly after — this catches the
        // race condition where the customer lands back before the webhook arrives.
        $payment = $order->payments
            ->where('provider_payment_id', $session->payment_intent)
            ->first();

        if ($payment && $payment->status !== 'captured') {
            PaymentTransitionService::capture($payment, ['stripe_session_id' => $sessionId]);
        }
    }

    private function handlePayPal(Request $request, Order $order, PaymentProvider $provider): void
    {
        // PayPal passes ?token=PAYPAL_ORDER_ID&PayerID=xxx on return
        $paypalOrderId = $request->query('token');
        if (! $paypalOrderId) {
            return;
        }

        $payment = $order->payments
            ->where('provider_payment_id', $paypalOrderId)
            ->first();

        if (! $payment || $payment->status === 'captured') {
            return;
        }

        $result = app(PayPalGateway::class)->captureOrder($paypalOrderId, $provider);

        $captureStatus = $result['status'] ?? null;

        if ($captureStatus === 'COMPLETED') {
            $captureId = $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
            PaymentTransitionService::capture($payment, [
                'paypal_capture_id' => $captureId,
                'paypal_status'     => $captureStatus,
            ]);
        } else {
            PaymentTransitionService::fail($payment, ['paypal_status' => $captureStatus]);
        }
    }
}
