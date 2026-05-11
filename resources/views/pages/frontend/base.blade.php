<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    {{-- SEO --}}
        
        @include('pages.frontend.snippets.google-analytics')
        @include('pages.frontend.snippets.seo')
        
    {{-- !_SEO --}}

    <!-- icons -->
    <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-nav.css') }}">

    {{-- theme --}}
    <link rel="stylesheet" href="{{ asset('assets/frontend/theme/assets/css/styles.css') }}">

    {{-- custom css --}}
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    {{-- bootstrap compatibility patch (NEW) --}}
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
                <img class="logo" width="50" src="{{ asset('/assets/admin/images/agares-logo.png'); }}">
                Agares
            </a>

            <ul class="navbar-menu">
              @include('navigation.nav', ['items' => \App\Support\MenuTree::byName('Main Menu')])
              {{-- <li><a href="/admin" target="_blank" class="btn btn-primary btn-sm">Try Demo</a></li> --}}
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
    
    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <div class="navbar-logo" style="margin-bottom: var(--space-lg);">
              <img class="logo" width="50" src="{{ asset('/assets/admin/images/agares-logo.png'); }}">
              Agares
            </div>
            <p style="color: var(--color-text-tertiary);">The modern content management system for developers and creators.</p>
          </div>
          
          <div class="footer-section">
            <h4>Features</h4>
            <ul class="footer-links">
              <li><a href="/page-editor">Page Editor</a></li>
              <li><a href="/blog">Blog</a></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Resources</h4>
            <ul class="footer-links">
              <li><a href="/documentation">Documentation</a></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Legal</h4>
            <ul class="footer-links">
              <li><a href="/privacy-policy">Privacy Policy</a></li>
              <li><a href="/terms-of-service">Terms of Service</a></li>
              <li><a href="/cookie-policy">Cookie Policy</a></li>
            </ul>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p style="color: var(--color-text-tertiary); margin: 0;">Copyright &copy; {{ date('Y') }} Agares. All rights reserved.</p>
          <div class="footer-social">
            {{-- <a href="#" class="social-link" aria-label="Twitter">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
              </svg>
            </a> --}}
            <a href="https://github.com/H4STUR" target="_blank" class="social-link" aria-label="GitHub">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
              </svg>
            </a>
            <a href="https://www.linkedin.com/in/lukasz-majerski/" target="_blank" class="social-link" aria-label="LinkedIn">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
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

  {{-- privacy preferences (neutral selectors so Brave/EasyList don't hide them) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/site-prefs.css') }}?v={{ config('app.version', '1') }}">
  <div id="site-prefs-root"></div>
  <script src="{{ asset('assets/js/site-prefs.js') }}?v={{ config('app.version', '1') }}"></script>


  @stack('scripts')

  {{-- !_custom css --}}
  {!! $customScript !!}
</body>
</html>
