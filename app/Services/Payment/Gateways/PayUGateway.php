<?php

namespace App\Services\Payment\Gateways;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayUGateway
{
    public function initiatePayment(Order $order, Payment $payment, PaymentProvider $provider): string
    {
        $sandbox = (bool) ($provider->config['sandbox'] ?? '1');
        $baseUrl = $sandbox
            ? 'https://secure.snd.payu.com'
            : 'https://secure.payu.com';

        $accessToken = $this->getAccessToken($baseUrl, $provider);

        $nameParts = explode(' ', $order->billing_address['name'] ?? '', 2);

        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/api/v2_1/orders", [
                'notifyUrl'     => route('shop.payment.webhook', 'payu'),
                'continueUrl'   => route('shop.payment.return', 'payu') . '?order=' . $order->order_number,
                'customerIp'    => request()->ip() ?? '127.0.0.1',
                'merchantPosId' => $provider->config['pos_id'] ?? '',
                'description'   => 'Order ' . $order->order_number,
                'currencyCode'  => $order->currency,
                'totalAmount'   => (string) (int) round($order->grand_total * 100),
                'extOrderId'    => $order->order_number,
                'buyer'         => [
                    'email'     => $order->billing_address['email'] ?? '',
                    'firstName' => $nameParts[0] ?? '',
                    'lastName'  => $nameParts[1] ?? '',
                    'phone'     => $order->billing_address['phone'] ?? '',
                ],
                'products' => $order->items->map(fn ($item) => [
                    'name'      => $item->name,
                    'unitPrice' => (string) (int) round($item->unit_price * 100),
                    'quantity'  => (string) $item->qty,
                ])->toArray(),
            ]);

        if ($response->failed()) {
            Log::error('PayU order creation failed', ['body' => $response->body()]);
            throw new RuntimeException('PayU payment initiation failed.');
        }

        $payuOrderId  = $response->json('orderId');
        $redirectUri  = $response->json('redirectUri');

        $payment->update(['provider_payment_id' => $payuOrderId]);

        return $redirectUri;
    }

    private function getAccessToken(string $baseUrl, PaymentProvider $provider): string
    {
        $response = Http::asForm()->post("{$baseUrl}/pl/standard/user/oauth/authorize", [
            'grant_type'    => 'client_credentials',
            'client_id'     => $provider->config['client_id']     ?? '',
            'client_secret' => $provider->config['client_secret'] ?? '',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('PayU OAuth token request failed.');
        }

        return $response->json('access_token');
    }
}
