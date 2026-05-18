@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Newsletter page extras ============ */

  /* Architecture: CMS -> SaaS delegation flow */
  .nl-arch {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: var(--space-md);
    align-items: stretch;
    padding: clamp(1.5rem, 3vw, 2.5rem);
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
  }
  @media (max-width: 800px) { .nl-arch { grid-template-columns: 1fr; } }
  .nl-arch-box {
    padding: var(--space-lg);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.015));
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-lg);
    text-align: center;
    transition: all var(--transition-base);
  }
  .nl-arch-box:hover { border-color: var(--color-border-strong); transform: translateY(-2px); }
  .nl-arch-box-label {
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-text-tertiary);
    margin-bottom: 0.4rem;
  }
  .nl-arch-box-name {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 0.6rem;
    letter-spacing: -0.015em;
  }
  .nl-arch-box-name.cms { color: var(--color-accent-secondary); }
  .nl-arch-box-name.saas { background: var(--color-accent-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
  .nl-arch-box-list { font-size: 0.78rem; color: var(--color-text-secondary); line-height: 1.65; text-align: left; padding-left: 1rem; }
  .nl-arch-box-list li { padding: 0.15rem 0; }
  .nl-arch-link {
    display: flex; align-items: center; justify-content: center;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0 var(--space-md);
    color: var(--color-text-tertiary);
    font-family: var(--font-mono);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .nl-arch-link svg { color: var(--color-accent-primary); }
  @media (max-width: 800px) {
    .nl-arch-link { padding: var(--space-md) 0; }
  }

  /* Status pills row */
  .nl-status-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-md);
  }
  .nl-status-card {
    padding: var(--space-lg);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .nl-status-card:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .nl-status-card .status {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.2rem 0.55rem;
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: var(--space-sm);
  }
  .nl-status-card .status.draft   { background: rgba(255, 255, 255, 0.05); color: var(--color-text-tertiary); border: 1px solid var(--color-border); }
  .nl-status-card .status.ready   { background: rgba(34, 211, 238, 0.12); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .nl-status-card .status.sending { background: rgba(251, 191, 36, 0.12); color: #fde68a; border: 1px solid rgba(251, 191, 36, 0.3); }
  .nl-status-card .status.sent    { background: rgba(52, 211, 153, 0.12); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); }
  .nl-status-card h4 { font-family: var(--font-display); font-size: 1rem; margin-bottom: 0.3rem; letter-spacing: -0.01em; }
  .nl-status-card p  { font-size: 0.82rem; color: var(--color-text-secondary); margin: 0; line-height: 1.55; }

  /* Feature grid */
  .nl-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .nl-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .nl-features { grid-template-columns: 1fr; } }

  .nl-feature {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .nl-feature:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .nl-feature-ico {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.3);
    color: #6ee7b7;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
  }
  .nl-feature h4 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: 0.4rem; letter-spacing: -0.015em; }
  .nl-feature p { font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.65; }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">NEWSLETTER</span>
        <span>Subscribers · lists · campaigns · GDPR-aware · queue-free</span>
      </div>

      <h1 class="hero-title">
        Email at scale.<br>
        <span class="text-gradient-magic">Without a queue worker.</span>
      </h1>

      <p class="hero-subtitle">
        Subscribers, lists, templates, campaigns — all in the CMS admin. Bulk sending delegates
        to the Agares SaaS over a signed HTTP contract, so the CMS still runs on any shared
        host. RFC 8058 one-click unsubscribe, full GDPR consent capture, webhook delivery sync.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See the newsletter admin
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#flow" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          See the architecture
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><div class="num">17</div><div class="label">Newsletter permissions</div></div>
        <div class="hero-stat"><div class="num">4</div><div class="label">Campaign status states</div></div>
        <div class="hero-stat"><div class="num">0</div><div class="label">Cron jobs needed</div></div>
        <div class="hero-stat"><div class="num">∞</div><div class="label">Subscribers per list</div></div>
      </div>
    </div>
  </section>

  {{-- ============ THE QUEUE-FREE TRICK ============ --}}
  <section id="flow" style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The architecture</span>
        <h2>CMS owns the content.<br><span class="text-gradient">SaaS owns the queue</span>.</h2>
        <p>One signed HTTP contract between them. The CMS never iterates the subscriber table for bulk send — it hands the campaign off and listens for webhooks back.</p>
      </div>

      <div class="nl-arch reveal">
        <div class="nl-arch-box">
          <div class="nl-arch-box-label">Front door</div>
          <div class="nl-arch-box-name cms">Agares CMS</div>
          <ul class="nl-arch-box-list">
            <li>Subscribers + lists</li>
            <li>Templates + drafts</li>
            <li>GDPR consent capture</li>
            <li>Test sends (sync)</li>
            <li>Delegates bulk →</li>
          </ul>
        </div>

        <div class="nl-arch-link">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
          <span>HMAC-signed<br>POST /campaigns</span>
        </div>

        <div class="nl-arch-box">
          <div class="nl-arch-box-label">Heavy lifting</div>
          <div class="nl-arch-box-name saas">Agares SaaS</div>
          <ul class="nl-arch-box-list">
            <li>Queue worker (per-min)</li>
            <li>Atomic per-recipient idempotency</li>
            <li>Bounce processor (per 10 min)</li>
            <li>Per-tenant rate limits</li>
            <li>Webhooks back to CMS ←</li>
          </ul>
        </div>
      </div>

      <p style="text-align: center; margin-top: var(--space-md); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
        The CMS works on any shared host. The SaaS runs the queue. No Supervisor on your server.
      </p>
    </div>
  </section>

  {{-- ============ CAMPAIGN STATES ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Campaign lifecycle</span>
        <h2>Four locked states.<br><span class="text-gradient">Zero accidental sends</span>.</h2>
        <p>Once a campaign is delegated, the CMS is read-only on it. The SaaS owns lifecycle from that point — and only failed campaigns can be re-sent.</p>
      </div>

      <div class="nl-status-row">
        <div class="nl-status-card reveal">
          <span class="status draft">draft</span>
          <h4>Draft</h4>
          <p>Fully editable. Templates can be applied, body rewritten, lists picked, sender identity chosen.</p>
        </div>
        <div class="nl-status-card reveal">
          <span class="status ready">ready</span>
          <h4>Ready</h4>
          <p>Locked for content. Test send allowed. Lists required. Send button armed.</p>
        </div>
        <div class="nl-status-card reveal">
          <span class="status sending">external_sending</span>
          <h4>Delegated</h4>
          <p>SaaS is sending in batches. CMS reads counters: <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">external_sent_count</code>, <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">external_failed_count</code>.</p>
        </div>
        <div class="nl-status-card reveal">
          <span class="status sent">external_sent</span>
          <h4>Sent</h4>
          <p>Final counters synced via webhook. Open and click counts continue to mirror as recipients engage.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FEATURE GRID ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Everything inside</span>
        <h2>A serious newsletter platform,<br><span class="text-gradient">without the SaaS pricing</span>.</h2>
      </div>

      <div class="nl-features">
        @foreach([
          ['M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M12 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z', 'Subscribers + lists', 'Many-to-many subscribers ↔ lists. Per-subscriber status: <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">active</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">pending</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">unsubscribed</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">bounced</code> / <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">complained</code>.'],
          ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'Templates + campaigns', 'TinyMCE-edited HTML templates with auto-sanitisation. Campaign picker prefills subject + body via inline JS, fully overridable.'],
          ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'GDPR consent capture', 'Every signup persists <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">consent_text</code> + <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">consent_ip</code> + <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">consent_user_agent</code>. Resubscribers get fresh metadata.'],
          ['M3 3h18v18H3zM9 9l4 4 4-4', 'RFC 8058 unsubscribe', 'One-click unsubscribe headers in every email. Token-based unsubscribe links work even after the module is disabled.'],
          ['M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6', 'Test sends (sync)', '<code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">LocalNewsletterSender</code> drives test emails through one synchronous <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">Mail::send()</code>. Subject auto-prefixed with [TEST], banner in body.'],
          ['M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2', 'Status sync', 'Pull-mode <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">syncStatus()</code> + push-mode HMAC webhook. The CMS always knows where bulk send is.'],
          ['M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z', 'Sender driver abstraction', 'Three drivers in shipping: <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">disabled</code>, <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">local</code>, <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">external_api</code>. Swap providers without controller churn.'],
          ['M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'Public signup partial', 'Drop <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">@include(\'partials.newsletter_signup\')</code> on any page. Self-gates by <code style="font-family:var(--font-mono);font-size:0.85em;color:#67e8f9;">enable_newsletter</code> setting.'],
          ['M12 8v4l3 3M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z', '17 permissions', 'View / manage subscribers, lists, templates, campaigns, settings + 4 specialised: preview, test send, delegate, sync.'],
        ] as $f)
          <div class="nl-feature reveal">
            <div class="nl-feature-ico">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $f[0] }}"/></svg>
            </div>
            <h4>{!! $f[1] !!}</h4>
            <p>{!! $f[2] !!}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ NO QUEUE CALLOUT ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal" style="text-align: left;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-2xl); align-items: center;">
          <div>
            <span class="badge badge-success mb-md">Shared-hosting friendly</span>
            <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); margin-bottom: var(--space-md);">No Supervisor.<br>No cron.<br><span class="text-gradient">No "ops setup"</span>.</h2>
            <p style="color: var(--color-text-secondary); margin: 0; line-height: 1.65; font-size: var(--text-base);">
              The CMS runs on Cyber-Folks shared hosting. Sends to a million inboxes happen on the SaaS, which has a real queue worker.
              You write content, the SaaS does the work, the CMS gets the result back.
            </p>
          </div>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <span class="badge badge-success">RFC 8058 ready</span>
            <span class="badge badge-success">HMAC webhooks</span>
            <span class="badge badge-success">DNS auto-verify</span>
            <span class="badge badge-success">Bounce processing</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-cyan mb-md">Newsletter demo</span>
        <h2>Build a list. Draft a campaign.<br><span class="text-gradient">Send yourself a test</span>.</h2>
        <p>The demo lets you walk through the entire flow — subscriber import, template, campaign, test send — without touching a queue worker.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the newsletter module
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/projects" class="btn btn-secondary btn-lg">See the SaaS that powers it</a>
        </div>
      </div>
    </div>
  </section>

@stop
