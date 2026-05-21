<?php

namespace App\Services\Payment\Gateways;

use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalGateway
{
    public function initiatePayment(Order $order, Payment $payment, PaymentProvider $provider): string
    {
        $baseUrl     = $this->baseUrl($provider);
        $accessToken = $this->getAccessToken($baseUrl, $provider);

        $response = Http::withToken($accessToken)
            ->withHeaders(['PayPal-Request-Id' => $order->order_number . '-init'])
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent'          => 'CAPTURE',
                'purchase_units'  => [[
                    'reference_id' => $order->order_number,
                    'amount'       => [
                        'currency_code' => $order->currency,
                        'value'         => number_format($order->grand_total, 2, '.', ''),
                    ],
                    'description' => 'Order ' . $order->order_number,
                ]],
                'application_context' => [
                    'return_url'  => route('shop.payment.return', 'paypal') . '?order=' . $order->order_number,
                    'cancel_url'  => route('shop.checkout'),
                    'brand_name'  => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayPal order creation failed', ['body' => $response->body()]);
            throw new RuntimeException('PayPal payment initiation failed.');
        }

        $paypalOrderId = $response->json('id');
        $approveLink   = collect($response->json('links'))->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $approveLink) {
            throw new RuntimeException('PayPal did not return an approval URL.');
        }

        $payment->update(['provider_payment_id' => $paypalOrderId]);

        return $approveLink;
    }

    public function captureOrder(string $paypalOrderId, PaymentProvider $provider): array
    {
        $baseUrl     = $this->baseUrl($provider);
        $accessToken = $this->getAccessToken($baseUrl, $provider);

        $response = Http::withToken($accessToken)
            ->withHeaders(['PayPal-Request-Id' => $paypalOrderId . '-capture'])
            ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->failed()) {
            Log::error('PayPal capture failed', ['body' => $response->body()]);
            throw new RuntimeException('PayPal capture failed.');
        }

        return $response->json();
    }

    private function getAccessToken(string $baseUrl, PaymentProvider $provider): string
    {
        $response = Http::withBasicAuth(
            $provider->config['client_id']     ?? '',
            $provider->config['client_secret'] ?? ''
        )->asForm()->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new RuntimeException('PayPal OAuth token request failed.');
        }

        return $response->json('access_token');
    }

    private function baseUrl(PaymentProvider $provider): string
    {
        $live = ($provider->config['mode'] ?? 'sandbox') === 'live';
        return $live ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }
}
