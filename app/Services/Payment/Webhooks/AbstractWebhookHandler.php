<?php

namespace App\Services\Payment\Webhooks;

use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use App\Services\Payment\PaymentTransitionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class AbstractWebhookHandler
{
    abstract public function handle(Request $request, PaymentProvider $provider): Response;

    protected function applyCapture(Payment $payment, array $meta = []): void
    {
        PaymentTransitionService::capture($payment, $meta);
    }

    protected function applyFailure(Payment $payment, array $meta = []): void
    {
        PaymentTransitionService::fail($payment, $meta);
    }
}
