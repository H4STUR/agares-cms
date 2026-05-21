<!-- Required Meta Tags -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- SEO Meta Tags -->
        <title>{{ $data['site']->title ?? $settings['meta_title'] }}</title> <!-- Unique title for each page, under 60 characters -->
        <meta name="description" content="{{ $data['site']->description ?: ($settings['meta_description'] ?? '') }}">
        <meta name="keywords" content="{{ $data['site']->keywords ?: ($settings['meta_keywords'] ?? '') }}"> <!-- Optional: Use relevant keywords -->
        {{-- <meta name="author" content=""> --}}
    
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">
    
        <!-- Open Graph Meta Tags (For Social Sharing) -->
        <meta property="og:title" content="{{ $settings['meta_title'] ?? '' }}">
        <meta property="og:description" content="{{ $settings['meta_description'] ?? '' }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset($settings['og_image']) }}"> <!-- Image must be at least 1200x630 px for best display on social media -->
    
        <!-- Twitter Card Meta Tags (For Twitter Sharing) -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $settings['meta_title'] ?? '' }}">
        <meta name="twitter:description" content="{{ $settings['meta_description'] ?? '' }}">
        <meta name="twitter:image" content="{{ asset($settings['og_image']) }}">
        <meta name="twitter:site" content="@your_twitter_handle"> <!-- Optional, if you have a Twitter handle -->
    
        <!-- Favicon (Add different sizes and types for various devices) -->
        <link rel="icon" href="{{ asset($settings['favicon_32x32']) }}" type="image/x-icon">
        {{-- <link rel="apple-touch-icon" href="/path-to-your-favicon/apple-touch-icon.png"> <!-- 180x180 px for iOS devices --> --}}
     
        <!-- Robots Meta Tags (Control crawling and indexing) -->
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow"> <!-- Google-specific -->
    
        <!-- Mobile Browser Theme Colors -->
        {{-- <meta name="theme-color" content="#ffffff"> <!-- Customize for Chrome on Android -->
        <meta name="msapplication-TileColor" content="#ffffff"> <!-- For Windows browsers -->
        <meta name="msapplication-TileImage" content="/path-to-your-favicon/mstile-150x150.png"> <!-- 150x150 px --> --}}
    
        <!-- Preconnects and Preloads (Performance optimization) -->
        {{-- <link rel="preconnect" href="https://fonts.bunny.net"> <!-- Preconnect to improve loading speed of external resources -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net"> <!-- DNS prefetch for performance boost -->
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    
        <!-- Structured Data for SEO (Schema.org) -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Organization",
          "url": "https://www.yourwebsite.com",
          "name": "Your Website Name",
          "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+1-800-555-5555",
            "contactType": "Customer Service"
          }
        }
        </script> --}}
    