<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default settings data
        $settings = [

            //GENERAL
            [
                'key' => 'site_name',
                'value' => 'My Application',
                'category' => 'general',
                'type' => 'string',
                'description' => 'The name of the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'home_url',
                'value' => 'home',
                'category' => 'general',
                'type' => 'string',
                'description' => 'URL of the home page',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'site_description',
                'value' => 'This is a sample application.',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Description of the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@example.com',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Email address for contact purposes',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+48 123 456 789',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Phone number for contact purposes',
                'created_by' => null,
                'updated_by' => null,
            ],

            //security
            [
                'key' => 'enable_registration',
                'value' => '0',
                'category' => 'security',
                'type' => 'boolean',
                'description' => 'Allow new users to register',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '1',
                'category' => 'security',
                'type' => 'boolean',
                'description' => 'Maintenance mode, site is only accessible for admins',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'category' => 'security',
                'type' => 'integer',
                'description' => 'Maximum number of login attempts before lockout',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => '2FA_enabled',
                'value' => '0',
                'category' => 'security',
                'type' => 'boolean',
                'description' => 'Allow users to enable 2FA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => '2FA_required',
                'value' => '0',
                'category' => 'security',
                'type' => 'boolean',
                'description' => 'Require users to login with 2FA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'public_profiles',
                'value' => '0',
                'category' => 'security',
                'type' => 'boolean',
                'description' => 'Make user profiles publicly visible (guests can view profiles)',
                'created_by' => null,
                'updated_by' => null,
            ],

            // SOCIAL MEDIA
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/yourpage',
                'category' => 'social_media',
                'type' => 'string',
                'description' => 'URL of the Facebook page',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'twitter_handle',
                'value' => '@yourhandle',
                'category' => 'social_media',
                'type' => 'string',
                'description' => 'Twitter handle of the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/yourpage',
                'category' => 'social_media',
                'type' => 'string',
                'description' => 'Twitter handle of the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/yourpage',
                'category' => 'social_media',
                'type' => 'string',
                'description' => 'URL of the Instagram page',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'linkedin_url',
                'value' => 'https://linkedin.com/company/yourpage',
                'category' => 'social_media',
                'type' => 'string',
                'description' => 'URL of the LinkedIn page',
                'created_by' => null,
                'updated_by' => null,
            ],

            // SEO
            [
                'key' => 'meta_title',
                'value' => 'Agares',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Default meta title for the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'meta_description',
                'value' => 'Agares',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Default meta description for the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'Agares',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Default meta keywords for the site',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'og_image',
                'value' => '/assets/admin/images/agares-logo.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Open Graph image URL for social media sharing',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'twitter_image',
                'value' => '/assets/admin/images/agares-logo.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Twitter image URL for sharing',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'google_analytics_id',
                'value' => 'G-XXXXXXXXXX',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'Google Analytics tracking ID',
                'created_by' => null,
                'updated_by' => null,
            ],

            // FAVICON AND LOGO
            [
                'key' => 'favicon_16x16',
                'value' => '/assets/admin/images/agares-logo.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'URL of the 16x16 favicon image',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'favicon_32x32',
                'value' => '/assets/admin/images/agares-logo.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'URL of the 32x32 favicon image',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'apple_touch_icon',
                'value' => '/assets/apple-touch-icon.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'URL of the Apple touch icon for iOS',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'key' => 'logo_url',
                'value' => '/assets/admin/images/agares-logo.png',
                'category' => 'seo',
                'type' => 'string',
                'description' => 'URL of the main logo image',
                'created_by' => null,
                'updated_by' => null,
            ],

            // addons
            [
                'key' => 'enable_ecommerce',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable shop',
                'created_by' => null,
                'updated_by' => null,
            ],

            // forum
            [
                'key' => 'enable_forum',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable forum',
                'created_by' => null,
                'updated_by' => null,
            ],
            
            // API
            [
                'key' => 'enable_api',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable API',
                'created_by' => null,
                'updated_by' => null,
            ],

            // newsletter
            [
                'key' => 'enable_newsletter',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable newsletter',
                'created_by' => null,
                'updated_by' => null,
            ],

            // newsletter
            [
                'key' => 'enable_rezervations',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable rezervations',
                'created_by' => null,
                'updated_by' => null,
            ],

            // newsletter
            [
                'key' => 'enable_template_editor',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable template editor',
                'created_by' => null,
                'updated_by' => null,
            ],

            // database backup
            [
                'key' => 'enable_database_backup',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable database backup',
                'created_by' => null,
                'updated_by' => null,
            ],

            // database backup
            [
                'key' => 'enable_rag-chatbot',
                'value' => '0',
                'category' => 'add-ons',
                'type' => 'boolean',
                'description' => 'Enable RAG chatbot',
                'created_by' => null,
                'updated_by' => null,
            ],

            //CUSTOM
        ];

        // Insert each setting into the database
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }

    }
}
