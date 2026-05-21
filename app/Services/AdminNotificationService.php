<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Ecommerce\Order;

class AdminNotificationService
{
    public static function create(
        string $type,
        string $title,
        string $message,
        string $icon = 'notifications',
        string $iconColor = 'primary',
        ?string $url = null,
        array $data = []
    ): AdminNotification {
        return AdminNotification::create([
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'icon'       => $icon,
            'icon_color' => $iconColor,
            'url'        => $url,
            'data'       => $data ?: null,
        ]);
    }

    public static function newOrder(Order $order): AdminNotification
    {
        $name  = $order->billing_address['name'] ?? 'Customer';
        $total = number_format($order->grand_total, 2) . ' ' . ($order->currency ?? '');

        return self::create(
            type:      'new_order',
            title:     'New Order Received',
            message:   "#{$order->order_number} — {$name} ({$total})",
            icon:      'shopping_cart',
            iconColor: 'success',
            url:       route('admin.ecommerce.orders.show', $order),
            data:      ['order_id' => $order->id, 'order_number' => $order->order_number],
        );
    }

    // Future hooks — add methods here as features are built:
    // public static function newUser(User $user): AdminNotification { ... }
    // public static function newComment($comment): AdminNotification { ... }
    // public static function newFormSubmission(Form $form): AdminNotification { ... }
}
