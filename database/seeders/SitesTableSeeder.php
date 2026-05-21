<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\User;
use App\Models\InputTemplate;
use App\Models\InputInstance;
use App\Models\Gallery;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class SitesTableSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'office@agares.co.uk')->value('id');

        $mainMenuId   = Menu::where('name', 'Main Menu')->value('id');
        $staticMenuId = Menu::where('name', 'Static Pages')->value('id');

        // HOME
        $home = Site::updateOrCreate(
            ['slug' => 'home'],
            [
                'name'        => 'Home Page',
                'title'       => 'Welcome to Our Website',
                'description' => 'This is the home page of our website.',
                'keywords'    => 'home, welcome, website',
                'template'    => 'home',
                'privileges'  => json_encode(['public' => true]),
                'menu_order'  => 1,
                'status'      => 'published',
                'published_at'=> now(),
                'created_by'  => $ownerId,
                'updated_by'  => $ownerId,
            ],
        );

        $this->attachToMenuIfMissing($home, $mainMenuId);
        
        $this->applyDefaultInputsToSite($home, $ownerId);

        // PRIVACY POLICY (static pages menu: ID 2)
        $privacy = Site::updateOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'name'        => 'Privacy Policy',
                'title'       => 'Privacy Policy',
                'description' => 'Privacy Policy page.',
                'keywords'    => 'privacy policy, privacy, gdpr',
                'template'    => 'index', // or your static page template if you have one
                'privileges'  => json_encode(['public' => true]),
                'status'      => 'published',
                'published_at'=> now(),
                'created_by'  => $ownerId,
                'updated_by'  => $ownerId,
            ],
        );

        $this->attachToMenuIfMissing($privacy, $staticMenuId);

        // Ensure default inputs exist
        $this->applyDefaultInputsToSite($privacy, $ownerId);

        // Fill "content" input with the privacy policy HTML
        $this->setSiteContent($privacy, $ownerId, $this->privacyPolicyHtml());

                // TERMS OF SERVICE
        $tos = Site::updateOrCreate(
            ['slug' => 'terms-of-service'],
            [
                'name'        => 'Terms of Service',
                'title'       => 'Terms of Service',
                'description' => 'Terms of Service page.',
                'keywords'    => 'terms, terms of service, conditions',
                'template'    => 'index',
                'privileges'  => json_encode(['public' => true]),
                'status'      => 'published',
                'published_at'=> now(),
                'created_by'  => $ownerId,
                'updated_by'  => $ownerId,
            ],
        );

        $this->attachToMenuIfMissing($tos, $staticMenuId);
        $this->applyDefaultInputsToSite($tos, $ownerId);
        $this->setSiteContent($tos, $ownerId, $this->termsOfServiceHtml());

        // COOKIE POLICY
        $cookies = Site::updateOrCreate(
            ['slug' => 'cookie-policy'],
            [
                'name'        => 'Cookie Policy',
                'title'       => 'Cookie Policy',
                'description' => 'Cookie Policy page.',
                'keywords'    => 'cookies, cookie policy, tracking',
                'template'    => 'index',
                'privileges'  => json_encode(['public' => true]),
                'status'      => 'published',
                'published_at'=> now(),
                'created_by'  => $ownerId,
                'updated_by'  => $ownerId,
            ],
        );

        $this->attachToMenuIfMissing($cookies, $staticMenuId);
        $this->applyDefaultInputsToSite($cookies, $ownerId);
        $this->setSiteContent($cookies, $ownerId, $this->cookiePolicyHtml());

    }

    private function attachToMenuIfMissing(Site $site, int $menuId): void
    {
        $already = DB::table('menu_site')
            ->where('menu_id', $menuId)
            ->where('site_id', $site->id)
            ->exists();

        if ($already) return;

        $maxOrder = DB::table('menu_site')
            ->where('menu_id', $menuId)
            ->max('menu_order');

        $site->menus()->attach($menuId, [
            'menu_order' => ((int)($maxOrder ?? 0)) + 1,
        ]);
    }

    private function setSiteContent(Site $site, ?int $createdBy, string $html): void
  {
      $headerVar  = 'header';
      $contentVar = 'content';

      // ---- HEADER ----
      $header = InputInstance::where('owner_type', Site::class)
          ->where('owner_id', $site->id)
          ->where('variable', $headerVar)
          ->first();

      if ($header) {
          $header->value = e($site->title ?? $site->name ?? 'Page');
          $header->created_by = $header->created_by ?? $createdBy;
          $header->save();
      }

      // ---- CONTENT ----
      $content = InputInstance::where('owner_type', Site::class)
          ->where('owner_id', $site->id)
          ->where('variable', $contentVar)
          ->first();

      if (!$content) {
          throw new \RuntimeException(
              "Privacy Policy: input variable '{$contentVar}' not found for site #{$site->id}. Add it to the default site template."
          );
      }

      $content->value = $html;
      $content->created_by = $content->created_by ?? $createdBy;
      $content->save();
  }


    private function privacyPolicyHtml(): string
    {
        return <<<HTML
      <div id="privacy-policy">
        <p>
          This Privacy Policy explains how we collect, use, disclose, and protect your information when you use our website
          and related services (the “Service”). If you do not agree with this policy, please do not use the Service.
        </p>

          <h2>1. Data Controller</h2>
          <p>
              The data controller of personal data processed through this website is
              <strong>AGARES Łukasz Majerski</strong>, with its registered office in Poland,
              NIP: <strong>629-249-98-24</strong>.
          </p>
          <p>
              If you have any questions regarding the processing of your personal data,
              you may contact us at:
              <a href="mailto:office@agares.co.uk">office@agares.co.uk</a>.
          </p>

        <h2>2. What Data We Collect</h2>

        <h3>2.1 Data You Provide</h3>
        <ul>
          <li><strong>Contact details</strong> (e.g., name, email address, phone number) if you contact us.</li>
          <li><strong>Message content</strong> if you send an inquiry, support request, or feedback.</li>
          <li><strong>Account details</strong> (if accounts are enabled), such as username and email.</li>
        </ul>

        <h3>2.2 Data Collected Automatically</h3>
        <ul>
          <li><strong>Device and usage data</strong> such as browser type, operating system, IP address, pages visited, and timestamps.</li>
          <li><strong>Cookies and similar technologies</strong> used for essential site functionality and optional analytics (see Section 6).</li>
        </ul>

        <h3>2.3 Data From Third Parties</h3>
        <p>
          We may receive information from service providers (e.g., analytics or hosting providers) that help us operate and improve the Service.
        </p>

        <h2>3. How We Use Your Data</h2>
        <p>We use your information for the following purposes:</p>
        <ul>
          <li>To provide and maintain the Service.</li>
          <li>To respond to inquiries and provide customer support.</li>
          <li>To improve the Service, including performance monitoring and troubleshooting.</li>
          <li>To protect the Service against fraud, abuse, and security incidents.</li>
          <li>To comply with legal obligations.</li>
        </ul>

        <h2>4. Legal Bases (EEA/UK)</h2>
        <p>If you are located in the EEA or UK, we process personal data under one or more of the following legal bases:</p>
        <ul>
          <li><strong>Consent</strong> (e.g., non-essential cookies or marketing where applicable).</li>
          <li><strong>Contract</strong> (e.g., to provide features you request).</li>
          <li><strong>Legitimate interests</strong> (e.g., website security, basic analytics, improving our Service), unless overridden by your rights.</li>
          <li><strong>Legal obligation</strong> (e.g., compliance with applicable laws).</li>
        </ul>

        <h2>5. How We Share Your Data</h2>
        <p>We may share your information:</p>
        <ul>
          <li><strong>With service providers</strong> who help us operate the Service (e.g., hosting, analytics, email delivery), under appropriate safeguards.</li>
          <li><strong>For legal reasons</strong> if required by law, regulation, or legal process.</li>
          <li><strong>To protect rights and safety</strong> of our users, the public, or the Service.</li>
          <li><strong>In a business transfer</strong> (e.g., merger, acquisition, or asset sale), where permitted by law.</li>
        </ul>
        <p>We do not sell your personal information.</p>

        <h2>6. Cookies and Tracking</h2>
        <p>
          We use cookies and similar technologies for essential website functionality and, where enabled, analytics.
          You can manage cookies using your browser settings. If we use a cookie consent tool, you can also manage preferences there.
        </p>
        <ul>
          <li><strong>Essential cookies</strong>: required for core site features (e.g., authentication, security).</li>
          <li><strong>Analytics cookies</strong> (optional): help us understand how the Service is used and improve it.</li>
        </ul>

        <h2>7. Analytics</h2>
        <p>
          We may use analytics tools (such as Google Analytics) to measure traffic and improve the Service.
          Analytics providers may set cookies or collect usage data. Where required by law, analytics will only be enabled after consent.
        </p>

        <h2>8. Data Retention</h2>
        <p>
          We keep personal data only as long as necessary for the purposes described in this policy, unless a longer retention period is required or permitted by law.
        </p>

        <h2>9. Security</h2>
        <p>
          We use reasonable administrative, technical, and organizational measures to protect your data.
          However, no method of transmission over the Internet or electronic storage is completely secure.
        </p>

        <h2>10. International Transfers</h2>
        <p>
          Your data may be processed in countries other than your own. Where required, we use appropriate safeguards for international transfers.
        </p>

        <h2>11. Your Rights</h2>
        <p>Depending on your location, you may have the right to:</p>
        <ul>
          <li>Access, correct, or delete your personal data.</li>
          <li>Object to or restrict certain processing.</li>
          <li>Withdraw consent (where processing is based on consent).</li>
          <li>Data portability (where applicable).</li>
          <li>Lodge a complaint with a data protection authority.</li>
        </ul>

        <h2>12. Children’s Privacy</h2>
        <p>
          The Service is not intended for children under the age of 16 (or the applicable age in your jurisdiction).
          We do not knowingly collect personal data from children.
        </p>

        <h2>13. Third-Party Links</h2>
        <p>
          The Service may contain links to third-party websites. We are not responsible for the privacy practices of those websites.
        </p>

        <h2>14. Changes to This Policy</h2>
        <p>
          We may update this Privacy Policy from time to time. We will post the updated version on this page and update the “Last updated” date above.
        </p>

        <h2>15. Contact</h2>
        <p>
          If you have questions about this Privacy Policy or our privacy practices, please contact us using the contact details provided on the website.
        </p>
          </div>
      HTML;
    }

    private function termsOfServiceHtml(): string
    {
      return <<<HTML
      <div id="terms-of-service">
        <p>
          These Terms of Service (“Terms”) govern your access to and use of this website and related services (the “Service”).
          By accessing or using the Service, you agree to be bound by these Terms.
        </p>

        <h2>1. About the Service</h2>
        <p>
          The Service is provided by <strong>AGARES Łukasz Majerski</strong> (“we”, “us”, “our”).
          The Service may include content pages, a demo, and features related to Agares CMS.
        </p>

        <h2>2. Acceptable Use</h2>
        <ul>
          <li>You must not misuse the Service or attempt to gain unauthorized access.</li>
          <li>You must not upload or distribute malware, spam, or unlawful content.</li>
          <li>You must comply with applicable laws when using the Service.</li>
        </ul>

        <h2>3. Accounts (if enabled)</h2>
        <p>
          If accounts are enabled, you are responsible for maintaining the confidentiality of your login credentials and for all activity under your account.
        </p>

        <h2>4. Intellectual Property</h2>
        <p>
          Unless stated otherwise, all content, branding, and software related to the Service are owned by us or our licensors.
          You may not copy, modify, or redistribute them without permission.
        </p>

        <h2>5. Availability and Changes</h2>
        <p>
          We may modify, suspend, or discontinue the Service (in whole or in part) at any time.
          We do not guarantee uninterrupted availability.
        </p>

        <h2>6. Disclaimer</h2>
        <p>
          The Service is provided “as is” and “as available”. To the maximum extent permitted by law, we disclaim all warranties,
          express or implied, including fitness for a particular purpose and non-infringement.
        </p>

        <h2>7. Limitation of Liability</h2>
        <p>
          To the maximum extent permitted by law, we will not be liable for any indirect, incidental, special, consequential,
          or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly.
        </p>

        <h2>8. Links to Third Parties</h2>
        <p>
          The Service may contain links to third-party sites. We are not responsible for third-party content or policies.
        </p>

        <h2>9. Governing Law</h2>
        <p>
          These Terms are governed by the laws applicable to our business location, unless mandatory consumer laws provide otherwise.
        </p>

        <h2>10. Contact</h2>
        <p>
          Questions about these Terms can be sent to <a href="mailto:office@agares.co.uk">office@agares.co.uk</a>.
        </p>
      </div>
      HTML;
    }

    private function cookiePolicyHtml(): string
    {
        return <<<HTML
    <div id="cookie-policy">
      <p>
        This Cookie Policy explains what cookies are, how we use them, and how you can control them.
        For more information about personal data processing, see our Privacy Policy.
      </p>

      <h2>1. What are cookies?</h2>
      <p>
        Cookies are small text files stored on your device when you visit a website. They help the site function properly,
        remember preferences, and (optionally) measure usage.
      </p>

      <h2>2. Types of cookies we use</h2>

      <h3>2.1 Essential cookies</h3>
      <p>
        These cookies are necessary for the website to function (e.g., security, session management, authentication if enabled).
        They cannot be switched off in our systems.
      </p>

      <h3>2.2 Analytics cookies (optional)</h3>
      <p>
        If enabled and where required by law, analytics cookies (e.g., Google Analytics) help us understand how visitors use the Service
        so we can improve performance and user experience.
      </p>

      <h3>2.3 Functional cookies (optional)</h3>
      <p>
        These cookies remember choices you make (e.g., language, theme) to provide a more personalized experience.
      </p>

      <h2>3. How to manage cookies</h2>
      <ul>
        <li>You can delete cookies in your browser settings.</li>
        <li>You can block cookies, but parts of the Service may not work correctly.</li>
        <li>If we use a cookie consent tool, you can manage preferences there.</li>
      </ul>

      <h2>4. Changes to this Cookie Policy</h2>
      <p>
        We may update this Cookie Policy from time to time. The latest version will be published on this page.
      </p>

      <h2>5. Contact</h2>
      <p>
        If you have questions, contact us at <a href="mailto:office@agares.co.uk">office@agares.co.uk</a>.
      </p>
    </div>
    HTML;
    }



    private function applyDefaultInputsToSite(Site $site, ?int $createdBy): void
    {
        $tpl = InputTemplate::where('applies_to', 'site')
            ->whereNull('scope_type')
            ->whereNull('scope_id')
            ->with(['items.field'])
            ->first();

        if (!$tpl) {
            throw new \RuntimeException("Default site InputTemplate not found (applies_to='site').");
        }

        foreach ($tpl->items as $item) {
            $exists = InputInstance::where('owner_type', Site::class)
                ->where('owner_id', $site->id)
                ->where('variable', $item->variable)
                ->where('is_default', true)
                ->exists();

            if ($exists) continue;

            $fieldType = $item->field?->field_type;

            $galleryId = null;
            $mediaId   = null;
            $value     = $item->default_value;

            if ($fieldType === 'gallery') {
                $gallery = Gallery::firstOrCreate(
                    [
                        'owner_type' => Site::class,
                        'owner_id'   => $site->id,
                        'variable'   => $item->variable,
                    ],
                    [
                        'name'       => $item->label ?? 'Gallery',
                        'sort_order' => (int) $item->sort_order,
                    ]
                );

                $galleryId = $gallery->id;
                $value = null;
            }

            if ($fieldType === 'image' || $fieldType === 'file') {
                $mediaId = null;
            }

            InputInstance::create([
                'owner_type'      => Site::class,
                'owner_id'        => $site->id,
                'input_field_id'  => $item->input_field_id,
                'label'           => $item->label,
                'variable'        => $item->variable,
                'value'           => $value,
                'description'     => $item->description,
                'media_id'        => $mediaId,
                'gallery_id'      => $galleryId,
                'sort_order'      => (int) $item->sort_order,
                'is_default'      => true,
                'is_locked'       => (bool) $item->is_locked,
                'created_by'      => $createdBy,
            ]);
        }
    }
}
