
@extends('pages.frontend.base')
@section('content')


  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">{{ $data['header']->value ?? '' }}</h1>
        <p class="hero-subtitle">{!! safe_html($data['content']->value ?? '') !!}</p>
        {{-- <div class="hero-buttons">
          <a href="/contact" class="btn btn-primary btn-lg">Collaboration</a>
          <a href="/admin" target="_blank" class="btn btn-secondary btn-lg">View Live Demo</a>
        </div> --}}
      </div>
      
      <div class="hero-image">
        <div class="dashboard-preview">
          <div class="preview-bar">
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
            <span class="preview-dot"></span>
          </div>
          <div class="preview-content" style="padding: 0px;">
            @php $img = $data['preview_img'] ?? null; @endphp

            @if($img && $img->files && $img->files->count())
              <img class="w-100"src="{{ asset($img->files->first()->file_path) }}" alt="">
            @endif

            {{-- <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
            </svg>
            <p>Dashboard Preview</p> --}}
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Features Highlight -->
<section>
  <div class="container">
    <div class="text-center" style="max-width: 700px; margin: 0 auto var(--space-4xl);">
      <span class="badge badge-primary">Features</span>
      <h2 style="margin-top: var(--space-lg);">Everything You Need to Build Amazing Websites</h2>
      <p>Agares CMS provides all the tools you need to create, manage, and grow your online presence with confidence.</p>
    </div>

    {{-- ✅ READY NOW --}}
    <div class="feature-grid mb-4">

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M3 9h18M9 21V9"/>
          </svg>
        </div>
        <h3 class="card-title">Visual Page Editor</h3>
        <p class="card-description">Build beautiful pages with our intuitive drag-and-drop editor. Create custom layouts without writing code, or dive into the HTML when you need full control.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="8" y1="13" x2="16" y2="13"/>
            <line x1="8" y1="17" x2="16" y2="17"/>
          </svg>
        </div>
        <h3 class="card-title">Built-in Blog System</h3>
        <p class="card-description">Launch your blog in minutes with full category management, tags, SEO optimization, and scheduled publishing. Perfect for content marketing and thought leadership.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 20V10M12 20V4M6 20v-6"/>
          </svg>
        </div>
        <h3 class="card-title">Analytics Dashboard</h3>
        <p class="card-description">Track your website performance with integrated Google Analytics. Monitor visitors, page views, bounce rates, and conversions all from your CMS dashboard.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <polyline points="7.5 4.21 12 6.81 16.5 4.21"/>
            <polyline points="7.5 19.79 7.5 14.6 3 12"/>
            <polyline points="21 12 16.5 14.6 16.5 19.79"/>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
            <line x1="12" y1="22.08" x2="12" y2="12"/>
          </svg>
        </div>
        <h3 class="card-title">Unlimited Custom Fields</h3>
        <p class="card-description">Add unlimited inputs to any page or post. Choose from text, number, rich text, file uploads, galleries, and more. Build exactly what you need.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
        <h3 class="card-title">Global Media Gallery</h3>
        <p class="card-description">Organize all your images, videos, and documents in one central library. Easily reuse media across multiple pages with powerful search and filtering.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <h3 class="card-title">User Roles & Permissions</h3>
        <p class="card-description">Control who can access what with granular permission settings. Create custom roles for authors, editors, and administrators to match your team structure.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="16 18 22 12 16 6"/>
            <polyline points="8 6 2 12 8 18"/>
          </svg>
        </div>
        <h3 class="card-title">Custom Scripts & Styles</h3>
        <p class="card-description">Inject custom CSS and JavaScript into your pages. Add tracking codes, custom fonts, or any third-party integrations with ease.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l9 4.5v11L12 22 3 17.5v-11L12 2z"/>
            <path d="M7 7h10M7 12h6M7 17h10"/>
          </svg>
        </div>
        <h3 class="card-title">API & Integrations</h3>
        <p class="card-description">Connect Agares CMS with external apps using secure API keys and rate limiting. Perfect for headless setups, mobile apps, and custom integrations.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 18l6-6-6-6"/>
            <path d="M8 6l-6 6 6 6"/>
            <path d="M14 4l-4 16"/>
          </svg>
        </div>
        <h3 class="card-title">Blade Templates Editing</h3>
        <p class="card-description">Full developer freedom. Build or customize your website using Laravel Blade templates while managing content, media, SEO, and users directly from the CMS.</p>
      </div>

    </div>

    <div class="text-center" style="max-width: 700px; margin: var(--space-4xl) auto;">
      <span class="badge badge-primary">Coming soon</span>
      <h2 style="margin-top: var(--space-lg);">More modules on the way</h2>
      <p>Optional add-ons designed for real businesses - enabled when you need them.</p>
    </div>

    <div class="feature-grid">

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <h3 class="card-title">Ecommerce</h3>
        <p class="card-description">Sell products with orders, payments, shipping options, and inventory management.</p>
      </div>

      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
            <line x1="8" y1="9" x2="16" y2="9"/>
            <line x1="8" y1="13" x2="14" y2="13"/>
          </svg>
        </div>
        <h3 class="card-title">Forum</h3>
        <p class="card-description">Discussion boards with categories, roles, moderation tools, and rich user profiles to build communities.</p>
      </div>

      {{-- 🆕 Reservation system card --}}
      <div class="card feature-card">
        <div class="feature-icon" aria-hidden="true">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
            <path d="M8 14h4"/>
            <path d="M8 18h6"/>
          </svg>
        </div>
        <h3 class="card-title">Reservation System</h3>
        <p class="card-description">Bookings for services and events: calendar view, availability rules, email notifications, and admin management.</p>
      </div>

    </div>

  </div>
</section>

  
  <!-- Social Proof -->
  {{-- <section class="section-sm">
    <div class="container">
      <div class="text-center" style="max-width: 700px; margin: 0 auto var(--space-4xl);">
        <span class="badge badge-primary">Details</span>
        <h2 style="margin-top: var(--space-lg);">Main Features</h2>
      </div>
      <div class="grid grid-4" style="opacity: 1;">
        <div class="card" style="padding: var(--space-xl); text-align: center;">
          <div style="font-weight: bold; font-size: var(--text-xl);">Site Management</div>
        </div>
        <div class="card" style="padding: var(--space-xl); text-align: center;">
          <div style="font-weight: bold; font-size: var(--text-xl);">Blog</div>
        </div>
        <div class="card" style="padding: var(--space-xl); text-align: center;">
          <div style="font-weight: bold; font-size: var(--text-xl);">eCommerce</div>
        </div>
        <div class="card" style="padding: var(--space-xl); text-align: center;">
          <div style="font-weight: bold; font-size: var(--text-xl);">Forum</div>
        </div>
      </div>
    </div>
  </section> --}}
  

@stop 