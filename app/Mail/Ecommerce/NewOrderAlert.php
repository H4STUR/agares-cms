<?php

namespace App\Mail\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrderAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build(): static
    {
        $this->order->loadMissing(['items']);

        return $this
            ->subject('[New Order] ' . $this->order->order_number . ' — ' . number_format($this->order->grand_total, 2) . ' ' . $this->order->currency)
            ->view('emails.ecommerce.new-order-alert');
    }
}
