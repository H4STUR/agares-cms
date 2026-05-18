{{-- Global demo modal — triggered by any element with data-demo-open --}}
<div id="demo-modal" class="demo-modal" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title" aria-hidden="true">
  <div class="demo-modal__backdrop" data-demo-close></div>
  <div class="demo-modal__panel" role="document">
    <button class="demo-modal__close" data-demo-close aria-label="Close demo modal">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 6L6 18M6 6l12 12"/>
      </svg>
    </button>

    <div class="demo-modal__glow" aria-hidden="true"></div>

    <div class="demo-modal__eyebrow">
      <span class="pulse-dot"></span>
      <span>Read-only tour</span>
    </div>

    <h2 id="demo-modal-title" class="demo-modal__title">
      The live demo is being polished
    </h2>

    <p class="demo-modal__lead">
      In a few days you'll get full access to the admin panel as a read-only viewer — walk through every screen,
      every module, click every button. Nothing you do will affect the demo content.
    </p>

    <div class="demo-modal__cards">
      <div class="demo-modal__card">
        <div class="demo-modal__card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
          </svg>
        </div>
        <div>
          <strong>Multi-site dashboard</strong>
          <span>Switch between client sites, manage menus &amp; categories.</span>
        </div>
      </div>
      <div class="demo-modal__card">
        <div class="demo-modal__card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div>
          <strong>Roles &amp; permissions</strong>
          <span>See the full RBAC matrix, 2FA controls, audit log.</span>
        </div>
      </div>
      <div class="demo-modal__card">
        <div class="demo-modal__card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
            <line x1="12" y1="22.08" x2="12" y2="12"/>
          </svg>
        </div>
        <div>
          <strong>Ecommerce &amp; payments</strong>
          <span>Stripe, PayU, P24 and PayPal — already wired up.</span>
        </div>
      </div>
    </div>

    <div class="demo-modal__actions">
      <button type="button" class="btn btn-primary" disabled aria-disabled="true" title="Demo user being prepared">
        <span class="pulse-dot pulse-dot--inline"></span>
        Log in as demo user
        <span class="demo-modal__soon">coming soon</span>
      </button>
      <a href="/contact" class="btn btn-secondary">
        Talk to us instead
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
      </a>
    </div>

    <p class="demo-modal__foot">
      Want the source? <a href="https://github.com/H4STUR" target="_blank" rel="noopener">It's on GitHub →</a>
    </p>
  </div>
</div>

<style>
.demo-modal {
  position: fixed; inset: 0; z-index: 2000;
  display: none;
  align-items: center; justify-content: center;
  padding: 1rem;
}
.demo-modal.is-open { display: flex; animation: demo-fade 220ms ease both; }
@keyframes demo-fade { from { opacity: 0; } to { opacity: 1; } }

.demo-modal__backdrop {
  position: absolute; inset: 0;
  background: rgba(5, 7, 13, 0.75);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.demo-modal__panel {
  position: relative;
  width: 100%;
  max-width: 560px;
  padding: clamp(1.5rem, 4vw, 2.5rem);
  background:
    radial-gradient(ellipse 80% 60% at 50% 0%, rgba(139, 92, 246, 0.18), transparent 70%),
    linear-gradient(180deg, rgba(22, 27, 39, 0.95), rgba(11, 14, 22, 0.95));
  border: 1px solid var(--color-border-hover);
  border-radius: var(--radius-2xl);
  box-shadow: 0 50px 120px -30px rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(139, 92, 246, 0.15);
  animation: demo-rise 320ms cubic-bezier(0.4, 0, 0.2, 1) both;
}
@keyframes demo-rise { from { opacity: 0; transform: translateY(20px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

.demo-modal__glow {
  position: absolute;
  top: -50px; left: 50%; transform: translateX(-50%);
  width: 70%; height: 100px;
  background: radial-gradient(ellipse, rgba(139, 92, 246, 0.4), transparent 70%);
  filter: blur(40px);
  pointer-events: none;
}

.demo-modal__close {
  position: absolute;
  top: 1rem; right: 1rem;
  width: 36px; height: 36px;
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 50%;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all var(--transition-base);
}
.demo-modal__close:hover { color: var(--color-text-primary); background: var(--color-surface-hover); border-color: var(--color-border-hover); }

.demo-modal__eyebrow {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.35rem 0.85rem;
  background: rgba(139, 92, 246, 0.12);
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: var(--radius-full);
  font-family: var(--font-mono);
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #c4b5fd;
  margin-bottom: var(--space-md);
}

.pulse-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.6);
  animation: pulse-dot 1.8s ease infinite;
}
.pulse-dot--inline { background: rgba(255, 255, 255, 0.7); box-shadow: none; animation: none; }
@keyframes pulse-dot {
  0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.6); }
  70% { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); }
  100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
}

.demo-modal__title {
  font-family: var(--font-display);
  font-size: clamp(1.5rem, 3vw, 1.85rem);
  line-height: 1.15;
  letter-spacing: -0.025em;
  margin-bottom: var(--space-sm);
}
.demo-modal__lead { color: var(--color-text-secondary); font-size: var(--text-base); line-height: 1.65; margin-bottom: var(--space-xl); }

.demo-modal__cards {
  display: grid;
  gap: 0.6rem;
  margin-bottom: var(--space-xl);
}
.demo-modal__card {
  display: flex; gap: 0.85rem; align-items: flex-start;
  padding: 0.85rem 1rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
}
.demo-modal__card strong { display: block; color: var(--color-text-primary); font-family: var(--font-display); font-size: var(--text-sm); font-weight: 600; margin-bottom: 2px; }
.demo-modal__card span { display: block; color: var(--color-text-tertiary); font-size: 0.8rem; line-height: 1.5; }
.demo-modal__card-icon {
  flex-shrink: 0; width: 32px; height: 32px;
  display: inline-flex; align-items: center; justify-content: center;
  border-radius: var(--radius-sm);
  background: rgba(139, 92, 246, 0.12);
  border: 1px solid rgba(139, 92, 246, 0.25);
  color: var(--color-accent-primary);
}

.demo-modal__actions { display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: var(--space-lg); }
.demo-modal__actions .btn[disabled] {
  background: var(--color-surface-strong);
  color: var(--color-text-secondary);
  cursor: not-allowed;
  box-shadow: none;
  border: 1px solid var(--color-border-hover);
}
.demo-modal__actions .btn[disabled]:hover { transform: none; }
.demo-modal__soon {
  margin-left: 0.4rem;
  padding: 0.15rem 0.45rem;
  background: rgba(251, 191, 36, 0.15);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fde68a;
  font-family: var(--font-mono);
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  border-radius: var(--radius-full);
}

.demo-modal__foot { font-size: var(--text-sm); color: var(--color-text-tertiary); margin: 0; text-align: center; }
.demo-modal__foot a { color: var(--color-accent-secondary); font-weight: 500; }
</style>

<script>
(function() {
  const modal = document.getElementById('demo-modal');
  if (!modal) return;
  let lastFocus = null;

  function open(trigger) {
    lastFocus = trigger || document.activeElement;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('.demo-modal__close')?.focus(), 50);
  }
  function close() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-demo-open]');
    if (opener) { e.preventDefault(); open(opener); return; }
    if (e.target.closest('[data-demo-close]')) { e.preventDefault(); close(); }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
  });
})();
</script>
