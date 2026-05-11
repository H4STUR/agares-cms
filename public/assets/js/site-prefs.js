(function () {
  const STORAGE_KEY = 'site_prefs_v1';

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

    if (window.AgaresConsent && typeof window.AgaresConsent.setConsent === 'function') {
      window.AgaresConsent.setConsent(payload);
    }
  }

  // Accepts both new (data-sp-category) and legacy (data-cookie-category) attributes
  // so any client-injected scripts using the old attribute keep working.
  function gatedScripts() {
    return document.querySelectorAll('script[data-sp-category], script[data-cookie-category]');
  }

  function categoryOf(script) {
    return script.dataset.spCategory || script.dataset.cookieCategory;
  }

  function blockScripts() {
    gatedScripts().forEach(s => {
      s.type = 'text/plain';
    });
  }

  function allowScripts(consent) {
    gatedScripts().forEach(script => {
      const cat = categoryOf(script);
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

  function btn(html, id, variant) {
    const v = variant || 'primary';
    return `<button type="button" class="sp-btn sp-btn--${v}" id="${id}">${html}</button>`;
  }

  function toggleSwitch(cat, checked, disabled) {
    const dis = disabled ? 'disabled' : '';
    const chk = checked ? 'checked' : '';
    return `
      <label class="sp-switch" aria-label="${esc(CATEGORY_LABELS[cat] || cat)}">
        <input type="checkbox" data-cat="${cat}" ${chk} ${dis}>
        <span class="sp-slider"></span>
      </label>
    `;
  }

  function formatExpiry(c) {
    if (c.session) return 'Session';
    if (!c.expires) return '';
    const d = new Date(c.expires);
    if (!isNaN(d.getTime())) return d.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' });
    return c.expires;
  }

  function itemListHtml(items) {
    if (!items || !items.length) {
      return `<div class="sp-muted">No entries detected in this category (last scan).</div>`;
    }

    return `
      <div class="sp-item-list">
        ${items.map(c => {
          const expiry = formatExpiry(c);
          return `
          <div class="sp-item">
            <div class="sp-item-name"><code>${esc(c.name)}</code></div>
            <div class="sp-item-meta">${esc(c.domain || '')}${expiry ? ` · <span class="sp-item-expires">${esc(expiry)}</span>` : ''}</div>
            <div class="sp-item-desc">${esc(c.description || 'No description')}</div>
          </div>`;
        }).join('')}
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
      <div class="sp-acc-item">
        <button class="sp-acc-header" type="button" aria-expanded="${idx === 0 ? 'true' : 'false'}">
          <div class="sp-acc-left">
            <div class="sp-acc-title">${esc(title)}</div>
            <div class="sp-acc-sub">${esc(cfgCat?.description || '')}</div>
          </div>
          <div class="sp-acc-right">
            ${toggleSwitch(cat, checked, locked)}
            <span class="sp-acc-chevron">▾</span>
          </div>
        </button>
        <div class="sp-acc-body" ${idx === 0 ? '' : 'hidden'}>
          ${itemListHtml(items)}
        </div>
      </div>
    `;
  }

  function showFloatingButton(cfg) {
    if (document.getElementById('sp-fab')) return;

    const btnEl = document.createElement('button');
    btnEl.id = 'sp-fab';
    btnEl.type = 'button';
    btnEl.className = 'sp-fab';
    btnEl.title = cfg?.buttons?.manage || 'Privacy settings';
    btnEl.setAttribute('aria-label', btnEl.title);
    btnEl.innerHTML = '<i class="material-icons-outlined">cookie</i>';

    document.body.appendChild(btnEl);

    btnEl.addEventListener('click', () => {
      openManageModalOnly();
    });
  }

  function lockBodyScroll() {
    if (document.body.dataset.spLocked === '1') return;
    document.body.dataset.spLocked = '1';
    document.body.dataset.spPrevOverflow = document.body.style.overflow || '';
    document.body.style.overflow = 'hidden';
  }

  function unlockBodyScroll() {
    if (document.body.dataset.spLocked !== '1') return;
    document.body.style.overflow = document.body.dataset.spPrevOverflow || '';
    delete document.body.dataset.spLocked;
    delete document.body.dataset.spPrevOverflow;
  }

  function openPanel(panel) {
    panel.hidden = false;
    lockBodyScroll();
  }

  function closePanel(panel) {
    panel.hidden = true;
    unlockBodyScroll();
  }

  function wireAccordionAndSave(panel, cfg) {
    panel.querySelectorAll('.sp-acc-header').forEach(header => {
      header.addEventListener('click', (e) => {
        if (e.target && e.target.closest?.('.sp-switch')) return;

        const body = header.parentElement.querySelector('.sp-acc-body');
        const expanded = header.getAttribute('aria-expanded') === 'true';
        header.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        body.hidden = expanded;
      });
    });

    const saveBtn = panel.querySelector('#sp-save');
    if (saveBtn) {
      saveBtn.onclick = () => {
        const consent = {};

        panel.querySelectorAll('#sp-acc input[data-cat]').forEach(inp => {
          consent[inp.dataset.cat] = inp.checked;
        });

        consent.essential = true;

        applyChoice(consent);
        showFloatingButton(cfg);

        // Saving the preferences IS the consent decision — dismiss the notice too
        const notice = document.getElementById('site-prefs-notice');
        if (notice) notice.remove();

        closePanel(panel);
      };
    }
  }

  function openManageModalOnly() {
    Promise.all([
      fetch('/api/site-prefs/config').then(r => r.json()),
      fetch('/api/site-prefs/catalog').then(r => r.json()).catch(() => ({ categories: {} })),
    ]).then(([cfg, catalog]) => {
      if (!cfg?.enabled) return;

      let panel = document.getElementById('site-prefs-panel');
      if (panel) {
        openPanel(panel);
        return;
      }

      const storedConsent = getConsent();

      const root = document.getElementById('site-prefs-root') || document.body;
      const wrap = document.createElement('div');

      wrap.innerHTML = `
        <div id="site-prefs-panel" class="sp-panel">
          <div class="sp-panel-backdrop"></div>
          <div class="sp-panel-card" role="dialog" aria-modal="true" aria-label="Manage preferences">
            <div class="sp-panel-head">
              <div>
                <div class="sp-panel-title">${esc(cfg.title)}</div>
                <div class="sp-muted">Choose which categories you want to allow.</div>
              </div>
              <button type="button" class="sp-close" id="sp-close" aria-label="Close">✕</button>
            </div>

            <div class="sp-acc" id="sp-acc">
              ${['essential','functional','analytics','marketing'].map((cat, idx) =>
                accordionItemHtml(cat, cfg.categories?.[cat], catalog?.categories?.[cat], idx, storedConsent)
              ).join('')}
            </div>

            <div class="sp-panel-actions">
              ${btn(esc(cfg.buttons.save), 'sp-save', 'primary')}
            </div>
          </div>
        </div>
      `;

      root.appendChild(wrap);

      panel = document.getElementById('site-prefs-panel');
      const close = () => closePanel(panel);

      panel.querySelector('#sp-close').onclick = close;
      panel.querySelector('.sp-panel-backdrop').onclick = close;

      wireAccordionAndSave(panel, cfg);
      openPanel(panel);
    });
  }

  Promise.all([
    fetch('/api/site-prefs/config').then(r => r.json()),
    fetch('/api/site-prefs/catalog').then(r => r.json()).catch(() => ({ categories: {} })),
  ]).then(([cfg, catalog]) => {
    if (!cfg?.enabled) return;

    const existing = getConsent();
    if (existing && cfg.remember_consent) {
      allowScripts(existing);
      showFloatingButton(cfg);
      return;
    }

    if (cfg.block_until_choice) blockScripts();

    const root = document.getElementById('site-prefs-root');
    if (!root) return;

    const storedConsent = existing;

    root.innerHTML = `
      <div id="site-prefs-notice" class="sp-notice" role="dialog" aria-label="Privacy preferences">
        ${cfg.logo_url ? `<div class="sp-notice-logo"><img src="${esc(cfg.logo_url)}" alt="Site logo"></div>` : ''}
        <div class="sp-notice-title">${esc(cfg.title)}</div>
        ${cfg.message ? `<div class="sp-notice-msg">${cfg.message}</div>` : ''}

        <div class="sp-notice-actions">
          ${btn(esc(cfg.buttons.accept_all), 'sp-accept-all', 'primary')}
          ${btn(esc(cfg.buttons.reject_all), 'sp-reject-all', 'secondary')}
          ${btn(esc(cfg.buttons.manage), 'sp-manage', 'ghost')}
        </div>
      </div>

      <div id="site-prefs-panel" class="sp-panel" hidden>
        <div class="sp-panel-backdrop"></div>
        <div class="sp-panel-card" role="dialog" aria-modal="true" aria-label="Manage preferences">
          <div class="sp-panel-head">
            <div>
              <div class="sp-panel-title">${esc(cfg.title)}</div>
              <div class="sp-muted">Choose which categories you want to allow.</div>
            </div>
            <button type="button" class="sp-close" id="sp-close" aria-label="Close">✕</button>
          </div>

          <div class="sp-acc" id="sp-acc">
            ${['essential','functional','analytics','marketing'].map((cat, idx) =>
              accordionItemHtml(cat, cfg.categories?.[cat], catalog?.categories?.[cat], idx, storedConsent)
            ).join('')}
          </div>

          <div class="sp-panel-actions">
            ${btn(esc(cfg.buttons.save), 'sp-save', 'primary')}
          </div>
        </div>
      </div>
    `;

    const notice = document.getElementById('site-prefs-notice');
    const panel  = document.getElementById('site-prefs-panel');

    document.getElementById('sp-accept-all').onclick = () => {
      applyChoice({ functional:true, analytics:true, marketing:true });
      showFloatingButton(cfg);
      notice.remove();
    };

    document.getElementById('sp-reject-all').onclick = () => {
      applyChoice({ functional:false, analytics:false, marketing:false });
      showFloatingButton(cfg);
      notice.remove();
    };

    document.getElementById('sp-manage').onclick = () => openPanel(panel);
    document.getElementById('sp-close').onclick = () => closePanel(panel);
    panel.querySelector('.sp-panel-backdrop').onclick = () => closePanel(panel);

    wireAccordionAndSave(panel, cfg);
  });

})();
