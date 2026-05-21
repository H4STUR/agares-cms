<?php

namespace App\Mail\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build(): static
    {
        $this->order->loadMissing(['items', 'payments.provider']);

        return $this
            ->subject(__('Order Confirmed — :number', ['number' => $this->order->order_number]))
            ->view('emails.ecommerce.order-confirmed');
    }
}
