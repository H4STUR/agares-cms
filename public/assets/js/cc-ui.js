(function () {
  const STORAGE_KEY = 'cookie_consent_v1';

  const CATEGORY_LABELS = {
    essential: 'Essential',
    functional: 'Functional',
    analytics: 'Analytics',
    marketing: 'Marketing',
  };

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  function normalizeConsent(input) {
    const obj = input && typeof input === 'object' ? input : {};
    return {
      essential: true,
      functional: !!obj.functional,
      analytics: !!obj.analytics,
      marketing: !!obj.marketing,
    };
  }

  function getConsent() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      return normalizeConsent(JSON.parse(raw));
    } catch {
      return null;
    }
  }

  function saveConsent(data) {
    const payload = normalizeConsent(data);

    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      ...payload,
      savedAt: new Date().toISOString()
    }));

    // Notify Google Consent Mode mapping (your GA snippet)
    if (window.AgaresConsent && typeof window.AgaresConsent.setConsent === 'function') {
      window.AgaresConsent.setConsent(payload);
    }
  }

  function blockScripts() {
    document.querySelectorAll('script[data-cookie-category]').forEach(s => {
      s.type = 'text/plain';
    });
  }

  function allowScripts(consent) {
    document.querySelectorAll('script[data-cookie-category]').forEach(script => {
      const cat = script.dataset.cookieCategory;
      if (consent?.[cat]) {
        const s = document.createElement('script');
        if (script.src) s.src = script.src;
        if (script.innerHTML) s.innerHTML = script.innerHTML;
        script.replaceWith(s);
      }
    });
  }

  function applyChoice(consent) {
    const normalized = normalizeConsent(consent);
    saveConsent(normalized);
    allowScripts(normalized);
  }

  function btn(html, id) {
    return `<button type="button" class="cc-btn" id="${id}">${html}</button>`;
  }

  function toggleSwitch(cat, checked, disabled) {
    const dis = disabled ? 'disabled' : '';
    const chk = checked ? 'checked' : '';
    return `
      <label class="cc-switch" aria-label="${esc(CATEGORY_LABELS[cat] || cat)}">
        <input type="checkbox" data-cat="${cat}" ${chk} ${dis}>
        <span class="cc-slider"></span>
      </label>
    `;
  }

  function cookieListHtml(items) {
    if (!items || !items.length) {
      return `<div class="cc-muted">No cookies detected in this category (last scan).</div>`;
    }

    return `
      <div class="cc-cookie-list">
        ${items.map(c => `
          <div class="cc-cookie-item">
            <div class="cc-cookie-name"><code>${esc(c.name)}</code></div>
            <div class="cc-cookie-meta">${esc(c.domain || '')}</div>
            <div class="cc-cookie-desc">${esc(c.description || 'No description')}</div>
          </div>
        `).join('')}
      </div>
    `;
  }

  function accordionItemHtml(cat, cfgCat, items, idx, storedConsent) {
    const title = CATEGORY_LABELS[cat] || cat;
    const locked = !!cfgCat?.locked;

    const checked = locked
      ? true
      : (storedConsent ? !!storedConsent[cat] : !!cfgCat?.enabled);

    return `
      <div class="cc-acc-item">
        <button class="cc-acc-header" type="button" aria-expanded="${idx === 0 ? 'true' : 'false'}">
          <div class="cc-acc-left">
            <div class="cc-acc-title">${esc(title)}</div>
            <div class="cc-acc-sub">${esc(cfgCat?.description || '')}</div>
          </div>
          <div class="cc-acc-right">
            ${toggleSwitch(cat, checked, locked)}
            <span class="cc-acc-chevron">▾</span>
          </div>
        </button>
        <div class="cc-acc-body" ${idx === 0 ? '' : 'hidden'}>
          ${cookieListHtml(items)}
        </div>
      </div>
    `;
  }

  function showFloatingButton(cfg) {
    if (document.getElementById('cc-fab')) return;

    const btnEl = document.createElement('button');
    btnEl.id = 'cc-fab';
    btnEl.type = 'button';
    btnEl.className = 'cc-fab';
    btnEl.title = cfg?.buttons?.manage || 'Cookie settings';
    btnEl.setAttribute('aria-label', btnEl.title);
    btnEl.innerHTML = '<i class="material-icons-outlined">cookie</i>';

    document.body.appendChild(btnEl);

    btnEl.addEventListener('click', () => {
      openManageModalOnly();
    });
  }

  function wireAccordionAndSave(modal, cfg) {
    // Accordion behavior
    modal.querySelectorAll('.cc-acc-header').forEach(header => {
      header.addEventListener('click', (e) => {
        if (e.target && e.target.closest?.('.cc-switch')) return;

        const body = header.parentElement.querySelector('.cc-acc-body');
        const expanded = header.getAttribute('aria-expanded') === 'true';
        header.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        body.hidden = expanded;
      });
    });

    // Save
    const saveBtn = modal.querySelector('#cc-save');
    if (saveBtn) {
      saveBtn.onclick = () => {
        const consent = {};

        modal.querySelectorAll('#cc-acc input[data-cat]').forEach(inp => {
          consent[inp.dataset.cat] = inp.checked;
        });

        // essential always true
        consent.essential = true;

        applyChoice(consent);
        showFloatingButton(cfg);

        modal.hidden = true;
      };
    }
  }

  function openManageModalOnly() {
    Promise.all([
      fetch('/api/cookies/consent').then(r => r.json()),
      fetch('/api/cookies/catalog').then(r => r.json()).catch(() => ({ categories: {} })),
    ]).then(([cfg, catalog]) => {
      if (!cfg?.enabled) return;

      // if modal exists show it
      let modal = document.getElementById('cookie-modal');
      if (modal) {
        modal.hidden = false;
        return;
      }

      const storedConsent = getConsent();

      const root = document.getElementById('cookie-consent-root') || document.body;
      const wrap = document.createElement('div');

      wrap.innerHTML = `
        <div id="cookie-modal" class="cc-modal">
          <div class="cc-modal-backdrop"></div>
          <div class="cc-modal-card" role="dialog" aria-modal="true" aria-label="Manage cookies">
            <div class="cc-modal-head">
              <div>
                <div class="cc-modal-title">${esc(cfg.title)}</div>
                <div class="cc-muted">Choose which categories you want to allow.</div>
              </div>
              <button type="button" class="cc-x" id="cc-close" aria-label="Close">✕</button>
            </div>

            <div class="cc-acc" id="cc-acc">
              ${['essential','functional','analytics','marketing'].map((cat, idx) =>
                accordionItemHtml(cat, cfg.categories?.[cat], catalog?.categories?.[cat], idx, storedConsent)
              ).join('')}
            </div>

            <div class="cc-modal-actions">
              ${btn(esc(cfg.buttons.save), 'cc-save')}
            </div>
          </div>
        </div>
      `;

      root.appendChild(wrap);

      modal = document.getElementById('cookie-modal');
      const close = () => { modal.hidden = true; };

      modal.querySelector('#cc-close').onclick = close;
      modal.querySelector('.cc-modal-backdrop').onclick = close;

      wireAccordionAndSave(modal, cfg);
    });
  }

  // Initial boot
  Promise.all([
    fetch('/api/cookies/consent').then(r => r.json()),
    fetch('/api/cookies/catalog').then(r => r.json()).catch(() => ({ categories: {} })),
  ]).then(([cfg, catalog]) => {
    if (!cfg?.enabled) return;

    const existing = getConsent();
    if (existing && cfg.remember_consent) {
      allowScripts(existing);
      showFloatingButton(cfg);
      return;
    }

    if (cfg.block_until_choice) blockScripts();

    const root = document.getElementById('cookie-consent-root');
    if (!root) return;

    const storedConsent = existing; // if any

    root.innerHTML = `
      <div id="cookie-banner" class="cc-banner" role="dialog" aria-label="Cookie consent">
        <div class="cc-banner-title">${esc(cfg.title)}</div>
        ${cfg.message ? `<div class="cc-banner-msg">${cfg.message}</div>` : ''}

        <div class="cc-banner-actions">
          ${btn(esc(cfg.buttons.accept_all), 'cc-accept')}
          ${btn(esc(cfg.buttons.reject_all), 'cc-reject')}
          ${btn(esc(cfg.buttons.manage), 'cc-manage')}
        </div>
      </div>

      <div id="cookie-modal" class="cc-modal" hidden>
        <div class="cc-modal-backdrop"></div>
        <div class="cc-modal-card" role="dialog" aria-modal="true" aria-label="Manage cookies">
          <div class="cc-modal-head">
            <div>
              <div class="cc-modal-title">${esc(cfg.title)}</div>
              <div class="cc-muted">Choose which categories you want to allow.</div>
            </div>
            <button type="button" class="cc-x" id="cc-close" aria-label="Close">✕</button>
          </div>

          <div class="cc-acc" id="cc-acc">
            ${['essential','functional','analytics','marketing'].map((cat, idx) =>
              accordionItemHtml(cat, cfg.categories?.[cat], catalog?.categories?.[cat], idx, storedConsent)
            ).join('')}
          </div>

          <div class="cc-modal-actions">
            ${btn(esc(cfg.buttons.save), 'cc-save')}
          </div>
        </div>
      </div>
    `;

    const banner = document.getElementById('cookie-banner');
    const modal  = document.getElementById('cookie-modal');

    const open = () => { modal.hidden = false; };
    const close = () => { modal.hidden = true; };

    document.getElementById('cc-accept').onclick = () => {
      applyChoice({ functional:true, analytics:true, marketing:true });
      showFloatingButton(cfg);
      banner.remove();
    };

    document.getElementById('cc-reject').onclick = () => {
      applyChoice({ functional:false, analytics:false, marketing:false });
      showFloatingButton(cfg);
      banner.remove();
    };

    document.getElementById('cc-manage').onclick = () => open();
    document.getElementById('cc-close').onclick = () => close();
    modal.querySelector('.cc-modal-backdrop').onclick = () => close();

    wireAccordionAndSave(modal, cfg);
  });

})();
