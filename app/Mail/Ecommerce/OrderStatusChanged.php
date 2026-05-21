<?php

namespace App\Mail\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $fromStatus,
        public string $toStatus,
        public ?string $comment = null,
    ) {}

    public function build(): static
    {
        return $this
            ->subject(__('Order :number — Status Update', ['number' => $this->order->order_number]))
            ->view('emails.ecommerce.order-status-changed');
    }
}
