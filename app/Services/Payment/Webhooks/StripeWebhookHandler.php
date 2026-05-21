<?php

namespace App\Services\Payment\Webhooks;

use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookHandler extends AbstractWebhookHandler
{
    public function handle(Request $request, PaymentProvider $provider): Response
    {
        $webhookSecret = $provider->config['webhook_secret'] ?? null;
        $sigHeader     = $request->header('Stripe-Signature');

        if (! $webhookSecret) {
            Log::critical('Stripe webhook: webhook_secret not configured — rejecting all requests');
            return response('Webhook not configured', 500);
        }

        if (! $sigHeader || ! $this->verifySignature($request->getContent(), $sigHeader, $webhookSecret)) {
            Log::warning('Stripe webhook: invalid signature');
            return response('Invalid signature', 400);
        }

        $payload   = $request->json()->all();
        $eventType = $payload['type'] ?? null;
        $object    = $payload['data']['object'] ?? [];

        Log::info('Stripe webhook received', ['type' => $eventType]);

        switch ($eventType) {
            // Checkout Session completed — primary event when using Checkout Sessions API
            case 'checkout.session.completed':
                if (($object['payment_status'] ?? null) === 'paid') {
                    // Look up by PaymentIntent ID (stored at session creation time)
                    $piId    = $object['payment_intent'] ?? null;
                    $payment = Payment::where('provider_payment_id', $piId)->first();
                    if ($payment && $payment->status !== 'captured') {
                        $this->applyCapture($payment, [
                            'stripe_event_id'   => $payload['id'] ?? null,
                            'stripe_session_id' => $object['id'] ?? null,
                        ]);
                    }
                }
                break;

            case 'payment_intent.succeeded':
                $payment = Payment::where('provider_payment_id', $object['id'] ?? null)->first();
                if ($payment && $payment->status !== 'captured') {
                    $this->applyCapture($payment, [
                        'stripe_event_id'        => $payload['id'] ?? null,
                        'stripe_payment_intent'  => $object['id'] ?? null,
                    ]);
                }
                break;

            case 'payment_intent.payment_failed':
                $payment = Payment::where('provider_payment_id', $object['id'] ?? null)->first();
                if ($payment) {
                    $this->applyFailure($payment, [
                        'stripe_event_id'  => $payload['id'] ?? null,
                        'failure_message'  => $object['last_payment_error']['message'] ?? null,
                    ]);
                }
                break;

            case 'charge.refunded':
                $paymentIntentId = $object['payment_intent'] ?? null;
                $payment = Payment::where('provider_payment_id', $paymentIntentId)->first();
                if ($payment) {
                    $payment->update(['status' => 'refunded']);
                    $payment->order->update(['payment_status' => 'refunded']);
                }
                break;
        }

        return response('OK', 200);
    }

    private function verifySignature(string $payload, string $sigHeader, string $secret): bool
    {
        // Parse "t=timestamp,v1=sig1,v1=sig2"
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$k][] = $v;
        }

        $timestamp  = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if (! $timestamp || empty($signatures)) {
            return false;
        }

        // Reject events older than 5 minutes
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }

        return false;
    }
}
