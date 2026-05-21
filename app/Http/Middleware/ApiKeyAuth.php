<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next, ...$requiredAbilities)
    {
        $provided = $request->header('X-API-Key');

        if (!$provided || !is_string($provided)) {
            return response()->json(['message' => 'Missing API key'], 401);
        }

        // We store hash in DB, so we must lookup by checking hash.
        // For performance at scale, you’d store a prefix and index it.
        $apiKey = ApiKey::query()
            ->whereNull('revoked_at')
            ->get()
            ->first(function (ApiKey $k) use ($provided) {
                return Hash::check($provided, $k->key_hash);
            });

        if (!$apiKey) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        if ($apiKey->isExpired()) {
            return response()->json(['message' => 'API key expired'], 401);
        }

        // Enforce required scopes if middleware is called like: api.key:sites:read,articles:read
        foreach ($requiredAbilities as $ability) {
            if (!$apiKey->can($ability)) {
                return response()->json(['message' => 'Insufficient scope', 'required' => $ability], 403);
            }
        }

        // Attach to request for controllers/policies
        $request->attributes->set('apiKey', $apiKey);

        // Track usage (don’t do this on every request if very high traffic; but fine for now)
        $apiKey->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
