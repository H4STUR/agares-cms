<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    {{-- SEO --}}
        @include('pages.frontend.snippets.google-analytics')
        @include('pages.frontend.snippets.seo')
    {{-- !_SEO --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
    {{-- Display + body + mono fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-nav.css') }}">

    {{-- theme --}}
    <link rel="stylesheet" href="{{ asset('assets/frontend/theme/assets/css/styles.css') }}?v={{ config('app.version', '2') }}">

    {{-- custom css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    {{-- bootstrap compatibility patch --}}
    <link rel="stylesheet" href="{{ asset('assets/frontend/theme/assets/css/bootstrap-patch.css') }}">

    {{-- !_custom css --}}
    <style>{!! $customStyle !!}</style>

    @stack('styles')
</head>
<body>
  <div class="agares-theme">
    {{-- admin nav --}}
      @include('pages.frontend.snippets.admin-bar')
    {{-- !_admin nav --}}

    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="navbar-container">
            <a href="/" class="navbar-logo" aria-label="Agares CMS Home">
                <img class="logo" src="{{ asset('/assets/admin/images/agares-logo.png') }}" alt="Agares CMS logo">
                <span>Agares</span>
            </a>

            <ul class="navbar-menu">
              @include('navigation.nav', ['items' => \App\Support\MenuTree::byName('Main Menu')])
              <li>
                <button type="button" class="navbar-cta" data-demo-open>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                  </svg>
                  Try the Demo
                </button>
              </li>
            </ul>

            <button class="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <div class="page">
      @yield('content')
    </div>

    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <div class="navbar-logo" style="margin-bottom: var(--space-md);">
              <img class="logo" src="{{ asset('/assets/admin/images/agares-logo.png') }}" alt="Agares CMS logo">
              <span>Agares</span>
            </div>
            <p style="color: var(--color-text-secondary); max-width: 320px; font-size: var(--text-sm);">The modern multi-site content management system for developers, agencies and creators.</p>
            <div class="flex gap-sm" style="margin-top: var(--space-md);">
              <span class="badge badge-success">Demo Live</span>
              <span class="badge badge-cyan">v2.0</span>
            </div>
          </div>

          <div class="footer-section">
            <h4>Product</h4>
            <ul class="footer-links">
              <li><a href="/features">Features</a></li>
              <li><a href="/pricing">Pricing</a></li>
              <li><a href="/security">Security</a></li>
              <li><a href="/projects">Showcase</a></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Build</h4>
            <ul class="footer-links">
              <li><a href="/page-editor">Page Editor</a></li>
              <li><a href="/blog">Blog Engine</a></li>
              <li><a href="/documentation">Documentation</a></li>
              <li><a href="/demo">Try Demo</a></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Company</h4>
            <ul class="footer-links">
              <li><a href="/contact">Contact</a></li>
              <li><a href="https://github.com/H4STUR" target="_blank" rel="noopener">GitHub</a></li>
              <li><a href="https://www.linkedin.com/in/lukasz-majerski/" target="_blank" rel="noopener">LinkedIn</a></li>
            </ul>
          </div>

          <div class="footer-section">
            <h4>Legal</h4>
            <ul class="footer-links">
              <li><a href="/privacy-policy">Privacy</a></li>
              <li><a href="/terms-of-service">Terms</a></li>
              <li><a href="/cookie-policy">Cookies</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p style="color: var(--color-text-tertiary); margin: 0; font-size: var(--text-sm);">© {{ date('Y') }} Agares. Crafted with intent.</p>
          <div class="footer-social">
            <a href="https://github.com/H4STUR" target="_blank" rel="noopener" class="social-link" aria-label="GitHub">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
              </svg>
            </a>
            <a href="https://www.linkedin.com/in/lukasz-majerski/" target="_blank" rel="noopener" class="social-link" aria-label="LinkedIn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                <circle cx="4" cy="4" r="2"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>

  {{-- bootstrap --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- theme --}}
  <script src="{{ asset('assets/frontend/theme/assets/js/main.js') }}"></script>

  {{-- nav scroll state + card spotlight + reveal-on-scroll --}}
  <script>
    (function () {
      // 1) Navbar shadow when scrolled
      const nav = document.querySelector('.navbar');
      if (nav) {
        const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 12);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
      }

      // 2) Mouse spotlight on cards / bento items
      document.querySelectorAll('.card, .bento-item').forEach((el) => {
        el.addEventListener('mousemove', (e) => {
          const r = el.getBoundingClientRect();
          el.style.setProperty('--mouse-x', ((e.clientX - r.left) / r.width * 100) + '%');
          el.style.setProperty('--mouse-y', ((e.clientY - r.top) / r.height * 100) + '%');
        });
      });

      // 3) Reveal-on-scroll: auto-tag major sections + any .reveal element
      const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reduced || !('IntersectionObserver' in window)) return;

      // Auto-tag: section headers, bento items, persona cards, pricing, quote
      const auto = document.querySelectorAll(
        '.section-header, .bento-hero, .bento-item, .persona-card, .pricing-card, .quote-card, .stat-strip, .cta-banner, .code-window, .split-image, .step, .feature-card, .reveal'
      );
      auto.forEach((el) => el.classList.add('reveal'));

      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

      document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
    })();
  </script>

  {{-- global demo modal --}}
  @include('pages.frontend.partials.demo_modal')

  {{-- privacy preferences --}}
  <link rel="stylesheet" href="{{ asset('assets/css/site-prefs.css') }}?v={{ config('app.version', '1') }}">
  <div id="site-prefs-root"></div>
  <script src="{{ asset('assets/js/site-prefs.js') }}?v={{ config('app.version', '1') }}"></script>

  @stack('scripts')

  {!! $customScript !!}
</body>
</html>
