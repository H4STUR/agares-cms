<?php

// app/Http/Middleware/EnsureSetting.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class EnsureSetting
{
    /**
     * Usage:
     *  setting:<key>,<expected>,<mode?>
     *
     * Examples:
     *  setting:enable_registration,true
     *  setting:maintenance_mode,false
     *  setting:max_login_attempts,5
     *  setting:site_name,My Application
     *
     * mode (optional):
     *  - "abort404" (default)
     *  - "abort403"
     *  - "json404"
     *  - "json403"
     *
     * Notes:
     *  - If request expects JSON (API), abort403/abort404 will automatically return JSON.
     *  - Boolean settings are read via Setting::bool() when expected looks like a boolean.
     */
    public function handle(Request $request, Closure $next, string $key, string $expected = 'true', string $mode = 'abort404')
    {
        $expectedNorm = strtolower(trim($expected));

        // If expected looks boolean-ish, read actual via Setting::bool()
        $expectedIsBool = in_array($expectedNorm, ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'], true);

        $actual = $expectedIsBool
            ? Setting::bool($key, false)
            : Setting::get($key);

        // If setting doesn't exist (and we didn't use bool fallback), treat as "not allowed"
        if (!$expectedIsBool && $actual === null) {
            return $this->deny($request, $mode);
        }

        // Cast expected into the same "shape" as actual, then compare strictly
        $expectedCasted = $this->castExpected($expected, $actual);

        if ($actual !== $expectedCasted) {
            return $this->deny($request, $mode);
        }

        return $next($request);
    }

    private function castExpected(string $expected, mixed $actual): mixed
    {
        // If actual is boolean, cast expected to boolean
        if (is_bool($actual)) {
            return filter_var($expected, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
        }

        // If actual is int, cast expected to int
        if (is_int($actual)) {
            return (int) $expected;
        }

        // If actual is float, cast expected to float
        if (is_float($actual)) {
            return (float) $expected;
        }

        // If actual is array/object-ish, allow passing JSON in expected
        if (is_array($actual)) {
            $decoded = json_decode($expected, true);
            return $decoded ?? $expected;
        }

        // Default: compare as string
        return (string) $expected;
    }

    private function deny(Request $request, string $mode)
    {
        $wantsJson = $request->expectsJson() || $request->is('api/*');

        // If it's an API request and user chose abort*, respond JSON anyway.
        if ($wantsJson && str_starts_with($mode, 'abort')) {
            $code = ($mode === 'abort403') ? 403 : 404;
            return response()->json([
                'message' => $code === 403 ? 'Forbidden' : 'Not found',
            ], $code);
        }

        return match ($mode) {
            'abort403' => abort(403),
            'abort404' => abort(404),

            'json403' => response()->json(['message' => 'Forbidden'], 403),
            'json404' => response()->json(['message' => 'Not found'], 404),

            default => abort(404),
        };
    }
}
