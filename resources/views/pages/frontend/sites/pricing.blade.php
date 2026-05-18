@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Pricing page extras ============ */

  .plan-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-xl);
    align-items: stretch;
  }
  @media (max-width: 900px) { .plan-split { grid-template-columns: 1fr; } }

  .plan {
    position: relative;
    display: flex; flex-direction: column;
    padding: clamp(2rem, 3.5vw, 3rem);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--color-border);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    transition: all var(--transition-base);
    overflow: hidden;
  }
  .plan:hover { border-color: var(--color-border-hover); transform: translateY(-4px); }

  .plan.self {
    background:
      radial-gradient(ellipse 70% 50% at 50% 0%, rgba(34, 211, 238, 0.10), transparent 60%),
      linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
  }
  .plan.managed {
    background:
      radial-gradient(ellipse 70% 50% at 50% 0%, rgba(139, 92, 246, 0.16), transparent 60%),
      linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border-color: rgba(139, 92, 246, 0.35);
    box-shadow: 0 30px 80px -25px rgba(139, 92, 246, 0.4), 0 0 0 1px rgba(139, 92, 246, 0.15);
  }
  .plan.managed::before {
    content: 'Most teams pick this';
    position: absolute;
    top: -1px; right: var(--space-2xl);
    transform: translateY(-50%);
    padding: 0.3rem 0.85rem;
    background: var(--color-accent-gradient);
    color: white;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-radius: var(--radius-full);
    white-space: nowrap;
  }

  .plan-icon {
    width: 48px; height: 48px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .plan.self    .plan-icon { background: rgba(34, 211, 238, 0.12); border: 1px solid rgba(34, 211, 238, 0.3); color: #67e8f9; }
  .plan.managed .plan-icon { background: rgba(139, 92, 246, 0.16); border: 1px solid rgba(139, 92, 246, 0.35); color: #c4b5fd; }

  .plan-eyebrow {
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--color-text-tertiary);
    margin-bottom: 0.5rem;
  }
  .plan h3 {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    letter-spacing: -0.03em;
    margin-bottom: var(--space-sm);
  }
  .plan .tagline {
    color: var(--color-text-secondary);
    font-size: var(--text-base);
    margin-bottom: var(--space-lg);
    line-height: 1.6;
  }

  .plan-price {
    display: flex; align-items: baseline; gap: 0.4rem;
    margin: var(--space-md) 0;
    padding: var(--space-md) 0 var(--space-lg);
    border-top: 1px solid var(--color-border);
    border-bottom: 1px solid var(--color-border);
  }
  .plan-price .from { font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.08em; }
  .plan-price .amount {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 4vw, 3.25rem);
    font-weight: 700;
    color: var(--color-text-primary);
    letter-spacing: -0.04em;
    line-height: 1;
  }
  .plan-price .period { font-family: var(--font-mono); color: var(--color-text-tertiary); font-size: 0.85rem; }

  .plan-features { list-style: none; padding: 0; margin: var(--space-lg) 0; flex: 1; }
  .plan-features li {
    display: flex; gap: 0.65rem; align-items: flex-start;
    padding: 0.45rem 0;
    font-size: 0.92rem;
    color: var(--color-text-secondary);
    line-height: 1.5;
  }
  .plan-features li svg { flex-shrink: 0; margin-top: 4px; color: var(--color-accent-green); }
  .plan-features li.muted { opacity: 0.45; }
  .plan-features li.muted svg { color: var(--color-text-muted); }

  .plan-cta {
    display: block;
    text-align: center;
    margin-top: var(--space-lg);
  }

  /* Feature comparison table */
  .compare {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
  }
  .compare-row {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--color-border);
  }
  .compare-row:last-child { border-bottom: none; }
  .compare-row.head {
    background: rgba(255, 255, 255, 0.03);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-text-tertiary);
  }
  .compare-row.head .col-self,
  .compare-row.head .col-managed {
    text-align: center;
    font-family: var(--font-display);
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: none;
    letter-spacing: -0.01em;
    color: var(--color-text-primary);
  }
  .compare-row .feature { color: var(--color-text-primary); font-weight: 500; }
  .compare-row .feature small { display: block; color: var(--color-text-tertiary); font-weight: 400; font-size: 0.78rem; margin-top: 2px; }
  .compare-row .col-self,
  .compare-row .col-managed { text-align: center; }
  .compare-row .yes  { color: var(--color-accent-green); }
  .compare-row .no   { color: var(--color-text-muted); }
  .compare-row .meh  { color: var(--color-accent-amber); font-size: 0.85rem; }

  @media (max-width: 800px) {
    .compare { overflow-x: auto; }
    .compare-row { min-width: 580px; }
  }

  /* FAQ */
  .faq-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-md);
  }
  @media (max-width: 800px) { .faq-grid { grid-template-columns: 1fr; } }

  .faq {
    padding: var(--space-lg) var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .faq:hover { border-color: var(--color-border-hover); }
  .faq h4 {
    font-family: var(--font-display);
    font-size: 1.05rem;
    margin-bottom: 0.5rem;
    letter-spacing: -0.015em;
  }
  .faq p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">PRICING</span>
        <span>Two ways to run Agares · pick yours</span>
      </div>

      <h1 class="hero-title">
        Own the code.<br>
        <span class="text-gradient-magic">Or own your weekends.</span>
      </h1>

      <p class="hero-subtitle">
        Run Agares yourself with full source access, or let us host, monitor and update it
        while you focus on content. Same CMS, same modules, same data — different operational
        burden.
      </p>
    </div>
  </section>

  {{-- ============ TWO PLANS ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container">

      <div class="plan-split">

        {{-- Self-hosted --}}
        <div class="plan self reveal">
          <div class="plan-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="3" width="20" height="14" rx="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/>
              <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
          </div>
          <span class="plan-eyebrow">For teams that prefer control</span>
          <h3>Self-hosted</h3>
          <p class="tagline">Full source. Your server. Your call.</p>

          <div class="plan-price">
            <span class="from">from</span>
            <span class="amount">€499</span>
            <span class="period">/ one-time</span>
          </div>

          <ul class="plan-features">
            @foreach([
              ['Full source code under perpetual license', true],
              ['Unlimited sites, unlimited admin users', true],
              ['All modules: ecommerce, newsletter, RBAC, 2FA, API', true],
              ['12 months of free updates &amp; new features', true],
              ['Self-deploy on any Laravel-capable host', true],
              ['Community support via GitHub issues', true],
              ['Optional paid support per incident', true],
              ['SLA, hosting, monitoring', false],
              ['Managed deployments &amp; rollbacks', false],
              ['Daily backups &amp; uptime monitoring', false],
            ] as $f)
              <li class="{{ $f[1] ? '' : 'muted' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  @if($f[1])
                    <polyline points="20 6 9 17 4 12"/>
                  @else
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  @endif
                </svg>
                <span>{!! $f[0] !!}</span>
              </li>
            @endforeach
          </ul>

          <a href="/contact" class="btn btn-secondary btn-lg plan-cta btn-icon-after">
            Buy a license
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>

        {{-- Managed --}}
        <div class="plan managed reveal">
          <div class="plan-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>
            </svg>
          </div>
          <span class="plan-eyebrow">For teams that prefer time</span>
          <h3>Managed</h3>
          <p class="tagline">We host. We monitor. We update.</p>

          <div class="plan-price">
            <span class="from">from</span>
            <span class="amount">€49</span>
            <span class="period">/ month per site</span>
          </div>

          <ul class="plan-features">
            @foreach([
              ['Everything in Self-hosted, plus:', true],
              ['Managed EU hosting (Cyber-Folks)', true],
              ['SSL + custom domain set up for you', true],
              ['Daily encrypted backups, 30-day retention', true],
              ['Uptime monitoring + status page', true],
              ['Zero-downtime deploys &amp; rollbacks', true],
              ['New features pushed automatically', true],
              ['Email support, 1-business-day response', true],
              ['99.9% uptime SLA on Studio &amp; up', true],
              ['Optional managed SaaS (newsletters, RAG)', true],
            ] as $f)
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{!! $f[0] !!}</span>
              </li>
            @endforeach
          </ul>

          <a href="/contact" class="btn btn-primary btn-lg plan-cta btn-icon-after">
            Start a managed site
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      </div>

      <p style="text-align: center; margin-top: var(--space-xl); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
        All prices in EUR, excl. VAT. Volume discounts for 5+ sites. Custom enterprise terms available — <a href="/contact" style="color: var(--color-accent-secondary);">talk to us</a>.
      </p>
    </div>
  </section>

  {{-- ============ COMPARISON TABLE ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Side by side</span>
        <h2>What's in each plan,<br><span class="text-gradient">at a glance</span>.</h2>
      </div>

      <div class="compare">
        <div class="compare-row head">
          <span>Feature</span>
          <span class="col-self">Self-hosted</span>
          <span class="col-managed">Managed</span>
        </div>

        @foreach([
          ['CMS core', 'Multi-site, categories, articles, custom fields',                'yes', 'yes'],
          ['Modules', 'Ecommerce, newsletter, RBAC, 2FA, cookies, forms, media',         'yes', 'yes'],
          ['REST API', 'Versioned, scoped keys, rate limited',                            'yes', 'yes'],
          ['Source access', 'Full Laravel codebase, perpetual license',                   'yes', 'yes'],
          ['Updates', 'Free for 12 months on Self-hosted; continuous on Managed',         '12 months', 'Continuous'],
          ['Hosting', 'EU data centre, SSL, custom domain',                               'no',  'yes'],
          ['Backups', 'Daily encrypted, 30-day retention',                                'no',  'yes'],
          ['Uptime monitoring', 'External monitor + status page',                         'no',  'yes'],
          ['Deploy automation', 'Zero-downtime deploys &amp; rollbacks',                  'no',  'yes'],
          ['Support', 'Community on Self-hosted, email on Managed',                       'Community', 'Email (1 business day)'],
          ['SLA', '99.9% uptime, credit on miss',                                         'no',  'yes'],
          ['Agares SaaS bundle', 'Newsletter, RAG, multi-tenant agents',                  'Pay-as-you-go', 'Optional add-on'],
        ] as $row)
          <div class="compare-row reveal">
            <span class="feature">
              <strong>{{ $row[0] }}</strong>
              <small>{!! $row[1] !!}</small>
            </span>
            <span class="col-self">
              @if($row[2] === 'yes')
                <svg class="yes" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              @elseif($row[2] === 'no')
                <svg class="no" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              @else
                <span class="meh">{{ $row[2] }}</span>
              @endif
            </span>
            <span class="col-managed">
              @if($row[3] === 'yes')
                <svg class="yes" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              @elseif($row[3] === 'no')
                <svg class="no" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              @else
                <span class="meh">{{ $row[3] }}</span>
              @endif
            </span>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ FAQ ============ --}}
  <section>
    <div class="container">
      <div class="section-header">
        <span class="eyebrow">Common questions</span>
        <h2>Things people ask us<br><span class="text-gradient">before they pick</span>.</h2>
      </div>

      <div class="faq-grid">
        @foreach([
          ['Can I switch from Managed to Self-hosted later?', 'Yes — we export your database and media library, and you get the same source code you\'d get if you bought Self-hosted on day one. No vendor lock-in by design.'],
          ['What\'s included in the 12-month update window?', 'Every new feature, security patch and module released in the 12 months from purchase. After that you keep what you have forever; updates become a renewal you can opt into.'],
          ['Do I need to use your hosting?', 'Only on Managed. Self-hosted runs anywhere Laravel runs — your DigitalOcean droplet, Cyber-Folks shared hosting, Hetzner, AWS, Forge, anything with PHP 8.3 and MySQL 8.'],
          ['Is the source really mine?', 'Yes. A perpetual license, no obfuscation, no DRM, no phone-home. You can read the whole thing, modify it, fork it for your client. The only thing you can\'t do is resell it as a competing product.'],
          ['What if my site outgrows the basic Managed tier?', 'We bundle one site per Managed seat, but extra sites are just additional seats. Studio (3 sites) and Enterprise (white-label, dedicated support) tiers exist — <a href="/contact" style="color: var(--color-accent-secondary);">talk to us</a>.'],
          ['Do you take care of GDPR / cookies?', 'The cookie consent module, the newsletter consent capture and the form submission logs are all GDPR-aware on both plans. On Managed we also host in the EU and sign a DPA on request.'],
          ['Can I bring my own developer?', 'Absolutely — Agares is just Laravel. Hire any Laravel dev and they\'ll be productive in a day. We\'re happy to do code review for your team if you\'re on Managed.'],
          ['Is there a free trial?', 'The live demo at <a href="/" style="color: var(--color-accent-secondary);">demo.agares.co.uk</a> is the trial. Walk through every screen as a read-only viewer — no signup needed.'],
        ] as $f)
          <div class="faq reveal">
            <h4>{{ $f[0] }}</h4>
            <p>{!! $f[1] !!}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-primary mb-md">Still deciding?</span>
        <h2>Walk the demo, then pick.<br>Or just <span class="text-gradient">talk to us</span>.</h2>
        <p>Most teams figure out which plan fits in about ten minutes inside the demo. If you'd rather skip ahead, we're an email away.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Try the demo
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/contact" class="btn btn-secondary btn-lg">Talk to us</a>
        </div>
      </div>
    </div>
  </section>

@stop
