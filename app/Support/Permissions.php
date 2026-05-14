<?php

namespace App\Support;

/**
 * Single source of truth for admin permission names.
 * Convention: "view X" / "manage X" (lowercase, space-separated).
 */
final class Permissions
{
    // Admin core
    public const VIEW_ADMIN_PANEL  = 'view admin panel';
    public const MANAGE_DASHBOARD  = 'manage dashboard';

    // Sites / CMS
    public const VIEW_SITES        = 'view sites';
    public const MANAGE_SITES      = 'manage sites';
    public const VIEW_MENUS        = 'view menus';
    public const MANAGE_MENUS      = 'manage menus';
    public const VIEW_MEDIA        = 'view media';
    public const MANAGE_MEDIA      = 'manage media';
    public const VIEW_SETTINGS     = 'view settings';
    public const MANAGE_SETTINGS   = 'manage settings';
    public const VIEW_CUSTOM       = 'view custom';
    public const MANAGE_CUSTOM     = 'manage custom';
    public const VIEW_UNPUBLISHED  = 'view unpublished content';
    public const ADMIN_NAV         = 'admin nav';

    // Content
    public const VIEW_ARTICLES     = 'view articles';
    public const MANAGE_ARTICLES   = 'manage articles';
    public const VIEW_CATEGORIES   = 'view categories';
    public const MANAGE_CATEGORIES = 'manage categories';

    // Users / permissions
    public const VIEW_USERS        = 'view users';
    public const MANAGE_USERS      = 'manage users';
    public const VIEW_PERMISSIONS  = 'view permissions';
    public const MANAGE_PERMISSIONS = 'manage permissions';

    // Cookies
    public const VIEW_COOKIES      = 'view cookies';
    public const MANAGE_COOKIES    = 'manage cookies';

    // Forum
    public const VIEW_FORUM        = 'view forum';
    public const MANAGE_FORUM      = 'manage forum';

    // API
    public const VIEW_API          = 'view API';
    public const MANAGE_API        = 'manage API';

    // Tools
    public const VIEW_TOOLS        = 'view tools';
    public const MANAGE_TOOLS      = 'manage tools';

    // Notifications (own only — kept ungated beyond admin-panel)
    public const VIEW_NOTIFICATIONS = 'view notifications';

    // Ecommerce
    public const VIEW_ECOMMERCE    = 'view ecommerce';
    public const MANAGE_ECOMMERCE  = 'manage ecommerce';
    public const VIEW_ORDERS       = 'view orders';
    public const MANAGE_ORDERS     = 'manage orders';

    // Ecommerce — customer (frontend)
    public const PLACE_ORDERS      = 'place orders';
    public const VIEW_OWN_ORDERS   = 'view own orders';

    // Newsletter
    public const VIEW_NEWSLETTER              = 'view newsletter';
    public const MANAGE_NEWSLETTER            = 'manage newsletter';
    public const VIEW_NEWSLETTER_SUBSCRIBERS  = 'view newsletter subscribers';
    public const MANAGE_NEWSLETTER_SUBSCRIBERS = 'manage newsletter subscribers';
    public const VIEW_NEWSLETTER_LISTS        = 'view newsletter lists';
    public const MANAGE_NEWSLETTER_LISTS      = 'manage newsletter lists';

    // Newsletter — Phase 2 (templates + campaign drafts + test send)
    public const VIEW_NEWSLETTER_TEMPLATES   = 'view newsletter templates';
    public const MANAGE_NEWSLETTER_TEMPLATES = 'manage newsletter templates';
    public const VIEW_NEWSLETTER_CAMPAIGNS   = 'view newsletter campaigns';
    public const MANAGE_NEWSLETTER_CAMPAIGNS = 'manage newsletter campaigns';
    public const PREVIEW_NEWSLETTER_CAMPAIGNS = 'preview newsletter campaigns';
    public const SEND_TEST_NEWSLETTER_CAMPAIGNS = 'send test newsletter campaigns';

    // Newsletter — Phase 3 (external API delegation + sync + integration settings)
    public const VIEW_NEWSLETTER_SETTINGS       = 'view newsletter settings';
    public const EDIT_NEWSLETTER_SETTINGS       = 'edit newsletter settings';
    public const TEST_NEWSLETTER_INTEGRATION    = 'test newsletter integration';
    public const DELEGATE_NEWSLETTER_CAMPAIGNS  = 'delegate newsletter campaigns';
    public const SYNC_NEWSLETTER_CAMPAIGNS      = 'sync newsletter campaigns';

