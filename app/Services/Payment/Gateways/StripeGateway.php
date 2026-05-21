<?php

namespace App\Services\Payment\Gateways;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeGateway
{
    public function initiatePayment(Order $order, Payment $payment, PaymentProvider $provider): string
    {
        Stripe::setApiKey($provider->config['secret_key'] ?? '');

        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency'     => strtolower($order->currency),
                'product_data' => ['name' => $item->name],
                'unit_amount'  => (int) round($item->unit_price * 100),
            ],
            'quantity' => $item->qty,
        ])->values()->toArray();

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'customer_email'       => $order->billing_address['email'] ?? null,
            'metadata'             => ['order_number' => $order->order_number],
            'success_url'          => route('shop.payment.return', 'stripe')
                . '?session_id={CHECKOUT_SESSION_ID}'
                . '&order=' . $order->order_number,
            'cancel_url'           => route('shop.checkout'),
        ]);

        // Store the PaymentIntent ID — matches what payment_intent.succeeded webhook sends
        if ($session->payment_intent) {
            $payment->update([
                'provider_payment_id' => $session->payment_intent,
                'meta'                => array_merge($payment->meta ?? [], ['stripe_session_id' => $session->id]),
            ]);
        }

        return $session->url;
    }

    public function retrieveSession(string $sessionId, PaymentProvider $provider): Session
    {
        Stripe::setApiKey($provider->config['secret_key'] ?? '');

        return Session::retrieve($sessionId);
    }
}
