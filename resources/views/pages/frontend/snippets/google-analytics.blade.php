{{-- resources/views/pages/frontend/snippets/google-analytics.blade.php --}}
@php
    use Illuminate\Support\Str;

    $gaId = $settings['google_analytics_id'] ?? null;

    if (!is_string($gaId) || trim($gaId) === '') {
        $gaId = \App\Models\Setting::str('google_analytics_id', '');
    }

    $gaId = is_string($gaId) ? trim($gaId) : null;
    $isGa4 = filled($gaId) && Str::startsWith($gaId, 'G-');

    $host = request()->getHost();
    $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);
    $isLocalEnv  = app()->environment('local');
    $autoGrant = $isLocalEnv || $isLocalHost;
@endphp

@if($isGa4)
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
  <script>
    (function () {
      window.dataLayer = window.dataLayer || [];
      function gtag(){ dataLayer.push(arguments); }

      const GA_ID = @json($gaId);
      const AUTO = @json($autoGrant);

      /**
       * Our single source of truth (banner):
       * localStorage cookie_consent_v1 = { essential, functional, analytics, marketing, savedAt }
       */
      const CONSENT_STORAGE_KEY = 'cookie_consent_v1';

      function readBannerConsent() {
        if (AUTO) {
          return { essential:true, functional:true, analytics:true, marketing:true };
        }

        try {
          const raw = window.localStorage ? localStorage.getItem(CONSENT_STORAGE_KEY) : null;
          if (!raw) return null;
          const obj = JSON.parse(raw);
          if (!obj || typeof obj !== 'object') return null;

          return {
            essential: true,
            functional: !!obj.functional,
            analytics: !!obj.analytics,
            marketing: !!obj.marketing
          };
        } catch (e) {
          return null;
        }
      }

      function applyConsent(consent) {
        // Map banner categories to Google Consent Mode buckets
        // - essential => security_storage always granted
        // - functional => functionality_storage
        // - analytics => analytics_storage
        // - marketing => ad_storage, ad_user_data, ad_personalization
        const functional = !!consent.functional;
        const analytics  = !!consent.analytics;
        const marketing  = !!consent.marketing;

        gtag('consent', 'update', {
          analytics_storage: analytics ? 'granted' : 'denied',
          functionality_storage: functional ? 'granted' : 'denied',
          security_storage: 'granted',

          // marketing mapping (Google ads ecosystem)
          ad_storage: marketing ? 'granted' : 'denied',
          ad_user_data: marketing ? 'granted' : 'denied',
          ad_personalization: marketing ? 'granted' : 'denied',
        });
      }

      // Default consent:
      // Local/dev: granted so you can test realtime
      // Prod: denied until we read stored consent
      gtag('consent', 'default', {
        analytics_storage: AUTO ? 'granted' : 'denied',
        functionality_storage: AUTO ? 'granted' : 'denied',
        security_storage: 'granted',

        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
      });

      gtag('js', new Date());

      // You can keep config always; consent mode decides what is stored/used
      gtag('config', GA_ID, {
        anonymize_ip: true,
        debug_mode: AUTO
      });

      // Restore consent from banner localStorage, if present
      const stored = readBannerConsent();
      if (stored) applyConsent(stored);

      /**
       * Public API for your cookie banner JS
       * - setConsent({functional, analytics, marketing})
       * - setAnalytics(true/false) (backward compat)
       */
      window.AgaresConsent = window.AgaresConsent || {};

      window.AgaresConsent.setConsent = function (consent) {
        // consent can be {functional, analytics, marketing} or full object
        const normalized = {
          essential: true,
          functional: !!(consent && consent.functional),
          analytics:  !!(consent && consent.analytics),
          marketing:  !!(consent && consent.marketing),
        };

        applyConsent(normalized);
      };

      // backward compat
      window.AgaresConsent.setAnalytics = function (allow) {
        window.AgaresConsent.setConsent({
          functional: true,
          analytics: !!allow,
          marketing: false
        });
      };

      // optional: expose read
      window.AgaresConsent.getStored = function () {
        return readBannerConsent();
      };

    })();
  </script>
@endif
