<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\PaymentProvider;
use App\Services\Payment\Webhooks\P24WebhookHandler;
use App\Services\Payment\Webhooks\PayPalWebhookHandler;
use App\Services\Payment\Webhooks\PayUWebhookHandler;
use App\Services\Payment\Webhooks\StripeWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    private array $handlers = [
        'stripe' => StripeWebhookHandler::class,
        'payu'   => PayUWebhookHandler::class,
        'p24'    => P24WebhookHandler::class,
        'paypal' => PayPalWebhookHandler::class,
    ];

    public function handle(string $driver, Request $request): Response
    {
        Log::info("Webhook received for driver: {$driver}", [
            'ip'          => $request->ip(),
            'content_type'=> $request->header('Content-Type'),
        ]);

        $provider = PaymentProvider::where('driver', $driver)
            ->where('enabled', true)
            ->first();

        if (! $provider) {
            Log::warning("Webhook: provider '{$driver}' not found or disabled");
            return response('Provider not available', 404);
        }

        if (! isset($this->handlers[$driver])) {
            Log::warning("Webhook: no handler registered for driver '{$driver}'");
            return response('Driver not supported', 400);
        }

        /** @var \App\Services\Payment\Webhooks\AbstractWebhookHandler $handler */
        $handler = app($this->handlers[$driver]);

        return $handler->handle($request, $provider);
    }
}
