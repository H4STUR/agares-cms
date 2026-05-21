<?php

namespace App\Services\Payment\Gateways;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class P24Gateway
{
    public function initiatePayment(Order $order, Payment $payment, PaymentProvider $provider): string
    {
        $sandbox    = (bool) ($provider->config['sandbox'] ?? '1');
        $baseUrl    = $sandbox
            ? 'https://sandbox.przelewy24.pl'
            : 'https://secure.przelewy24.pl';

        $merchantId = (int) ($provider->config['merchant_id'] ?? 0);
        $posId      = (int) ($provider->config['pos_id']      ?: $merchantId);
        $crcKey     = $provider->config['crc_key'] ?? '';

        $amount   = (int) round($order->grand_total * 100);
        $currency = $order->currency;

        $sign = hash('sha384', json_encode([
            'sessionId'  => $order->order_number,
            'merchantId' => $merchantId,
            'amount'     => $amount,
            'currency'   => $currency,
            'crc'        => $crcKey,
        ], JSON_UNESCAPED_UNICODE));

        $response = Http::withBasicAuth((string) $posId, $crcKey)
            ->post("{$baseUrl}/api/v1/transaction/register", [
                'merchantId'  => $merchantId,
                'posId'       => $posId,
                'sessionId'   => $order->order_number,
                'amount'      => $amount,
                'currency'    => $currency,
                'description' => 'Order ' . $order->order_number,
                'email'       => $order->billing_address['email'] ?? '',
                'urlReturn'   => route('shop.payment.return', 'p24') . '?order=' . $order->order_number,
                'urlStatus'   => route('shop.payment.webhook', 'p24'),
                'sign'        => $sign,
                'encoding'    => 'UTF-8',
            ]);

        if ($response->failed()) {
            Log::error('P24 transaction register failed', ['body' => $response->body()]);
            throw new RuntimeException('P24 payment initiation failed.');
        }

        $token = $response->json('data.token');

        $payment->update(['provider_payment_id' => $token]);

        return "{$baseUrl}/trnRequest/{$token}";
    }
}
