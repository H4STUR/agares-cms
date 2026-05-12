<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Mail\Ecommerce\OrderStatusChanged;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view orders', only: ['index', 'show']),
            new Middleware('can:manage orders', only: ['updateStatus']),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->get('status');

        $q = Order::query();

        if ($status) {
            $q->where('status', $status);
        }

        $orders = $q->orderByDesc('id')->paginate(25)->withQueryString();

        return view('pages.admin.ecommerce.orders.index', compact('orders', 'status'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'statusHistory', 'payments.provider']);

        return view('pages.admin.ecommerce.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status'  => ['required', Rule::in(['pending_payment', 'processing', 'on_hold', 'completed', 'cancelled', 'refunded', 'failed'])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $old = $order->status;

        $order->update(['status' => $validated['status']]);

        OrderStatusHistory::create([
            'order_id'    => $order->id,
            'from_status' => $old,
            'to_status'   => $validated['status'],
            'comment'     => $validated['comment'] ?? null,
            'changed_by'  => auth()->id(),
        ]);

        $customerEmail = $order->billing_address['email'] ?? null;
        if ($customerEmail) {
            Mail::to($customerEmail)->send(
                new OrderStatusChanged($order, $old, $validated['status'], $validated['comment'] ?? null)
            );
        }

        return back()->with('success', __('Order status updated.'));
    }
}
