<?php

namespace App\Services\Payment\Webhooks;

use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookHandler extends AbstractWebhookHandler
{
    public function handle(Request $request, PaymentProvider $provider): Response
    {
        $clientId = $provider->config['client_id'] ?? null;
        if (! $clientId) {
            Log::warning('PayPal webhook received but provider not configured');
            return response('Provider not configured', 400);
        }

        if (! $this->verifySignature($request, $provider)) {
            Log::warning('PayPal webhook: signature verification failed');
            return response('Invalid signature', 400);
        }

        $payload   = $request->json()->all();
        $eventType = $payload['event_type'] ?? null;
        $resource  = $payload['resource']   ?? [];
        $captureId = $resource['id']        ?? null;

        Log::info('PayPal webhook received', ['event_type' => $eventType]);

        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $payment = Payment::where('provider_payment_id', $captureId)->first();
                if ($payment) {
                    $this->applyCapture($payment, [
                        'paypal_event'   => $eventType,
                        'paypal_capture' => $captureId,
                    ]);
                }
                break;

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                $payment = Payment::where('provider_payment_id', $captureId)->first();
                if ($payment) {
                    $this->applyFailure($payment, [
                        'paypal_event'   => $eventType,
                        'paypal_capture' => $captureId,
                    ]);
                }
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
                $payment = Payment::where('provider_payment_id', $captureId)->first();
                if ($payment) {
                    $payment->update(['status' => 'refunded']);
                    $payment->order->update(['payment_status' => 'refunded']);
                }
                break;
        }

        return response('OK', 200);
    }

    private function verifySignature(Request $request, PaymentProvider $provider): bool
    {
        $transmissionId  = $request->header('PayPal-Transmission-Id');
        $timestamp       = $request->header('PayPal-Transmission-Time');
        $certUrl         = $request->header('PayPal-Cert-Url');
        $authAlgo        = $request->header('PayPal-Auth-Algo');
        $transmissionSig = $request->header('PayPal-Transmission-Sig');
        $webhookId       = $provider->config['webhook_id'] ?? null;

        if (! $transmissionId || ! $timestamp || ! $certUrl || ! $authAlgo || ! $transmissionSig) {
            Log::warning('PayPal webhook: missing required signature headers');
            return false;
        }

        if (! $webhookId) {
            Log::critical('PayPal webhook: webhook_id not configured in provider settings — cannot verify signature');
            return false;
        }

        // Validate cert URL is from PayPal's domain (prevents SSRF / cert spoofing)
        $parsed = parse_url($certUrl);
        $host   = $parsed['host'] ?? '';
        $scheme = $parsed['scheme'] ?? '';

        if ($scheme !== 'https' || ! preg_match('/(?:^|\.)paypal\.com$/', $host)) {
            Log::warning('PayPal webhook: cert URL not from paypal.com', ['url' => $certUrl]);
            return false;
        }

        $certPem = @file_get_contents($certUrl);
        if (! $certPem) {
            Log::warning('PayPal webhook: failed to fetch cert', ['url' => $certUrl]);
            return false;
        }

        $pubKey = openssl_get_publickey($certPem);
        if (! $pubKey) {
            Log::warning('PayPal webhook: invalid or unparseable cert');
            return false;
        }

        $crc32   = crc32($request->getContent());
        $message = "{$transmissionId}|{$timestamp}|{$webhookId}|{$crc32}";

        $algoMap = [
            'SHA256withRSA' => OPENSSL_ALGO_SHA256,
            'SHA1withRSA'   => OPENSSL_ALGO_SHA1,
        ];
        $algo = $algoMap[$authAlgo] ?? OPENSSL_ALGO_SHA256;
        $sig  = base64_decode($transmissionSig, true);

        return $sig !== false && openssl_verify($message, $sig, $pubKey, $algo) === 1;
    }
}
