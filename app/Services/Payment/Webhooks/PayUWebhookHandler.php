<?php

namespace App\Services\Payment\Webhooks;

use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayUWebhookHandler extends AbstractWebhookHandler
{
    public function handle(Request $request, PaymentProvider $provider): Response
    {
        $md5Key    = $provider->config['md5_key'] ?? null;
        $sigHeader = $request->header('OpenPayU-Signature');

        if (! $md5Key) {
            Log::critical('PayU webhook: md5_key not configured — rejecting all requests');
            return response('Webhook not configured', 500);
        }

        if (! $sigHeader || ! $this->verifySignature($request->getContent(), $sigHeader, $md5Key)) {
            Log::warning('PayU webhook: invalid signature');
            return response('Invalid signature', 400);
        }

        $payload    = $request->json()->all();
        $payuOrder  = $payload['order'] ?? null;

        if (! $payuOrder) {
            return response('Missing order payload', 400);
        }

        $payuOrderId = $payuOrder['orderId']    ?? null; // PayU internal ID
        $extOrderId  = $payuOrder['extOrderId'] ?? null; // Our order_number
        $status      = $payuOrder['status']     ?? null;

        Log::info('PayU webhook received', ['status' => $status, 'extOrderId' => $extOrderId]);

        // Find payment by provider_payment_id (if already set) or by order number
        $payment = Payment::where('provider_payment_id', $payuOrderId)->first()
            ?? Payment::whereHas('order', fn ($q) => $q->where('order_number', $extOrderId))->first();

        if (! $payment) {
            return response('OK', 200); // 200 to stop retries
        }

        // Back-fill the provider payment ID if we found by order number
        if (! $payment->provider_payment_id && $payuOrderId) {
            $payment->update(['provider_payment_id' => $payuOrderId]);
        }

        match ($status) {
            'COMPLETED'          => $this->applyCapture($payment, ['payu_status' => $status, 'payu_order_id' => $payuOrderId]),
            'CANCELED', 'REJECTED' => $this->applyFailure($payment, ['payu_status' => $status]),
            default              => null,
        };

        return response('OK', 200);
    }

    private function verifySignature(string $body, string $sigHeader, string $md5Key): bool
    {
        // Header format: signature=xxx;algorithm=MD5;sender=...
        $parts = [];
        foreach (explode(';', $sigHeader) as $part) {
            if (str_contains($part, '=')) {
                [$k, $v] = explode('=', $part, 2);
                $parts[trim($k)] = trim($v);
            }
        }

        $expected = md5($body . $md5Key);
        return hash_equals($expected, strtolower($parts['signature'] ?? ''));
    }
}
