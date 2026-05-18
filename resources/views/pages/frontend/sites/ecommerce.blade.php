@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Ecommerce page extras ============ */

  /* Gateway grid (5 cards: Stripe / PayU / P24 / PayPal / COD) */
  .gateway-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 1100px) { .gateway-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 700px)  { .gateway-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 480px)  { .gateway-grid { grid-template-columns: 1fr; } }

  .gateway {
    position: relative;
    padding: var(--space-lg);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
    overflow: hidden;
  }
  .gateway:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .gateway::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 2px;
    background: var(--accent, var(--color-accent-gradient));
    opacity: 0.7;
  }
  .gateway.stripe { --accent: linear-gradient(90deg, #635bff, #00d4ff); }
  .gateway.payu   { --accent: linear-gradient(90deg, #a5cd39, #4a8b1e); }
  .gateway.p24    { --accent: linear-gradient(90deg, #d40e14, #ff6b6b); }
  .gateway.paypal { --accent: linear-gradient(90deg, #003087, #009cde); }
  .gateway.cod    { --accent: linear-gradient(90deg, #34d399, #22d3ee); }

  .gateway-name {
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 0.4rem;
    letter-spacing: -0.01em;
  }
  .gateway-desc { font-size: 0.78rem; color: var(--color-text-tertiary); margin: 0; line-height: 1.5; }
  .gateway-meta {
    display: flex; flex-wrap: wrap; gap: 0.3rem;
    margin-top: var(--space-md);
    padding-top: var(--space-md);
    border-top: 1px solid var(--color-border);
  }
  .gateway-meta .chip {
    padding: 0.15rem 0.45rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.65rem;
    color: var(--color-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  /* Order flow timeline */
  .flow {
    position: relative;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 1100px) { .flow { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .flow { grid-template-columns: 1fr; } }

  .flow-step {
    position: relative;
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .flow-step:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .flow-step-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px;
    background: var(--color-accent-gradient);
    border-radius: var(--radius-sm);
    font-family: var(--font-display);
    font-size: 0.85rem;
    font-weight: 700;
    color: white;
    margin-bottom: var(--space-sm);
    box-shadow: 0 6px 16px -6px rgba(139, 92, 246, 0.6);
  }
  .flow-step h4 { font-family: var(--font-display); font-size: 0.95rem; margin-bottom: 0.4rem; letter-spacing: -0.01em; }
  .flow-step p { font-size: 0.8rem; color: var(--color-text-secondary); margin: 0; line-height: 1.55; }
  .flow-step code {
    display: inline-block;
    margin-top: 0.4rem;
    padding: 0.15rem 0.4rem;
    background: var(--color-bg-code);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: #67e8f9;
  }

  /* Order-status pipeline */
  .pipeline {
    display: flex;
    align-items: center;
    gap: 0;
    padding: var(--space-lg);
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow-x: auto;
  }
  .pipe-state {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 0.45rem;
    padding: 0.55rem 0.9rem;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    border: 1px solid;
    white-space: nowrap;
  }
  .pipe-state .dot { width: 7px; height: 7px; border-radius: 50%; }
  .pipe-state.gray   { background: rgba(255, 255, 255, 0.05); border-color: var(--color-border-hover); color: var(--color-text-secondary); }
  .pipe-state.gray   .dot { background: var(--color-text-tertiary); }
  .pipe-state.warn   { background: rgba(251, 191, 36, 0.12); border-color: rgba(251, 191, 36, 0.3); color: #fde68a; }
  .pipe-state.warn   .dot { background: #fbbf24; }
  .pipe-state.cool   { background: rgba(34, 211, 238, 0.12); border-color: rgba(34, 211, 238, 0.3); color: #67e8f9; }
  .pipe-state.cool   .dot { background: #22d3ee; }
  .pipe-state.good   { background: rgba(52, 211, 153, 0.12); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7; }
  .pipe-state.good   .dot { background: #34d399; box-shadow: 0 0 8px rgba(52, 211, 153, 0.5); }
  .pipe-state.bad    { background: rgba(248, 113, 113, 0.08); border-color: rgba(248, 113, 113, 0.3); color: #fca5a5; }
  .pipe-state.bad    .dot { background: #f87171; }
  .pipe-arrow {
    flex-shrink: 0;
    width: 32px;
    height: 1px;
    background: linear-gradient(90deg, var(--color-border-hover), transparent);
    margin: 0 0.1rem;
    position: relative;
  }
  .pipe-arrow::after {
    content: '';
    position: absolute;
    right: 0; top: 50%;
    transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 4px solid var(--color-border-hover);
    border-top: 3px solid transparent;
    border-bottom: 3px solid transparent;
  }

  /* Feature grid */
  .ecom-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .ecom-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .ecom-features { grid-template-columns: 1fr; } }

  .ecom-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .ecom-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .ecom-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(244, 114, 182, 0.12);
    border: 1px solid rgba(244, 114, 182, 0.3);
    color: #f9a8d4;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .ecom-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .ecom-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">ECOMMERCE</span>
        <span>4 gateways · COD · variants · coupons · GDPR-aware</span>
      </div>

      <h1 class="hero-title">
        Sell anything.<br>
        <span class="text-gradient-magic">From anywhere in Europe.</span>
      </h1>

      <p class="hero-subtitle">
        Products with variants, coupons, tax rules, shipping methods, full order lifecycle.
        Stripe, PayU, Przelewy24 and PayPal all wired through one transition service — plus
        cash-on-delivery for when the customer prefers cash.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the shop admin
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#flow" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          See the order flow
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">5</div><div class="label">Payment methods</div></div>
        <div class="hero-stat"><div class="num">4</div><div class="label">Order statuses</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Product variants</div></div>
        <div class="hero-stat"><div class="num">EU</div><div class="label">VAT-ready by default</div></div>
      </div>
    </div>

    <div class="container-wide" style="margin-top: var(--space-3xl);">
      <div class="hero-showcase">
        <div class="hero-showcase-glow" aria-hidden="true"></div>
        <div class="dashboard-preview tilt">
          <div class="preview-bar">
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-url">agares.app/admin/ecommerce/payment-providers</span>
          </div>
          <div class="preview-content">
            <img src="{{ asset('assets/frontend/images/agares_cms_payments.jpg') }}" alt="Agares payment providers admin — Stripe, PayU, P24, PayPal, COD with enabled/disabled toggles and per-driver configure buttons" loading="eager" fetchpriority="high">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FOUR GATEWAYS + COD ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Payment gateways</span>
        <h2>One transition service.<br><span class="text-gradient">Five payment options</span>.</h2>
        <p>Each gateway is its own driver implementing the same interface — initiate, return, webhook. Swap providers without touching checkout.</p>
      </div>

      <div class="gateway-grid">
        <div class="gateway stripe reveal">
          <div class="gateway-name">Stripe</div>
          <p class="gateway-desc">Checkout Sessions API. Stores <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">payment_intent</code> ID for receipts.</p>
          <div class="gateway-meta">
            <span class="chip">International</span>
            <span class="chip">HMAC webhook</span>
          </div>
        </div>

        <div class="gateway payu reveal">
          <div class="gateway-name">PayU</div>
          <p class="gateway-desc">OAuth2 → <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">/api/v2_1/orders</code>. Stores <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">orderId</code>.</p>
          <div class="gateway-meta">
            <span class="chip">Polish market</span>
            <span class="chip">MD5 sig</span>
          </div>
        </div>

        <div class="gateway p24 reveal">
          <div class="gateway-name">Przelewy24</div>
          <p class="gateway-desc">SHA-384 signed <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">/api/v1/transaction/register</code>.</p>
          <div class="gateway-meta">
            <span class="chip">Polish bank transfer</span>
            <span class="chip">SHA-384</span>
          </div>
        </div>

        <div class="gateway paypal reveal">
          <div class="gateway-name">PayPal</div>
          <p class="gateway-desc">Orders API v2, captures on return. Cert-signed webhook with <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">webhook_id</code>.</p>
          <div class="gateway-meta">
            <span class="chip">International</span>
            <span class="chip">OpenSSL cert</span>
          </div>
        </div>

        <div class="gateway cod reveal">
          <div class="gateway-name">Cash on delivery</div>
          <p class="gateway-desc">No external call. Order goes straight to <code style="font-family:var(--font-mono);color:#67e8f9;font-size:0.85em;">processing</code> — courier collects payment.</p>
          <div class="gateway-meta">
            <span class="chip">No fees</span>
            <span class="chip">Trust-based</span>
          </div>
        </div>
      </div>

      <div style="padding: var(--space-md) var(--space-lg); margin-top: var(--space-lg); background: rgba(248, 113, 113, 0.06); border: 1px solid rgba(248, 113, 113, 0.25); border-left: 3px solid var(--color-error); border-radius: var(--radius-md);">
        <div style="font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: #fca5a5; margin-bottom: 0.3rem;">Hard-fail by design</div>
        <p style="font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6;">
          Every webhook hard-fails 500 if its signing secret is missing from the provider config. Silent acceptance of unsigned payloads is worse than downtime — we will never quietly trust a webhook we can't verify.
        </p>
      </div>
    </div>
  </section>

  {{-- ============ ORDER FLOW ============ --}}
  <section id="flow">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The order flow</span>
        <h2>From cart click to captured payment,<br><span class="text-gradient">in five well-defined steps</span>.</h2>
        <p>One transition service governs every state change. Every flip is auditable, every status has its own permission.</p>
      </div>

      <div class="flow">
        <div class="flow-step reveal">
          <span class="flow-step-num">1</span>
          <h4>Checkout submit</h4>
          <p>Validates, generates order number, creates <strong>Order</strong> + <strong>OrderItems</strong> + history + payment in one DB transaction.</p>
          <code>{prefix}{YYYYMMDD}{seq4}</code>
        </div>
        <div class="flow-step reveal">
          <span class="flow-step-num">2</span>
          <h4>Gateway initiate</h4>
          <p>Per-driver <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">initiatePayment()</code> returns a redirect URL. COD skips this and goes to processing.</p>
        </div>
        <div class="flow-step reveal">
          <span class="flow-step-num">3</span>
          <h4>Customer return</h4>
          <p>Gateway redirects back to <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">/shop/payment/return/{driver}</code>. Shown the confirmation page.</p>
        </div>
        <div class="flow-step reveal">
          <span class="flow-step-num">4</span>
          <h4>Webhook captures</h4>
          <p>Gateway POSTs signed webhook → per-driver handler → <code style="font-family:var(--font-mono);font-size:0.8em;color:#67e8f9;">PaymentTransitionService::capture()</code>.</p>
        </div>
        <div class="flow-step reveal">
          <span class="flow-step-num">5</span>
          <h4>Emails fire</h4>
          <p>Customer gets <strong>OrderConfirmed</strong>, admin gets <strong>NewOrderAlert</strong>. Status changes later send <strong>OrderStatusChanged</strong>.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ STATUS PIPELINE ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Order lifecycle</span>
        <h2>Every order's state,<br><span class="text-gradient">visible end to end</span>.</h2>
        <p>The full pipeline an order can walk. Every transition is recorded with actor, timestamp, optional comment, and feeds the audit trail.</p>
      </div>

      <div class="pipeline">
        <span class="pipe-state warn"><span class="dot"></span>pending_payment</span>
        <span class="pipe-arrow"></span>
        <span class="pipe-state cool"><span class="dot"></span>processing</span>
        <span class="pipe-arrow"></span>
        <span class="pipe-state cool"><span class="dot"></span>shipped</span>
        <span class="pipe-arrow"></span>
        <span class="pipe-state good"><span class="dot"></span>delivered</span>
      </div>

      <div style="margin-top: var(--space-md); display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; font-size: 0.82rem; color: var(--color-text-tertiary);">
        <span style="font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em;">Alt exits:</span>
        <span class="pipe-state bad"><span class="dot"></span>cancelled</span>
        <span class="pipe-state bad"><span class="dot"></span>refunded</span>
        <span class="pipe-state gray"><span class="dot"></span>on_hold</span>
      </div>

      <p style="margin-top: var(--space-lg); font-size: 0.82rem; color: var(--color-text-tertiary); font-family: var(--font-mono);">
        Schema: <code style="color:#c4b5fd;">id · order_number · status · subtotal · tax · shipping · grand_total · currency · customer · billing · created_at</code>
      </p>
    </div>
  </section>

  {{-- ============ FEATURES GRID ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Everything inside the box</span>
        <h2>Not just checkout.<br><span class="text-gradient">A full commerce stack</span>.</h2>
        <p>Products, taxes, coupons, shipping, customer accounts, returns. The boring stuff that takes months elsewhere.</p>
      </div>

      <div class="ecom-features">
        @foreach([
          ['M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z', 'Products + variants', 'Master product with size/color/material variants. Per-variant pricing, stock, SKU, weight.'],
          ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z', 'Coupons', 'Percent or fixed amount, min order, expiry, per-customer limit, single-use codes — full redemption history.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Shipping methods', 'Per-zone, per-weight, free-above-threshold. Couriers, pickup points, in-store collection.'],
          ['M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z', 'Tax rules', 'EU VAT-ready: per-country rate, reverse charge for B2B, tax-inclusive or exclusive product pricing.'],
          ['M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M12 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z', 'Customer accounts', 'Guest checkout supported. Optional "create account at checkout" flips the customer to a full user with order history.'],
          ['M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6', 'Email events', '<strong>OrderConfirmed</strong> to customer, <strong>NewOrderAlert</strong> to admin, <strong>OrderStatusChanged</strong> on every transition. Inline HTML, no Mailtrap surprises.'],
          ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'GDPR-aware', 'Customer data tagged for retention. Order PII separated from cart contents. Right-to-be-forgotten flow honoured.'],
          ['M3 3h18v18H3z M3 9h18 M9 21V9', 'Multi-currency ready', 'Single currency today (PLN / EUR / GBP / USD); the schema fields <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">currency</code> + <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">default_currency</code> + <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">available_currencies</code> are ready.'],
          ['M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01l-3-3', 'Returns &amp; refunds', 'Return RMA flow with reason codes, partial-refund support, automatic stock reinstatement.'],
        ] as $f)
          <div class="ecom-feature reveal">
            <div class="ecom-feature-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{!! $f[1] !!}</h4>
            <p>{!! $f[2] !!}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-pink mb-md">Shop demo</span>
        <h2>Place a fake order.<br>Walk the <span class="text-gradient">whole flow</span>.</h2>
        <p>The demo lets you add to cart, check out with COD, see the order land in the admin grid, and even simulate a webhook flip. No real money involved.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Try the shop demo
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/pricing" class="btn btn-secondary btn-lg">See pricing</a>
        </div>
      </div>
    </div>
  </section>

@stop
