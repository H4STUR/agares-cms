<?php

namespace App\Services\Payment;

use App\Models\Ecommerce\OrderStatusHistory;
use App\Models\Ecommerce\Payment;
use Illuminate\Support\Facades\DB;

class PaymentTransitionService
{
    public static function capture(Payment $payment, array $meta = []): void
    {
        DB::transaction(function () use ($payment, $meta) {
            $payment->update([
                'status' => 'captured',
                'meta'   => array_merge($payment->meta ?? [], $meta),
            ]);

            $order = $payment->order;
            $old   = $order->status;

            $order->update([
                'status'         => 'processing',
                'payment_status' => 'paid',
            ]);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $old,
                'to_status'   => 'processing',
                'comment'     => 'Payment confirmed via ' . (optional($payment->provider)->driver ?? 'gateway'),
                'changed_by'  => null,
            ]);
        });
    }

    public static function fail(Payment $payment, array $meta = []): void
    {
        DB::transaction(function () use ($payment, $meta) {
            $payment->update([
                'status' => 'failed',
                'meta'   => array_merge($payment->meta ?? [], $meta),
            ]);

            $order = $payment->order;
            $old   = $order->status;

            $order->update(['status' => 'failed']);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $old,
                'to_status'   => 'failed',
                'comment'     => 'Payment failed via ' . (optional($payment->provider)->driver ?? 'gateway'),
                'changed_by'  => null,
            ]);
        });
    }
}
