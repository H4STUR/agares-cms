<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class APIController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view API', only: ['index']),
            new Middleware('can:manage API', only: ['store', 'revoke']),
        ];
    }

    /**
     * Admin API dashboard (keys + docs)
     */
    public function index()
    {
        $keys = ApiKey::query()
            ->orderByDesc('id')
            ->paginate(20);

        // Central list of available abilities (show in UI)
        $abilities = [
            // --- Frontend / Headless content ---
            'content:read'        => 'Read public content (menus/sites/articles/categories + inputs/galleries)',
            'preview:read'        => 'Read draft/private/scheduled for preview (no edits)',

            // Optional granular content scopes (use when you want tighter keys)
            'menus:read'          => 'Read menus and navigation',
            'sites:read'          => 'Read sites/pages',
            'categories:read'     => 'Read categories',
            'articles:read'       => 'Read articles',
            'media:read'          => 'Read media details + URLs',
            'forms:read'          => 'Read form definitions (for contact forms rendered in frontend)',

            // --- Settings (NEVER expose all settings) ---
            'settings:read_public'=> 'Read only whitelisted public settings (site name, description, home_url, etc.)',

            // --- Admin / integration (server-to-server only) ---
            'admin:read'          => 'Read admin data (users/roles/permissions) – integration use only',
            // later when you add write endpoints:
            // 'admin:write'      => 'Write admin data (dangerous)',
            // 'content:write'    => 'Create/update sites/articles/categories/inputs',
        ];



        // Base URL shown in UI
        $apiBase = url('/api/v1');

        return view('pages.admin.api.index', compact('keys', 'abilities', 'apiBase'));
    }

    /**
     * Create API key (plaintext shown once)
     */
    public function storeKey(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|max:100',
            'expires_at' => 'nullable|date',
            'site_id' => 'nullable|integer',
        ]);

        $plain = 'ak_' . Str::random(48);

        $key = ApiKey::create([
            'name' => $data['name'],
            'key_hash' => Hash::make($plain),
            'abilities' => $data['abilities'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
            'site_id' => $data['site_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.api.index')
            ->with('success', 'API key created. Copy it now - it will not be shown again.')
            ->with('new_api_key', $plain)
            ->with('new_api_key_id', $key->id);
    }

    /**
     * Revoke API key
     */
    public function revokeKey(ApiKey $apiKey)
    {
        $apiKey->update(['revoked_at' => now()]);

        return back()->with('success', 'API key revoked.');
    }

    public function store(Request $request)
    {
        return $this->storeKey($request);
    }

    public function revoke(ApiKey $apiKey)
    {
        return $this->revokeKey($apiKey);
    }

}