    /** All permission strings, used by the seeder. */
    public static function all(): array
    {
        return [
            // Admin core
            ['name' => self::VIEW_ADMIN_PANEL,  'category' => 'admin'],
            ['name' => self::MANAGE_DASHBOARD,  'category' => 'admin'],

            // CMS
            ['name' => self::VIEW_SITES,        'category' => 'cms'],
            ['name' => self::MANAGE_SITES,      'category' => 'cms'],
            ['name' => self::VIEW_MENUS,        'category' => 'cms'],
            ['name' => self::MANAGE_MENUS,      'category' => 'cms'],
            ['name' => self::VIEW_MEDIA,        'category' => 'cms'],
            ['name' => self::MANAGE_MEDIA,      'category' => 'cms'],
            ['name' => self::VIEW_SETTINGS,     'category' => 'cms'],
            ['name' => self::MANAGE_SETTINGS,   'category' => 'cms'],
            ['name' => self::VIEW_CUSTOM,       'category' => 'cms'],
            ['name' => self::MANAGE_CUSTOM,     'category' => 'cms'],
            ['name' => self::VIEW_UNPUBLISHED,  'category' => 'cms'],
            ['name' => self::ADMIN_NAV,         'category' => 'cms'],

            // Content
            ['name' => self::VIEW_ARTICLES,     'category' => 'content'],
            ['name' => self::MANAGE_ARTICLES,   'category' => 'content'],
            ['name' => self::VIEW_CATEGORIES,   'category' => 'content'],
            ['name' => self::MANAGE_CATEGORIES, 'category' => 'content'],

            // Users
            ['name' => self::VIEW_USERS,        'category' => 'users'],
            ['name' => self::MANAGE_USERS,      'category' => 'users'],
            ['name' => self::VIEW_PERMISSIONS,  'category' => 'users'],
            ['name' => self::MANAGE_PERMISSIONS,'category' => 'users'],

            // Cookies
            ['name' => self::VIEW_COOKIES,      'category' => 'cms'],
            ['name' => self::MANAGE_COOKIES,    'category' => 'cms'],

            // Forum
            ['name' => self::VIEW_FORUM,        'category' => 'admin'],
            ['name' => self::MANAGE_FORUM,      'category' => 'admin'],

            // API
            ['name' => self::VIEW_API,          'category' => 'admin'],
            ['name' => self::MANAGE_API,        'category' => 'admin'],

            // Tools
            ['name' => self::VIEW_TOOLS,        'category' => 'admin'],
            ['name' => self::MANAGE_TOOLS,      'category' => 'admin'],

            // Notifications
            ['name' => self::VIEW_NOTIFICATIONS,'category' => 'admin'],

            // Ecommerce admin
            ['name' => self::VIEW_ECOMMERCE,    'category' => 'admin'],
            ['name' => self::MANAGE_ECOMMERCE,  'category' => 'admin'],
            ['name' => self::VIEW_ORDERS,       'category' => 'admin'],
            ['name' => self::MANAGE_ORDERS,     'category' => 'admin'],

            // Ecommerce customer
            ['name' => self::PLACE_ORDERS,      'category' => 'ecommerce'],
            ['name' => self::VIEW_OWN_ORDERS,   'category' => 'ecommerce'],

            // Newsletter
            ['name' => self::VIEW_NEWSLETTER,                 'category' => 'newsletter'],
            ['name' => self::MANAGE_NEWSLETTER,               'category' => 'newsletter'],
            ['name' => self::VIEW_NEWSLETTER_SUBSCRIBERS,     'category' => 'newsletter'],
            ['name' => self::MANAGE_NEWSLETTER_SUBSCRIBERS,   'category' => 'newsletter'],
            ['name' => self::VIEW_NEWSLETTER_LISTS,           'category' => 'newsletter'],
            ['name' => self::MANAGE_NEWSLETTER_LISTS,         'category' => 'newsletter'],
            ['name' => self::VIEW_NEWSLETTER_TEMPLATES,       'category' => 'newsletter'],
            ['name' => self::MANAGE_NEWSLETTER_TEMPLATES,     'category' => 'newsletter'],
            ['name' => self::VIEW_NEWSLETTER_CAMPAIGNS,       'category' => 'newsletter'],
            ['name' => self::MANAGE_NEWSLETTER_CAMPAIGNS,     'category' => 'newsletter'],
            ['name' => self::PREVIEW_NEWSLETTER_CAMPAIGNS,    'category' => 'newsletter'],
            ['name' => self::SEND_TEST_NEWSLETTER_CAMPAIGNS,  'category' => 'newsletter'],
            ['name' => self::VIEW_NEWSLETTER_SETTINGS,        'category' => 'newsletter'],
            ['name' => self::EDIT_NEWSLETTER_SETTINGS,        'category' => 'newsletter'],
            ['name' => self::TEST_NEWSLETTER_INTEGRATION,     'category' => 'newsletter'],
            ['name' => self::DELEGATE_NEWSLETTER_CAMPAIGNS,   'category' => 'newsletter'],
            ['name' => self::SYNC_NEWSLETTER_CAMPAIGNS,       'category' => 'newsletter'],
        ];
    }

    /** Read-only permission set for viewer / demo. */
    public static function viewerSet(): array
    {
        return [
            self::VIEW_ADMIN_PANEL,
            self::MANAGE_DASHBOARD, // dashboard is read-only by nature
            self::VIEW_SITES,
            self::VIEW_MENUS,
            self::VIEW_MEDIA,
            self::VIEW_SETTINGS,
            self::VIEW_CUSTOM,
            self::VIEW_ARTICLES,
            self::VIEW_CATEGORIES,
            self::VIEW_USERS,
            self::VIEW_PERMISSIONS,
            self::VIEW_COOKIES,
            self::VIEW_FORUM,
            self::VIEW_API,
            self::VIEW_TOOLS,
            self::VIEW_NOTIFICATIONS,
            self::VIEW_ECOMMERCE,
            self::VIEW_ORDERS,
            self::VIEW_NEWSLETTER,
            self::VIEW_NEWSLETTER_SUBSCRIBERS,
            self::VIEW_NEWSLETTER_LISTS,
            self::VIEW_NEWSLETTER_TEMPLATES,
            self::VIEW_NEWSLETTER_CAMPAIGNS,
            self::PREVIEW_NEWSLETTER_CAMPAIGNS,
            self::VIEW_NEWSLETTER_SETTINGS,
            self::VIEW_UNPUBLISHED,
            self::ADMIN_NAV,
        ];
    }
}
