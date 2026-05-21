<?php

namespace App\Services\Payment\Webhooks;

use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class P24WebhookHandler extends AbstractWebhookHandler
{
    public function handle(Request $request, PaymentProvider $provider): Response
    {
        $crcKey = $provider->config['crc_key'] ?? null;

        if ($crcKey && ! $this->verifySignature($request->all(), $crcKey)) {
            Log::warning('P24 webhook: invalid signature');
            return response('Invalid signature', 400);
        }

        // P24 sends form-encoded fields
        $sessionId  = $request->input('p24_session_id');  // Our order_number
        $p24OrderId = $request->input('p24_order_id');
        $amount     = $request->input('p24_amount');
        $currency   = $request->input('p24_currency');

        Log::info('P24 webhook received', ['session_id' => $sessionId, 'p24_order_id' => $p24OrderId]);

        $payment = Payment::where('provider_payment_id', $p24OrderId)->first()
            ?? Payment::whereHas('order', fn ($q) => $q->where('order_number', $sessionId))->first();

        if (! $payment) {
            return response('OK', 200);
        }

        if (! $payment->provider_payment_id && $p24OrderId) {
            $payment->update(['provider_payment_id' => $p24OrderId]);
        }

        $this->applyCapture($payment, [
            'p24_order_id' => $p24OrderId,
            'p24_amount'   => $amount,
            'p24_currency' => $currency,
        ]);

        return response('OK', 200);
    }

    private function verifySignature(array $data, string $crc): bool
    {
        // P24 sign: MD5("session_id|order_id|amount|currency|crc_key")
        $sign = md5(implode('|', [
            $data['p24_session_id'] ?? '',
            $data['p24_order_id']   ?? '',
            $data['p24_amount']     ?? '',
            $data['p24_currency']   ?? '',
            $crc,
        ]));

        return hash_equals($sign, strtolower($data['p24_sign'] ?? ''));
    }
}
