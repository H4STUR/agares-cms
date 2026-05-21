/**
 * NEXARA DARK SHOP — theme.js v1.0.0
 * UI interactions — no backend, no framework dependencies
 */

(function () {
  'use strict';

  /* ===== HELPERS ===== */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
  const on = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);
  const toggle = (el, cls) => el && el.classList.toggle(cls);
  const add = (el, cls) => el && el.classList.add(cls);
  const remove = (el, cls) => el && el.classList.remove(cls);
  const has = (el, cls) => el && el.classList.contains(cls);

  /* ===== MOBILE MENU ===== */
  function initMobileMenu() {
    const btn = $('#mobileMenuToggle');
    const nav = $('#mobileNav');
    if (!btn || !nav) return;

    on(btn, 'click', () => {
      const isOpen = has(nav, 'open');
      toggle(nav, 'open');
      btn.setAttribute('aria-expanded', String(!isOpen));
      document.body.style.overflow = isOpen ? '' : 'hidden';
    });

    // Close on outside click
    on(document, 'click', (e) => {
      if (!nav.contains(e.target) && !btn.contains(e.target) && has(nav, 'open')) {
        remove(nav, 'open');
        btn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      }
    });
  }

  /* ===== HEADER SEARCH ===== */
  function initHeaderSearch() {
    const toggleBtn = $('#searchToggle');
    const searchBar = $('#headerSearch');
    const closeBtn = $('#closeSearch');
    if (!toggleBtn || !searchBar) return;

    on(toggleBtn, 'click', () => {
      toggle(searchBar, 'open');
      if (has(searchBar, 'open')) {
        const input = searchBar.querySelector('input');
        if (input) setTimeout(() => input.focus(), 50);
      }
    });

    on(closeBtn, 'click', () => remove(searchBar, 'open'));

    on(document, 'keydown', (e) => {
      if (e.key === 'Escape') remove(searchBar, 'open');
    });
  }

  /* ===== MINI CART DROPDOWN ===== */
  function initMiniCart() {
    const btn = $('#cartToggle');
    const cart = $('#miniCart');
    if (!btn || !cart) return;

    on(btn, 'click', (e) => {
      e.stopPropagation();
      toggle(cart, 'open');
    });

    on(document, 'click', (e) => {
      if (!cart.contains(e.target) && !btn.contains(e.target)) {
        remove(cart, 'open');
      }
    });
  }

  /* ===== QUANTITY CONTROLS ===== */
  function initQuantityControls() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('[data-qty]');
      if (!btn) return;

      const action = btn.dataset.qty;
      const input = btn.closest('.quantity-ctrl')?.querySelector('.qty-input')
                 || btn.closest('.cart-item')?.querySelector('.qty-input');
      if (!input) return;

      let val = parseInt(input.value, 10) || 1;
      const min = parseInt(input.min, 10) || 1;
      const max = parseInt(input.max, 10) || 999;

      if (action === 'inc') val = Math.min(val + 1, max);
      if (action === 'dec') val = Math.max(val - 1, min);

      input.value = val;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Prevent non-numeric input
    on(document, 'input', (e) => {
      if (!e.target.classList.contains('qty-input')) return;
      e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
  }

  /* ===== PRODUCT GALLERY ===== */
  function initProductGallery() {
    const thumbs = $$('.gallery-thumb');
    const mainImg = $('.gallery-main img');
    if (!thumbs.length || !mainImg) return;

    thumbs.forEach((thumb) => {
      on(thumb, 'click', () => {
        thumbs.forEach((t) => remove(t, 'active'));
        add(thumb, 'active');
        const src = thumb.dataset.full || thumb.querySelector('img')?.src;
        if (src) {
          mainImg.style.opacity = '0';
          setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
          }, 150);
          mainImg.style.transition = 'opacity 0.15s ease';
        }
      });
    });
  }

  /* ===== VARIANT SELECTORS ===== */
  function initVariantSelectors() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('.variant-size-btn');
      if (!btn) return;
      const group = btn.closest('.variant-size-options');
      if (!group) return;
      $$('.variant-size-btn', group).forEach((b) => remove(b, 'active'));
      add(btn, 'active');
      // Update label if present
      const labelSpan = btn.closest('.variant-group')?.querySelector('.variant-label span');
      if (labelSpan) labelSpan.textContent = btn.textContent.trim();
    });

    on(document, 'click', (e) => {
      const btn = e.target.closest('.variant-color-btn');
      if (!btn) return;
      const group = btn.closest('.variant-color-options');
      if (!group) return;
      $$('.variant-color-btn', group).forEach((b) => remove(b, 'active'));
      add(btn, 'active');
    });
  }

  /* ===== WISHLIST HEART TOGGLE ===== */
  function initWishlistToggle() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('.btn-wishlist');
      if (!btn) return;
      toggle(btn, 'active');
      showToast(
        has(btn, 'active') ? 'Added to Wishlist' : 'Removed from Wishlist',
        has(btn, 'active') ? 'success' : 'info'
      );
    });
  }

  /* ===== ADD TO CART ===== */
  function initAddToCart() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('[data-add-cart]');
      if (!btn) return;
      const name = btn.dataset.addCart || 'Item';
      showToast(`${name} added to cart!`, 'success');
      // Update cart badge count demo
      const badge = $('.cart-btn .badge');
      if (badge) {
        const count = parseInt(badge.textContent, 10) || 0;
        badge.textContent = count + 1;
      }
    });
  }

  /* ===== FAQ ACCORDION ===== */
  function initFaqAccordion() {
    const questions = $$('.faq-question');
    questions.forEach((q) => {
      on(q, 'click', () => {
        const item = q.closest('.faq-item');
        const isOpen = has(item, 'open');

        // Close all (single-open mode)
        $$('.faq-item').forEach((fi) => {
          remove(fi, 'open');
          fi.querySelector('.faq-icon').textContent = '+';
        });

        if (!isOpen) {
          add(item, 'open');
          q.querySelector('.faq-icon').textContent = '+';
        }
      });
    });
  }

  /* ===== PRODUCT TABS ===== */
  function initProductTabs() {
    const tabBtns = $$('.tab-btn');
    tabBtns.forEach((btn) => {
      on(btn, 'click', () => {
        const tabGroup = btn.closest('.product-tabs') || btn.closest('[data-tabs]');
        if (!tabGroup) return;
        const target = btn.dataset.tab;

        $$('.tab-btn', tabGroup).forEach((b) => remove(b, 'active'));
        add(btn, 'active');

        $$('.tab-panel', tabGroup).forEach((p) => remove(p, 'active'));
        const panel = $(`[data-tab-panel="${target}"]`, tabGroup);
        if (panel) add(panel, 'active');
      });
    });
  }

  /* ===== FILTER SIDEBAR (mobile) ===== */
  function initFilterSidebar() {
    const openBtn = $('.mobile-filter-btn');
    const sidebar = $('.filter-sidebar');
    const backdrop = $('.filter-backdrop');
    if (!openBtn || !sidebar) return;

    const close = () => {
      remove(sidebar, 'open');
      if (backdrop) remove(backdrop, 'open');
      document.body.style.overflow = '';
    };
    const open = () => {
      add(sidebar, 'open');
      if (backdrop) add(backdrop, 'open');
      document.body.style.overflow = 'hidden';
    };

    on(openBtn, 'click', open);
    on(backdrop, 'click', close);

    const closeBtn = $('.filter-sidebar-close');
    on(closeBtn, 'click', close);
  }

  /* ===== FILTER GROUP TOGGLE ===== */
  function initFilterGroups() {
    $$('.filter-group-header').forEach((header) => {
      on(header, 'click', () => {
        const group = header.closest('.filter-group');
        const toggleIcon = header.querySelector('.filter-group-toggle');
        const list = group.querySelector('.filter-list, .price-range, .color-swatches, .rating-filter');

        if (list) {
          const hidden = list.style.display === 'none';
          list.style.display = hidden ? '' : 'none';
          if (toggleIcon) {
            toggleIcon.textContent = hidden ? '▲' : '▼';
          }
        }
      });
    });
  }

  /* ===== QUICK VIEW MODAL ===== */
  function initQuickView() {
    const overlay = $('#quickViewModal');
    if (!overlay) return;

    on(document, 'click', (e) => {
      const trigger = e.target.closest('[data-quick-view]');
      if (trigger) {
        add(overlay, 'open');
        document.body.style.overflow = 'hidden';
      }
    });

    const closeBtns = $$('.modal-close', overlay);
    const closeModal = () => {
      remove(overlay, 'open');
      document.body.style.overflow = '';
    };

    closeBtns.forEach((btn) => on(btn, 'click', closeModal));
    on(overlay, 'click', (e) => { if (e.target === overlay) closeModal(); });
    on(document, 'keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  }

  /* ===== PASSWORD VISIBILITY TOGGLE ===== */
  function initPasswordToggle() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('[data-password-toggle]');
      if (!btn) return;
      const inputId = btn.dataset.passwordToggle;
      const input = inputId ? document.getElementById(inputId) : btn.closest('.input-icon-wrap')?.querySelector('input');
      if (!input) return;

      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';

      // Swap icon
      const eyeIcon = btn.querySelector('.eye-icon');
      const eyeOffIcon = btn.querySelector('.eye-off-icon');
      if (eyeIcon && eyeOffIcon) {
        eyeIcon.style.display = isHidden ? 'none' : '';
        eyeOffIcon.style.display = isHidden ? '' : 'none';
      }
    });
  }

  /* ===== PAYMENT OPTION SELECTION ===== */
  function initPaymentOptions() {
    $$('.payment-option').forEach((opt) => {
      on(opt, 'click', () => {
        $$('.payment-option').forEach((o) => remove(o, 'selected'));
        add(opt, 'selected');

        // Show/hide payment fields
        $$('.payment-fields').forEach((f) => add(f, 'hidden'));
        const targetId = opt.dataset.showFields;
        if (targetId) {
          const fields = document.getElementById(targetId);
          if (fields) remove(fields, 'hidden');
        }

        const radio = opt.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
      });
    });
  }

  /* ===== DELIVERY OPTION SELECTION ===== */
  function initDeliveryOptions() {
    $$('.delivery-option').forEach((opt) => {
      on(opt, 'click', () => {
        $$('.delivery-option').forEach((o) => remove(o, 'selected'));
        add(opt, 'selected');
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
      });
    });
  }

  /* ===== SAME BILLING ADDRESS ===== */
  function initSameBillingAddress() {
    const checkbox = $('#sameBillingAddress');
    const billingSection = $('#billingAddressSection');
    if (!checkbox || !billingSection) return;

    const update = () => {
      billingSection.style.display = checkbox.checked ? 'none' : '';
    };
    on(checkbox, 'change', update);
    update();
  }

  /* ===== CART ITEM REMOVE ===== */
  function initCartRemove() {
    on(document, 'click', (e) => {
      const btn = e.target.closest('.cart-remove-btn');
      if (!btn) return;
      const item = btn.closest('.cart-item, .wishlist-item, .mini-cart-item');
      if (!item) return;

      item.style.transition = 'opacity 0.22s ease, transform 0.22s ease';
      item.style.opacity = '0';
      item.style.transform = 'translateX(20px)';
      setTimeout(() => item.remove(), 220);
      showToast('Item removed', 'info');
    });
  }

  /* ===== SEARCH SUGGESTIONS ===== */
  function initSearch() {
    const inputs = $$('input[type="search"]');
    inputs.forEach((input) => {
      on(input, 'keydown', (e) => {
        if (e.key === 'Enter') {
          const val = input.value.trim();
          if (val && window.location.pathname.indexOf('search') === -1) {
            window.location.href = `search.html?q=${encodeURIComponent(val)}`;
          }
        }
      });
    });
  }

  /* ===== TOAST NOTIFICATIONS ===== */
  let toastContainer = null;

  function getToastContainer() {
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }
    return toastContainer;
  }

  function showToast(message, type = 'info', duration = 3000) {
    const container = getToastContainer();
    const icons = {
      success: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>`,
      error: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>`,
      info: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>`,
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <div class="toast-icon">${icons[type] || icons.info}</div>
      <div class="toast-body"><strong>${message}</strong></div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(30px)';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }

  // Expose globally
  window.showToast = showToast;

  /* ===== FAQ CATEGORY FILTER ===== */
  function initFaqCategories() {
    $$('.faq-cat-btn').forEach((btn) => {
      on(btn, 'click', () => {
        $$('.faq-cat-btn').forEach((b) => remove(b, 'active'));
        add(btn, 'active');
      });
    });
  }

  /* ===== ACTIVE NAV LINK ===== */
  function initActiveNav() {
    const path = window.location.pathname.split('/').pop() || 'index.html';
    $$('.nav-list a, .mobile-nav-list a, .account-nav a').forEach((link) => {
      const href = link.getAttribute('href');
      if (href && (href === path || href.includes(path.replace('.html', '')))) {
        add(link, 'active');
      }
    });
  }

  /* ===== STICKY HEADER SHADOW ===== */
  function initStickyHeader() {
    const header = $('.site-header');
    if (!header) return;
    on(window, 'scroll', () => {
      if (window.scrollY > 10) {
        header.style.boxShadow = '0 4px 32px rgba(0,0,0,0.6)';
      } else {
        header.style.boxShadow = 'none';
      }
    }, { passive: true });
  }

  /* ===== VIEW TOGGLE (grid/list) ===== */
  function initViewToggle() {
    $$('.view-btn').forEach((btn) => {
      on(btn, 'click', () => {
        const group = btn.closest('.view-btns');
        $$('.view-btn', group).forEach((b) => remove(b, 'active'));
        add(btn, 'active');

        const viewType = btn.dataset.view;
        const grid = $('.products-grid');
        if (!grid) return;

        if (viewType === 'list') {
          add(grid, 'products-list');
        } else {
          remove(grid, 'products-list');
        }
      });
    });
  }

  /* ===== INIT ALL ===== */
  function init() {
    initMobileMenu();
    initHeaderSearch();
    initMiniCart();
    initQuantityControls();
    initProductGallery();
    initVariantSelectors();
    initWishlistToggle();
    initAddToCart();
    initFaqAccordion();
    initProductTabs();
    initFilterSidebar();
    initFilterGroups();
    initQuickView();
    initPasswordToggle();
    initPaymentOptions();
    initDeliveryOptions();
    initSameBillingAddress();
    initCartRemove();
    initSearch();
    initFaqCategories();
    initActiveNav();
    initStickyHeader();
    initViewToggle();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
