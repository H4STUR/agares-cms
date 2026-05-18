@extends('pages.frontend.base')

@push('styles')
<style>
  /* ============ Security page extras ============ */

  /* Three-pillar grid */
  .pillars {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 900px) { .pillars { grid-template-columns: 1fr; } }

  .pillar {
    padding: var(--space-2xl);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.01));
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    position: relative;
    overflow: hidden;
    transition: all var(--transition-base);
  }
  .pillar:hover { border-color: var(--color-border-hover); transform: translateY(-3px); }
  .pillar::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 2px;
    background: var(--accent, var(--color-accent-gradient));
    opacity: 0.7;
  }
  .pillar.green { --accent: linear-gradient(90deg, #34d399, #22d3ee); }
  .pillar.violet { --accent: linear-gradient(90deg, #8b5cf6, #f472b6); }
  .pillar.amber { --accent: linear-gradient(90deg, #fbbf24, #f472b6); }

  .pillar-icon {
    width: 48px; height: 48px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--icon-bg, rgba(139, 92, 246, 0.12));
    border: 1px solid var(--icon-border, rgba(139, 92, 246, 0.25));
    color: var(--icon-color, #c4b5fd);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-lg);
  }
  .pillar.green .pillar-icon { background: rgba(52, 211, 153, 0.12); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7; }
  .pillar.amber .pillar-icon { background: rgba(251, 191, 36, 0.12); border-color: rgba(251, 191, 36, 0.3); color: #fde68a; }

  .pillar h3 { font-size: var(--text-xl); margin-bottom: var(--space-sm); }
  .pillar p { font-size: var(--text-sm); color: var(--color-text-secondary); line-height: 1.65; margin-bottom: var(--space-md); }
  .pillar-list { list-style: none; padding: 0; margin: 0; }
  .pillar-list li {
    display: flex; gap: 0.55rem; align-items: flex-start;
    padding: 0.4rem 0;
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    border-top: 1px solid var(--color-border);
  }
  .pillar-list li:first-child { border-top: none; }
  .pillar-list .check {
    flex-shrink: 0;
    width: 16px; height: 16px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(52, 211, 153, 0.12);
    color: var(--color-accent-green);
    margin-top: 1px;
  }

  /* Factor row (TOTP / Email OTP / Recovery codes) */
  .factor-row {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
  }
  .factor-row-label {
    font-family: var(--font-mono);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }
  .factor-row-text { font-size: 0.88rem; color: var(--color-text-secondary); line-height: 1.6; }
  @media (max-width: 700px) {
    .factor-row { grid-template-columns: 1fr; gap: 0.4rem; padding: 0.85rem 1rem; }
  }

  /* Role matrix horizontal scroll on small screens */
  @media (max-width: 800px) {
    .role-matrix { overflow-x: auto; }
    .role-matrix-head, .role-matrix-row { min-width: 540px; }
  }

  /* Audit log mock */
  .audit-mock {
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    font-family: var(--font-mono);
  }
  .audit-mock-head {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: 0.75rem 1.1rem;
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid var(--color-border);
    font-size: var(--text-xs);
    color: var(--color-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }
  .audit-mock-head .dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 8px rgba(52, 211, 153, 0.6); }
  .audit-rows { display: grid; }
  .audit-row {
    display: grid;
    grid-template-columns: 1fr 1.7fr auto;
    gap: var(--space-md);
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid var(--color-border);
    align-items: center;
    font-size: 0.82rem;
  }
  .audit-row:last-child { border-bottom: none; }
  .audit-event {
    display: inline-flex; align-items: center; gap: 0.4rem;
    color: var(--color-text-primary);
  }
  .audit-event .ev {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .audit-event .ev.ok { background: rgba(52, 211, 153, 0.12); color: #6ee7b7; border: 1px solid rgba(52, 211, 153, 0.3); }
  .audit-event .ev.warn { background: rgba(251, 191, 36, 0.12); color: #fde68a; border: 1px solid rgba(251, 191, 36, 0.3); }
  .audit-event .ev.cool { background: rgba(34, 211, 238, 0.12); color: #67e8f9; border: 1px solid rgba(34, 211, 238, 0.3); }
  .audit-event .ev.violet { background: rgba(139, 92, 246, 0.12); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); }
  .audit-meta { color: var(--color-text-tertiary); }
  .audit-time { color: var(--color-text-muted); font-size: 0.75rem; text-align: right; white-space: nowrap; }
  @media (max-width: 700px) {
    .audit-row { grid-template-columns: 1fr; gap: 0.3rem; }
    .audit-time { text-align: left; }
  }

  /* Role matrix mock */
  .role-matrix {
    background: var(--color-bg-code);
    border: 1px solid var(--color-border-hover);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
  }
  .role-matrix-head, .role-matrix-row {
    display: grid;
    grid-template-columns: 1.4fr repeat(4, 1fr);
    align-items: center;
    padding: 0.7rem 1.1rem;
    font-family: var(--font-mono);
    font-size: 0.78rem;
  }
  .role-matrix-head {
    background: rgba(255, 255, 255, 0.03);
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.7rem;
  }
  .role-matrix-row {
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-secondary);
  }
  .role-matrix-row:last-child { border-bottom: none; }
  .role-matrix-row strong { color: var(--color-text-primary); font-weight: 600; }
  .role-matrix .cell { text-align: center; }
  .role-matrix .yes { color: var(--color-accent-green); }
  .role-matrix .gate { color: var(--color-accent-amber); }
  .role-matrix .no  { color: var(--color-text-muted); }

  /* Defense-in-depth stack */
  .defense-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-lg);
  }
  @media (max-width: 1000px) { .defense-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .defense-grid { grid-template-columns: 1fr; } }
  .defense-card {
    padding: var(--space-xl);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
  }
  .defense-card:hover { border-color: var(--color-border-hover); transform: translateY(-2px); }
  .defense-card h4 {
    font-family: var(--font-display);
    font-size: var(--text-base);
    margin-bottom: var(--space-xs);
    color: var(--color-text-primary);
    letter-spacing: -0.01em;
  }
  .defense-card p { font-size: 0.85rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6; }
  .defense-card .ico {
    width: 36px; height: 36px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(34, 211, 238, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.25);
    border-radius: var(--radius-sm);
    color: var(--color-accent-secondary);
    margin-bottom: var(--space-md);
  }
</style>
@endpush

@section('content')

  {{-- ============ HERO ============ --}}
  <section class="hero">
    <div class="container">
      <div class="hero-eyebrow">
        <span class="pill">SHIPPED</span>
        <span>2FA · RBAC · audit log · OAuth coverage · v2.0</span>
      </div>

      <h1 class="hero-title">
        Security, baked in.<br>
        <span class="text-gradient-magic">Not&nbsp;bolted on.</span>
      </h1>

      <p class="hero-subtitle">
        Two-factor auth with TOTP + email-OTP + OAuth coverage. Row-level RBAC
        with site-scoped grants. A queryable security audit log. All inside the
        Laravel app — no Auth0, no Okta, no third-party invoice.
      </p>

      <div class="hero-buttons">
        <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
          See it in the demo
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <a href="#audit-log" class="btn btn-secondary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          Read the audit log spec
        </a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="num">3</div>
          <div class="label">Auth factors</div>
        </div>
        <div class="hero-stat">
          <div class="num">9</div>
          <div class="label">Event types logged</div>
        </div>
        <div class="hero-stat">
          <div class="num">6</div>
          <div class="label">Built-in roles</div>
        </div>
        <div class="hero-stat">
          <div class="num">100%</div>
          <div class="label">Encrypted at rest</div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ THREE PILLARS ============ --}}
  <section style="padding-top: var(--space-2xl);">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The three pillars</span>
        <h2>Authentication. Authorisation.<br><span class="text-gradient">Accountability.</span></h2>
        <p>Three layers, each shipping in production today, each documented end-to-end.</p>
      </div>

      <div class="pillars">

        {{-- 2FA --}}
        <div class="pillar green reveal">
          <div class="pillar-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <h3>Two-factor auth</h3>
          <p>TOTP via any authenticator app, email-OTP fallback, single-use recovery codes, and OAuth callbacks honour 2FA too.</p>
          <ul class="pillar-list">
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Google Authenticator, Authy, 1Password, Bitwarden</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> 8× single-use recovery codes per user</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Force-2FA per role via setting</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Email OTP with 10-min TTL, hashed at rest</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Google + Facebook OAuth challenge through same flow</li>
          </ul>
        </div>

        {{-- RBAC --}}
        <div class="pillar violet reveal">
          <div class="pillar-icon" style="background: rgba(139, 92, 246, 0.14); border-color: rgba(139, 92, 246, 0.3); color: #c4b5fd;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <h3>Roles &amp; permissions</h3>
          <p>Spatie RBAC with a single source of truth in <code style="font-family:var(--font-mono);color:#67e8f9;">Permissions.php</code>. Site-scoped grants for per-client access.</p>
          <ul class="pillar-list">
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> 6 built-in roles, fully extensible</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Per-site permission overrides</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Defense-in-depth: route + controller gates</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> <code style="font-family:var(--font-mono);color:#67e8f9;">Gate::before</code> for owner role</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Viewer role for read-only demo access</li>
          </ul>
        </div>

        {{-- Audit log --}}
        <div class="pillar amber reveal">
          <div class="pillar-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
          </div>
          <h3>Audit log</h3>
          <p>A dedicated <code style="font-family:var(--font-mono);color:#67e8f9;">security_audit_log</code> table records every meaningful event with IP, UA &amp; actor distinction.</p>
          <ul class="pillar-list">
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> 9 event types tracked synchronously</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> <code style="font-family:var(--font-mono);color:#67e8f9;">actor_id</code> ≠ <code style="font-family:var(--font-mono);color:#67e8f9;">user_id</code> — admin actions are obvious</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> IP + user-agent on every row</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> JSON metadata column for per-event context</li>
            <li><span class="check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span> Reusable for any future security domain</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ 2FA DEEP DIVE — split with code window ============ --}}
  <section>
    <div class="container-wide">
      <div class="split">
        <div>
          <span class="eyebrow">Two-factor auth</span>
          <h2 style="margin-bottom: var(--space-md);">Pick your factor.<br><span class="text-gradient">Or pick both.</span></h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Admins choose the policy per role via the <code style="font-family:var(--font-mono);color:#67e8f9;">2FA_method</code> setting:
            TOTP only, email only, or both — letting users pick at enrolment.
          </p>

          <div style="display: grid; gap: 0.6rem; margin-bottom: var(--space-xl);">
            @foreach([
              ['TOTP', '#34d399', '6-digit codes from any RFC 6238 authenticator app. Secret + recovery codes are encrypted at rest in the database.'],
              ['Email OTP', '#67e8f9', '6-digit one-time code, hashed before storage, 10-minute TTL, rate-limited 1/60s short + 5/15min long.'],
              ['Recovery codes', '#c4b5fd', '8 single-use codes in XXXX-XXXX format. Cleared on use, regeneratable on demand, never stored in plaintext.'],
            ] as $row)
              <div class="factor-row">
                <div class="factor-row-label" style="color: {{ $row[1] }};">{{ $row[0] }}</div>
                <div class="factor-row-text">{{ $row[2] }}</div>
              </div>
            @endforeach
          </div>

          <div style="padding: var(--space-md); background: rgba(34, 211, 238, 0.05); border: 1px solid rgba(34, 211, 238, 0.2); border-left: 3px solid var(--color-accent-secondary); border-radius: var(--radius-md);">
            <div style="font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-accent-secondary); margin-bottom: 0.4rem;">No queue needed</div>
            <p style="font-size: 0.88rem; color: var(--color-text-secondary); margin: 0; line-height: 1.6;">Email OTP sends synchronously — same shared-hosting constraint as the rest of the CMS. No worker, no Supervisor, no cron.</p>
          </div>
        </div>

        <div>
          <div class="code-window">
            <div class="code-window-header">
              <span class="preview-dot"></span><span class="preview-dot"></span><span class="preview-dot"></span>
              <span class="code-window-title">app/Services/TwoFactorService.php</span>
            </div>
<pre><span class="com">// Single entry point — the post-login challenge router</span>
<span class="kw">public function</span> <span class="fn">shouldChallenge</span>(<span class="punct">?</span><span class="kw">User</span> <span class="punct">$user</span>)<span class="punct">:</span> <span class="kw">bool</span>
<span class="punct">{</span>
    <span class="kw">if</span> (!<span class="punct">$user</span> || !<span class="punct">$user</span>-&gt;<span class="fn">hasConfirmedTwoFactor</span>()) <span class="kw">return</span> <span class="kw">false</span><span class="punct">;</span>

    <span class="kw">return</span> <span class="kw">match</span> (<span class="fn">setting</span>(<span class="str">'2FA_method'</span>, <span class="str">'totp'</span>)) <span class="punct">{</span>
        <span class="str">'totp'</span>      =&gt; <span class="punct">$user</span>-&gt;two_factor_method === <span class="str">'totp'</span>,
        <span class="str">'email'</span>     =&gt; <span class="punct">$user</span>-&gt;two_factor_method === <span class="str">'email'</span>,
        <span class="str">'both'</span>      =&gt; <span class="kw">true</span>,
        <span class="kw">default</span>     =&gt; <span class="kw">false</span>,
    <span class="punct">};</span>
<span class="punct">}</span>

<span class="com">// OAuth callbacks honour the same gate (Phase 3, 2026-05-15)</span>
<span class="kw">public function</span> <span class="fn">callback</span>(<span class="punct">$provider</span>)
<span class="punct">{</span>
    <span class="punct">$user</span> = <span class="punct">$this</span>-&gt;<span class="fn">findOrCreateOAuthUser</span>(<span class="punct">$provider</span>)<span class="punct">;</span>

    <span class="kw">if</span> (<span class="kw">app</span>(<span class="kw">TwoFactorService</span>::<span class="kw">class</span>)-&gt;<span class="fn">shouldChallenge</span>(<span class="punct">$user</span>)) <span class="punct">{</span>
        <span class="fn">session</span>()-&gt;<span class="fn">put</span>(<span class="str">'login.2fa_user_id'</span>, <span class="punct">$user</span>-&gt;id)<span class="punct">;</span>
        <span class="kw">return</span> <span class="fn">redirect</span>()-&gt;<span class="fn">route</span>(<span class="str">'two-factor.challenge'</span>)<span class="punct">;</span>
    <span class="punct">}</span>

    <span class="kw">Auth</span>::<span class="fn">login</span>(<span class="punct">$user</span>)<span class="punct">;</span>
<span class="punct">}</span></pre>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ RBAC — split with permissions screenshot ============ --}}
  <section>
    <div class="container-wide">
      <div class="split reverse">
        <div>
          <div class="split-image tilt">
            <img src="{{ asset('assets/frontend/images/agares_cms_permissions.jpg') }}" alt="Agares CMS edit-permissions screen — page permissions matrix per role per site, plus CMS-wide permissions grid" loading="lazy">
          </div>
        </div>

        <div>
          <span class="eyebrow">Row-level RBAC</span>
          <h2 style="margin-bottom: var(--space-md);">One permission&nbsp;model.<br><span class="text-gradient">Two enforcement walls.</span></h2>
          <p style="color: var(--color-text-secondary); font-size: var(--text-lg); margin-bottom: var(--space-xl); line-height: 1.65;">
            Every admin route is gated by <code style="font-family:var(--font-mono);color:#67e8f9;">can:view&nbsp;X</code> or <code style="font-family:var(--font-mono);color:#67e8f9;">can:manage&nbsp;X</code>.
            Every controller declares the same middleware via <code style="font-family:var(--font-mono);color:#67e8f9;">HasMiddleware</code>.
            Refactor-proof &mdash; two walls have to fall for an unauthorised action to land.
          </p>

          <div class="role-matrix" style="margin-bottom: var(--space-xl);">
            <div class="role-matrix-head">
              <span>Role</span>
              <span class="cell">CMS</span>
              <span class="cell">Ecommerce</span>
              <span class="cell">Newsletter</span>
              <span class="cell">Security</span>
            </div>
            <div class="role-matrix-row">
              <strong>Owner</strong>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
            </div>
            <div class="role-matrix-row">
              <strong>Admin</strong>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
              <span class="cell yes">✓ all</span>
            </div>
            <div class="role-matrix-row">
              <strong>Moderator</strong>
              <span class="cell yes">✓ content</span>
              <span class="cell no">—</span>
              <span class="cell gate">view</span>
              <span class="cell no">—</span>
            </div>
            <div class="role-matrix-row">
              <strong>Viewer</strong>
              <span class="cell gate">view-only</span>
              <span class="cell gate">view-only</span>
              <span class="cell gate">view-only</span>
              <span class="cell gate">audit log</span>
            </div>
            <div class="role-matrix-row">
              <strong>Customer</strong>
              <span class="cell no">—</span>
              <span class="cell gate">own orders</span>
              <span class="cell no">—</span>
              <span class="cell gate">own 2FA</span>
            </div>
          </div>

          <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
            <span class="badge badge-primary">Per-site grants</span>
            <span class="badge badge-cyan">Single source: Permissions.php</span>
            <span class="badge badge-success">Defense-in-depth</span>
            <span class="badge badge-primary">119 passing tests</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ AUDIT LOG MOCK ============ --}}
  <section id="audit-log">
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">The audit log</span>
        <h2>Every meaningful event,<br><span class="text-gradient">queryable by anyone with access</span>.</h2>
        <p>One table, nine event constants, IP + UA on every row. Render it anywhere — user profile, admin grid, JSON export.</p>
      </div>

      <div class="audit-mock reveal">
        <div class="audit-mock-head">
          <span class="dot"></span>
          <span>security_audit_log · last 8 events</span>
        </div>

        <div class="audit-rows">
          <div class="audit-row">
            <span class="audit-event"><span class="ev ok">2fa.challenge.success</span> <strong style="color: var(--color-text-primary);">user@agares.app</strong></span>
            <span class="audit-meta">TOTP · ip&nbsp;<code style="font-family:var(--font-mono);color:#67e8f9;">81.142.10.5</code> · Chrome 130 / macOS</span>
            <span class="audit-time">2 min ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev violet">2fa.oauth.challenged</span> <strong style="color: var(--color-text-primary);">designer@studio.co</strong></span>
            <span class="audit-meta">provider=google · stash → /2fa/challenge</span>
            <span class="audit-time">14 min ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev warn">2fa.challenge.failed</span> <strong style="color: var(--color-text-primary);">user@agares.app</strong></span>
            <span class="audit-meta">3rd attempt · throttling engaged · ip&nbsp;<code style="font-family:var(--font-mono);color:#67e8f9;">203.0.113.42</code></span>
            <span class="audit-time">42 min ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev violet">2fa.admin_reset</span> <strong style="color: var(--color-text-primary);">writer@piesci.pl</strong></span>
            <span class="audit-meta">actor → <strong style="color: var(--color-text-primary);">admin@agares.app</strong> (admin reset)</span>
            <span class="audit-time">1 h ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev cool">2fa.email_code.sent</span> <strong style="color: var(--color-text-primary);">moderator@piesci.pl</strong></span>
            <span class="audit-meta">ttl 10 min · hashed at rest · resend 1/60s</span>
            <span class="audit-time">3 h ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev ok">2fa.recovery_code.used</span> <strong style="color: var(--color-text-primary);">designer@studio.co</strong></span>
            <span class="audit-meta">code #5 of 8 · remaining=3 · single-use</span>
            <span class="audit-time">5 h ago</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev ok">2fa.enrolled</span> <strong style="color: var(--color-text-primary);">newhire@agares.app</strong></span>
            <span class="audit-meta">method=totp · Bitwarden · enforced by role policy</span>
            <span class="audit-time">yesterday</span>
          </div>
          <div class="audit-row">
            <span class="audit-event"><span class="ev violet">2fa.recovery_codes.regen</span> <strong style="color: var(--color-text-primary);">owner@agares.app</strong></span>
            <span class="audit-meta">old codes invalidated · 8 new generated</span>
            <span class="audit-time">2 days ago</span>
          </div>
        </div>
      </div>

      <p style="text-align: center; margin-top: var(--space-lg); font-family: var(--font-mono); font-size: 0.78rem; color: var(--color-text-tertiary);">
        Schema: <code style="color:#c4b5fd;">id · user_id · actor_id · event · ip · user_agent · metadata json · created_at</code>
      </p>
    </div>
  </section>

  {{-- ============ DEFENSE IN DEPTH ============ --}}
  <section>
    <div class="container-wide">
      <div class="section-header">
        <span class="eyebrow">Defense in depth</span>
        <h2>It's not just auth.<br><span class="text-gradient">The whole stack is hardened.</span></h2>
        <p>Every layer has its own seatbelt. None of these are configurable hacks — they're how the project ships.</p>
      </div>

      <div class="defense-grid">
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
          <h4>safe_html() everywhere</h4>
          <p>Every admin-authored rich-text field is stripped of <code style="font-family:var(--font-mono);">on*</code> handlers, <code style="font-family:var(--font-mono);">javascript:</code> + <code style="font-family:var(--font-mono);">data:</code> URIs before render.</p>
        </div>
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
          <h4>Upload MIME allowlist</h4>
          <p>No <code style="font-family:var(--font-mono);">.php</code>, <code style="font-family:var(--font-mono);">.phar</code>, <code style="font-family:var(--font-mono);">.phtml</code>. Authenticated user does not equal trusted upload.</p>
        </div>
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
          <h4>Encrypted at rest</h4>
          <p>TOTP secrets &amp; recovery codes are encrypted via Laravel <code style="font-family:var(--font-mono);">encrypted</code> casts. DB leak ≠ live 2FA bypass.</p>
        </div>
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
          <h4>Per-site authorisation</h4>
          <p>Custom-field mutations call <code style="font-family:var(--font-mono);color:#67e8f9;">canOn('edit', $site)</code>. A user with one site's edit rights cannot touch another's.</p>
        </div>
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
          <h4>Webhook hard-fail</h4>
          <p>Stripe / PayU / P24 / PayPal / Newsletter webhooks 500 immediately if signing secret is missing — silent acceptance is worse than downtime.</p>
        </div>
        <div class="defense-card reveal">
          <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg></div>
          <h4>Write-only secrets</h4>
          <p>API keys + webhook secrets in settings forms never echo back. Submit empty = keep existing. Submit <code style="font-family:var(--font-mono);">_clear</code> = wipe.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ GDPR / COOKIES STRIP ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal" style="text-align: left; padding: clamp(2rem, 4vw, 3rem);">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-2xl); align-items: center;">
          <div>
            <span class="badge badge-cyan mb-md">GDPR &amp; cookies</span>
            <h2 style="font-size: clamp(1.75rem, 3vw, 2.25rem); margin-bottom: var(--space-md);">Compliance isn't an add-on either.</h2>
            <p style="color: var(--color-text-secondary); font-size: var(--text-base); margin-bottom: 0; line-height: 1.65;">
              The cookie module ships with a live page scanner, a granular consent UI, third-party script gating
              and a consent audit log. Newsletter signups capture <code style="font-family:var(--font-mono);color:#67e8f9;">consent_text</code> /
              <code style="font-family:var(--font-mono);color:#67e8f9;">consent_ip</code> / <code style="font-family:var(--font-mono);color:#67e8f9;">consent_user_agent</code> on every row.
            </p>
          </div>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <span class="badge badge-cyan">Cookie scanner</span>
            <span class="badge badge-cyan">Script gating</span>
            <span class="badge badge-cyan">Consent audit log</span>
            <span class="badge badge-cyan">Newsletter consent snapshot</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FINAL CTA ============ --}}
  <section>
    <div class="container">
      <div class="cta-banner reveal">
        <span class="badge badge-primary mb-md">Trust, verified</span>
        <h2>Walk every screen. Read every audit&nbsp;row.<br>Try to break it.</h2>
        <p>The demo lets you read the audit log, the permission matrix and the 2FA flows yourself. We dare you to find a gap.</p>
        <div class="hero-buttons">
          <button type="button" class="btn btn-primary btn-lg btn-icon-after" data-demo-open>
            Open the demo
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
          <a href="/contact" class="btn btn-secondary btn-lg">Talk to us</a>
        </div>
      </div>
    </div>
  </section>

@stop
