<?php

use App\Models\InputInstance;
use App\Support\Permissions;

/**
 * True when the current user has the "view X" permission but lacks the
 * matching "manage X" permission — i.e. read-only on that module.
 * Owner always returns false (Gate::before grants everything).
 */
if (! function_exists('is_read_only')) {
    function is_read_only(string $managePermission): bool
    {
        $user = auth()->user();
        if (! $user) return true;
        if (method_exists($user, 'hasRole') && $user->hasRole('owner')) return false;
        return ! $user->can($managePermission);
    }
}

/**
 * True if the current user is a pure viewer/demo user
 * (has admin-panel access but no `manage X` permission of any kind).
 */
if (! function_exists('is_viewer')) {
    function is_viewer(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if (method_exists($user, 'hasRole') && $user->hasRole('owner')) return false;

        // NB: MANAGE_DASHBOARD is excluded here — it gates the read-only dashboard page,
        // not any mutating action. Viewers legitimately hold it.
        static $manageNeedles = [
            Permissions::MANAGE_SITES,
            Permissions::MANAGE_MENUS,
            Permissions::MANAGE_MEDIA,
            Permissions::MANAGE_SETTINGS,
            Permissions::MANAGE_CUSTOM,
            Permissions::MANAGE_ARTICLES,
            Permissions::MANAGE_CATEGORIES,
            Permissions::MANAGE_USERS,
            Permissions::MANAGE_PERMISSIONS,
            Permissions::MANAGE_COOKIES,
            Permissions::MANAGE_FORUM,
            Permissions::MANAGE_API,
            Permissions::MANAGE_TOOLS,
            Permissions::MANAGE_ECOMMERCE,
            Permissions::MANAGE_ORDERS,
        ];

        foreach ($manageNeedles as $perm) {
            if ($user->can($perm)) return false;
        }
        return $user->can(Permissions::VIEW_ADMIN_PANEL);
    }
}

/**
 * Sanitize rich-text HTML: strip disallowed tags and dangerous attributes/protocols.
 * Use this wherever admin-authored HTML is rendered unescaped in Blade ({!! !!}).
 */
if (! function_exists('safe_html')) {
    function safe_html(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        static $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li>'
            . '<a><h1><h2><h3><h4><h5><h6>'
            . '<blockquote><pre><code>'
            . '<figure><figcaption><table><thead><tbody><tr><td><th>'
            . '<div><span><hr><img>';

        $out = strip_tags($value, $allowed);

        // Strip all event-handler attributes (onclick, onerror, onload, …)
        $out = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $out);

        // Strip javascript: and data: URIs from href / src / action
        $out = preg_replace(
            '/\b(href|src|action)\s*=\s*"(?:javascript|data):[^"]*"/i',
            '$1="#"',
            $out
        );
        $out = preg_replace(
            "/\\b(href|src|action)\\s*=\\s*'(?:javascript|data):[^']*'/i",
            '$1="#"',
            $out
        );
        $out = preg_replace(
            '/\b(href|src|action)\s*=\s*(?:javascript|data):\S*/i',
            '$1="#"',
            $out
        );

        return $out;
    }
}

/**
 * Sanitize a field label that may contain limited HTML (e.g. <a> for consent links).
 * Strips everything except <a>, <strong>, <em>, <br> and sanitizes hrefs.
 */
if (! function_exists('safe_label')) {
    function safe_label(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $out = strip_tags($value, '<a><strong><em><br>');

        // Strip event handlers
        $out = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $out);

        // Strip javascript: from href
        $out = preg_replace('/\bhref\s*=\s*"javascript:[^"]*"/i', 'href="#"', $out);
        $out = preg_replace("/\\bhref\\s*=\\s*'javascript:[^']*'/i", 'href="#"', $out);
        $out = preg_replace('/\bhref\s*=\s*javascript:\S*/i', 'href="#"', $out);

        return $out;
    }
}

if (! function_exists('input_value')) {
    function input_value(string $variable, $site = null, $category = null, $article = null): string
    {
        // Pick the first non-null owner in priority order
        $owner = $article ?? $category ?? $site;

        if (! $owner) return '';

        $instance = InputInstance::query()
            ->where('owner_type', get_class($owner))
            ->where('owner_id', $owner->id)
            ->where('variable', $variable)
            ->first();

        return (string) ($instance->value ?? '');
    }
}
