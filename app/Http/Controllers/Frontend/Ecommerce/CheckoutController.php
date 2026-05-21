<?php

namespace App\Http\Controllers\Frontend\Ecommerce;

use App\Http\Controllers\Frontend\PageController;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\OrderStatusHistory;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\PaymentProvider;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\Site;
use App\Mail\Ecommerce\NewOrderAlert;
use App\Mail\Ecommerce\OrderConfirmed;
use App\Services\AdminNotificationService;
use App\Models\User;
use App\Services\Payment\Gateways\P24Gateway;
use App\Services\Payment\Gateways\PayPalGateway;
use App\Services\Payment\Gateways\PayUGateway;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class CheckoutController extends PageController
{
    public function index()
    {
        $guestCheckout = (bool) EcommerceSetting::where('key', 'guest_checkout')->value('value');

        if (! auth()->check() && ! $guestCheckout) {
            return redirect()->route('login')->with('error', __('Please log in to place an order.'));
        }

        [$site, $inputs] = $this->loadShopSite();

        $cartItems = $this->resolveCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Your cart is empty.');
        }

        $providers     = PaymentProvider::where('enabled', true)->orderBy('id')->get();
        $allowRegister = (bool) EcommerceSetting::where('key', 'allow_register_at_checkout')->value('value');

        $data = array_merge([
            'site'                       => $site,
            'content_site'               => $inputs['byVar'],
            'content_site_list'          => $inputs['list'],
            'content'                    => $inputs['byVar'],
            'content_list'               => $inputs['list'],
            'cartItems'                  => $cartItems,
            'cartTotal'                  => $cartItems->sum(fn ($i) => $i['subtotal']),
            'providers'                  => $providers,
            'guest_checkout'             => $guestCheckout,
            'allow_register_at_checkout' => $allowRegister,
        ], $inputs['byVar']->all());

        return view('pages.frontend.ecommerce.checkout.index', compact('data'));
    }

    public function store(Request $request)
    {
        [$site, ] = $this->loadShopSite();

        $cartItems = $this->resolveCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Your cart is empty.');
        }

        $guestCheckout = (bool) EcommerceSetting::where('key', 'guest_checkout')->value('value');

        if (! auth()->check() && ! $guestCheckout) {
            return redirect()->route('login')->with('error', __('Please log in to place an order.'));
        }

        $allowRegister = (bool) EcommerceSetting::where('key', 'allow_register_at_checkout')->value('value');
        $willRegister  = $request->boolean('create_account') && $allowRegister && ! auth()->check();

        $requirePhone = (bool) EcommerceSetting::where('key', 'checkout_require_phone')->value('value');

        $rules = [
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => [$requirePhone ? 'required' : 'nullable', 'string', 'max:30'],
            'address'        => ['required', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
            'country'        => ['required', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string'],
        ];

        if ($willRegister) {
            $rules['email'][]  = Rule::unique('users', 'email');
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $provider = PaymentProvider::where('driver', $validated['payment_method'])
            ->where('enabled', true)
            ->first();

        if (! $provider) {
            return back()->withInput()->withErrors(['payment_method' => __('Selected payment method is not available.')]);
        }

        $billingAddress = [
            'name'     => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'address1' => $validated['address'],
            'city'     => $validated['city'],
            'postcode' => $validated['postal_code'],
            'country'  => $validated['country'],
            'phone'    => $validated['phone'] ?? null,
            'email'    => $validated['email'],
            'notes'    => $validated['notes'] ?? null,
        ];

        $subtotal    = $cartItems->sum('subtotal');
        $grandTotal  = $subtotal;
        $prefix      = EcommerceSetting::where('key', 'order_number_prefix')->value('value') ?? 'AG-';
        $orderNumber = $this->generateOrderNumber($prefix, now()->format('Ymd'));
        $isCod       = $provider->driver === 'cod';
        $status      = $isCod ? 'processing' : 'pending_payment';
        $currency    = EcommerceSetting::where('key', 'currency')->value('value') ?? 'PLN';

        $newUser = null;
        $payment = null;

        $order = DB::transaction(function () use (
            $site, $currency, $billingAddress, $status, $orderNumber,
            $grandTotal, $subtotal, $cartItems, $provider,
            $willRegister, $validated, &$newUser, &$payment
        ) {
            $order = Order::create([
                'site_id'         => $site->id,
                'user_id'         => auth()->id(),
                'order_number'    => $orderNumber,
                'status'          => $status,
                'payment_status'  => 'unpaid',
                'currency'        => $currency,
                'billing_address' => $billingAddress,
                'subtotal'        => $subtotal,
                'tax_total'       => 0,
                'shipping_total'  => 0,
                'discount_total'  => 0,
                'grand_total'     => $grandTotal,
                'placed_at'       => now(),
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product']->id,
                    'name'       => $item['product']->name,
                    'sku'        => $item['product']->sku,
                    'qty'        => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total'      => $item['subtotal'],
                ]);
            }

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => $status,
                'comment'     => $provider->driver === 'cod'
                    ? 'Order placed — Cash on Delivery'
                    : 'Order placed — awaiting payment',
                'changed_by'  => auth()->id(),
            ]);

            // Always create a payment record for every order
            $payment = Payment::create([
                'order_id'    => $order->id,
                'provider_id' => $provider->id,
                'status'      => 'pending',
                'amount'      => $grandTotal,
                'currency'    => $currency,
            ]);

            if ($willRegister) {
                $customerRole = Role::where('name', 'customer')->first();

                $newUser = User::create([
                    'name'     => $validated['first_name'],
                    'surname'  => $validated['last_name'],
                    'email'    => $validated['email'],
                    'password' => $validated['password'],
                    'phone'    => $validated['phone'] ?? null,
                    'role_id'  => $customerRole?->id,
                ]);

                $order->update(['user_id' => $newUser->id]);
            }

            return $order;
        });

        if ($newUser) {
            auth()->login($newUser);
        }

        session()->forget('cart');
        session()->put('confirmed_order_' . $order->order_number, true);

        // Emails
        $customerEmail = $order->billing_address['email'] ?? null;
        if ($customerEmail) {
            Mail::to($customerEmail)->send(new OrderConfirmed($order));
        }

        $adminEmail = EcommerceSetting::where('key', 'admin_email')->value('value');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewOrderAlert($order));
        }

        AdminNotificationService::newOrder($order);

        // COD: done — go straight to confirmation
        if ($provider->driver === 'cod') {
            return redirect()->route('shop.order.confirmation', $order->order_number);
        }

        // Online payment: hand off to the gateway
        $order->load('items');
        $gatewayUrl = $this->resolveGateway($provider)->initiatePayment($order, $payment, $provider);

        return redirect()->away($gatewayUrl);
    }

    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'payments.provider'])
            ->firstOrFail();

        $sessionKey  = 'confirmed_order_' . $orderNumber;
        $ownedByUser = auth()->check() && (int) $order->user_id === auth()->id();
        $hasSession  = session()->has($sessionKey);

        if (! $ownedByUser && ! $hasSession) {
            abort(403);
        }

        return view('pages.frontend.ecommerce.checkout.confirmation', compact('order'));
    }

    // -------------------------------------------------------------------------

    private function resolveGateway(PaymentProvider $provider): StripeGateway|PayUGateway|P24Gateway|PayPalGateway
    {
        return match ($provider->driver) {
            'stripe' => app(StripeGateway::class),
            'payu'   => app(PayUGateway::class),
            'p24'    => app(P24Gateway::class),
            'paypal' => app(PayPalGateway::class),
            default  => throw new \RuntimeException("No gateway for driver: {$provider->driver}"),
        };
    }

    private function generateOrderNumber(string $prefix, string $date): string
    {
        $count = Order::where('order_number', 'like', $prefix . $date . '%')->count();

        return $prefix . $date . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    private function loadShopSite(): array
    {
        $shopUrl = EcommerceSetting::where('key', 'shop_url')->value('value');
        if (! $shopUrl) {
            abort(404, 'Shop page is not configured.');
        }

        $site   = Site::where('slug', $shopUrl)->firstOrFail();
        $inputs = $this->loadOwnerInputs(Site::class, $site->id);

        return [$site, $inputs];
    }

    private function resolveCartItems()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($cart))
            ->where('status', 'published')
            ->with('defaultVariant')
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function ($qty, $productId) use ($products) {
            $product = $products->get($productId);
            if (! $product) {
                return null;
            }

            $price = $product->sale_price ?? $product->base_price;

            return [
                'product'  => $product,
                'quantity' => $qty,
                'price'    => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter()->values();
    }
}
