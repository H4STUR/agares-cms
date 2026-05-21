<x-app-layout>
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('/assets/admin/css/docs.css') }}">
        <style>
            .docs-layout { display: grid; grid-template-columns: 280px 1fr; gap: var(--space-3xl); align-items: start; }
            .docs-sidebar { position: sticky; top: 100px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); }
            .docs-nav { list-style: none; }
            .docs-nav a { display: block; padding: var(--space-sm) var(--space-md); border-radius: var(--radius-md); color: var(--color-text-secondary); transition: all var(--transition-base); font-size: var(--text-sm); }
            .docs-nav a:hover { background: var(--color-surface-hover); color: var(--color-text-primary); }
            .docs-nav a.active { background: var(--color-accent-primary); color: white; }
            .docs-section { margin-bottom: var(--space-4xl); scroll-margin-top: 100px; }
            .pill { display:inline-flex; align-items:center; gap:.35rem; padding:.15rem .5rem; border-radius: 999px; border: 1px solid var(--color-border); background: var(--color-surface); font-size: var(--text-xs); color: var(--color-text-secondary); }
            .pill code { font-size: .78em; }
            .kvs { display:grid; grid-template-columns: 200px 1fr; gap: var(--space-sm) var(--space-lg); }
            .kvs > div { padding: var(--space-sm) 0; border-bottom: 1px dashed var(--color-border); }
            .kvs > div:nth-child(odd) { color: var(--color-text-secondary); font-size: var(--text-sm); }
            .kvs > div:nth-child(even) { font-size: var(--text-sm); }
            @media (max-width: 968px) { .docs-layout { grid-template-columns: 1fr; } .docs-sidebar { position: static; } .kvs { grid-template-columns: 1fr; } }
        </style>
    @endpush
@endonce

    {{-- Header --}}
    <section class="section-sm" style="background: var(--color-bg-secondary); border-radius: 20px;">
        <div class="container">
            <div class="breadcrumbs">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumbs-separator">/</span>
                <span>Documentation</span>
            </div>

            <span class="badge badge-primary">API v1</span>
            <h1 style="margin-top: var(--space-md);">Agares CMS Documentation</h1>
            <p style="font-size: var(--text-lg); max-width: 900px;">
                This page documents the <strong>Headless API</strong> for consuming Agares CMS content from other apps (React/Next.js/mobile backends),
                plus preview & admin integration endpoints protected by API keys.
            </p>
        </div>
    </section>

    <section>
        <div class="container">
            <div class="docs-layout">

                {{-- Sidebar --}}
                <aside class="docs-sidebar">
                    <h3 style="margin-bottom: var(--space-lg); font-size: var(--text-lg);">Contents</h3>
                    <ul class="docs-nav">
                        <li><a href="#api-overview" data-scroll-spy>API Overview</a></li>
                        <li><a href="#auth" data-scroll-spy>Authentication (API Keys)</a></li>
                        <li><a href="#abilities" data-scroll-spy>Abilities & Key Profiles</a></li>
                        <li><a href="#conventions" data-scroll-spy>Conventions (Pagination, Filtering)</a></li>
                        <li><a href="#errors" data-scroll-spy>Error Responses</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Public Content</strong></li>
                        <li><a href="#endpoints-health" data-scroll-spy>Health</a></li>
                        <li><a href="#endpoints-settings" data-scroll-spy>Public Settings</a></li>
                        <li><a href="#endpoints-menus" data-scroll-spy>Menus</a></li>
                        <li><a href="#endpoints-sites" data-scroll-spy>Sites (Pages)</a></li>
                        <li><a href="#endpoints-articles" data-scroll-spy>Articles & Categories</a></li>
                        <li><a href="#endpoints-media" data-scroll-spy>Media</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Preview</strong></li>
                        <li><a href="#endpoints-preview" data-scroll-spy>Preview Endpoints</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Admin / Integration</strong></li>
                        <li><a href="#endpoints-admin" data-scroll-spy>Admin Endpoints</a></li>

                        <li style="margin-top: var(--space-md);"><a href="#security" data-scroll-spy>Security Notes</a></li>
                    </ul>
                </aside>

                {{-- Content --}}
                <div class="docs-content">

                    {{-- API Overview --}}
                    <div class="docs-section" id="api-overview" data-section>
                        <h2>API Overview</h2>
                        <p>
                            Agares CMS provides a REST-style API under <code>/api/v1</code>. Endpoints are grouped into:
                            <strong>Public Content</strong> (render websites), <strong>Preview</strong> (drafts/scheduled),
                            and <strong>Admin/Integration</strong> (users/roles/permissions etc.).
                        </p>

                        <div class="alert alert-info">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            <div>
                                <strong>Base URL:</strong> <code>{{ url('/api/v1') }}</code><br>
                                <span class="text-muted">Versioning is done via URL prefix (<code>v1</code>, <code>v2</code> later).</span>
                            </div>
                        </div>

                        <div class="kvs">
                            <div>Auth method</div><div>API key sent via HTTP header <code>X-API-Key</code></div>
                            <div>Rate limiting</div><div>Per key (default: 120 requests/minute). Exceeding returns <code>429</code>.</div>
                            <div>Response format</div><div>JSON. Lists may return <code>data</code> or paginated structure.</div>
                            <div>Visibility rules</div><div>Public endpoints return only published/scheduled (already live). Preview returns drafts too.</div>
                        </div>
                    </div>

                    {{-- Authentication --}}
                    <div class="docs-section" id="auth" data-section>
                        <h2>Authentication (API Keys)</h2>
                        <p>
                            Requests must include a valid API key in header <code>X-API-Key</code>.
                            Keys are created in <strong>Admin → API</strong> and stored hashed in the database.
                            The plaintext key is shown only once.
                        </p>

                        <h3>Header</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">HTTP</span>
                                <button class="code-copy">Copy</button>
                            </div>
<pre><code>X-API-Key: ak_your_generated_key_here</code></pre>
                        </div>

                        <h3>cURL example</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">Bash</span>
                                <button class="code-copy">Copy</button>
                            </div>
<pre><code>curl -H "X-API-Key: ak_XXXX" "{{ url('/api/v1/health') }}"</code></pre>
                        </div>

                        <h3>JavaScript example (server-side recommended)</h3>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">JavaScript</span>
                                <button class="code-copy">Copy</button>
                            </div>
<pre><code>const res = await fetch("{{ url('/api/v1/sites/home') }}", {
  headers: { "X-API-Key": process.env.CMS_API_KEY }
});
const json = await res.json();</code></pre>
                        </div>

                        <div class="alert alert-warning">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10 2h4l8 20H2L10 2z"/>
                            </svg>
                            <div>
                                <strong>Do not put API keys in browser-only React apps.</strong><br>
                                A browser cannot keep secrets. Use a server (Next.js/SSR/BFF) or user auth (Sanctum) for browser clients.
                            </div>
                        </div>
                    </div>

                    {{-- Abilities --}}
                    <div class="docs-section" id="abilities" data-section>
                        <h2>Abilities & Key Profiles</h2>
                        <p>
                            Each API key has assigned <strong>abilities (scopes)</strong>. Endpoints require specific abilities.
                            Create different keys per project/environment so you can revoke them independently.
                        </p>

                        <h3>Recommended abilities</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ability</th>
                                        <th>Used for</th>
                                        <th>Examples</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>content:read</code></td>
                                        <td>Read public content for frontend rendering</td>
                                        <td><span class="pill"><code>/menus</code></span> <span class="pill"><code>/sites</code></span> <span class="pill"><code>/articles</code></span></td>
                                    </tr>
                                    <tr>
                                        <td><code>preview:read</code></td>
                                        <td>Read drafts/private/scheduled for preview</td>
                                        <td><span class="pill"><code>/preview/sites/{slug}</code></span></td>
                                    </tr>
                                    <tr>
                                        <td><code>settings:read_public</code></td>
                                        <td>Read whitelisted public settings only</td>
                                        <td><span class="pill"><code>/settings</code></span></td>
                                    </tr>
                                    <tr>
                                        <td><code>media:read</code></td>
                                        <td>Read media metadata + URLs</td>
                                        <td><span class="pill"><code>/media/{id}</code></span></td>
                                    </tr>
                                    <tr>
                                        <td><code>admin:read</code></td>
                                        <td>Integration: users/roles/permissions (server-to-server)</td>
                                        <td><span class="pill"><code>/admin/users</code></span> <span class="pill"><code>/admin/roles</code></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Key profiles (copy/paste guidance)</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Public Frontend (production)</strong>: <code>content:read</code>, <code>settings:read_public</code>, optionally <code>media:read</code></li>
                            <li><strong>Preview Frontend (staging)</strong>: <code>preview:read</code>, <code>settings:read_public</code>, optionally <code>media:read</code></li>
                            <li><strong>Integration / Reports</strong>: <code>admin:read</code> (+ <code>content:read</code> if needed)</li>
                        </ul>
                    </div>

                    {{-- Conventions --}}
                    <div class="docs-section" id="conventions" data-section>
                        <h2>Conventions (Pagination, Filtering)</h2>

                        <h3>Pagination</h3>
                        <p>Paginated endpoints return Laravel-style pagination JSON. Use query params:</p>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">HTTP</span><button class="code-copy">Copy</button></div>
<pre><code>GET /api/v1/sites/{siteSlug}/articles?page=2</code></pre>
                        </div>

                        <h3>Filtering</h3>
                        <p>Some list endpoints support filters. Example: filter articles by category:</p>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">HTTP</span><button class="code-copy">Copy</button></div>
<pre><code>GET /api/v1/sites/{siteSlug}/articles?category_id=123</code></pre>
                        </div>

                        <h3>Sorting</h3>
                        <p>
                            Default sorting is opinionated:
                            sites by <code>menu_order</code>, articles by newest <code>published_at</code>.
                            If you add custom sorting later, keep it explicit (e.g. <code>?sort=published_at&dir=desc</code>).
                        </p>
                    </div>

                    {{-- Errors --}}
                    <div class="docs-section" id="errors" data-section>
                        <h2>Error Responses</h2>
                        <p>API uses standard HTTP status codes. Common ones:</p>

                        <div class="table-container">
                            <table class="table">
                                <thead><tr><th>Status</th><th>Meaning</th><th>Typical reason</th></tr></thead>
                                <tbody>
                                    <tr><td><code>401</code></td><td>Unauthorized</td><td>Missing/invalid API key, expired key</td></tr>
                                    <tr><td><code>403</code></td><td>Forbidden</td><td>Key exists but lacks required ability</td></tr>
                                    <tr><td><code>404</code></td><td>Not found</td><td>Resource missing OR not public (draft/private hidden)</td></tr>
                                    <tr><td><code>422</code></td><td>Validation</td><td>Bad request body (for write endpoints later)</td></tr>
                                    <tr><td><code>429</code></td><td>Too Many Requests</td><td>Rate limit exceeded</td></tr>
                                    <tr><td><code>500</code></td><td>Server error</td><td>Unexpected exception</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Example error payload</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">JSON</span><button class="code-copy">Copy</button></div>
<pre><code>{
  "message": "Missing API key"
}</code></pre>
                        </div>
                    </div>

                    {{-- Endpoints: Health --}}
                    <div class="docs-section" id="endpoints-health" data-section>
                        <h2>Health</h2>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/health</code>
                        <p>Health check. No abilities required unless you wrap it.</p>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Bash</span><button class="code-copy">Copy</button></div>
<pre><code>curl "{{ url('/api/v1/health') }}"</code></pre>
                        </div>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">JSON</span><button class="code-copy">Copy</button></div>
<pre><code>{
  "ok": true,
  "version": 1
}</code></pre>
                        </div>
                    </div>

                    {{-- Endpoints: Public Settings --}}
                    <div class="docs-section" id="endpoints-settings" data-section>
                        <h2>Public Settings</h2>
                        <p><span class="pill">Ability: <code>settings:read_public</code></span></p>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/settings</code>

                        <p>
                            Returns a <strong>whitelisted</strong> set of settings safe for frontend consumption
                            (e.g. site name, description, home_url). Secrets are never exposed.
                        </p>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Bash</span><button class="code-copy">Copy</button></div>
<pre><code>curl -H "X-API-Key: ak_XXXX" "{{ url('/api/v1/settings') }}"</code></pre>
                        </div>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">JSON</span><button class="code-copy">Copy</button></div>
<pre><code>{
  "data": {
    "site_name": "My Application",
    "site_description": "This is a sample application.",
    "home_url": "home"
  }
}</code></pre>
                        </div>
                    </div>

                    {{-- Endpoints: Menus --}}
                    <div class="docs-section" id="endpoints-menus" data-section>
                        <h2>Menus</h2>
                        <p><span class="pill">Ability: <code>content:read</code></span></p>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/menus</code>
                        <p>Returns menus with ordered sites from <code>menu_site.menu_order</code>. Draft/trashed sites are excluded.</p>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Bash</span><button class="code-copy">Copy</button></div>
<pre><code>curl -H "X-API-Key: ak_XXXX" "{{ url('/api/v1/menus') }}"</code></pre>
                        </div>
                    </div>

                    {{-- Endpoints: Sites --}}
                    <div class="docs-section" id="endpoints-sites" data-section>
                        <h2>Sites (Pages)</h2>
                        <p><span class="pill">Ability: <code>content:read</code></span></p>

                        <h3>List sites</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/sites</code>
                        <p>Returns public sites (published + scheduled already live). Useful for sitemaps/search.</p>

                        <h3>Get site by slug (full render payload)</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/sites/{slug}</code>
                        <p>
                            Returns a site including SEO fields, children, inputs (your flexible input system),
                            galleries and resolved media URLs.
                        </p>

                        <div class="alert alert-success">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <div>
                                <strong>Frontend tip:</strong> Use <code>inputs</code> array for rendering blocks by <code>variable</code> and field type.
                                Galleries/media already contain <code>url</code>.
                            </div>
                        </div>
                    </div>

                    {{-- Endpoints: Articles --}}
                    <div class="docs-section" id="endpoints-articles" data-section>
                        <h2>Articles & Categories</h2>
                        <p><span class="pill">Ability: <code>content:read</code></span></p>

                        <h3>Categories for a site</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/sites/{siteSlug}/categories</code>

                        <h3>Articles for a site (paginated)</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/sites/{siteSlug}/articles</code>
                        <p>Optional filters: <code>?category_id=123</code></p>

                        <h3>Single article</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/articles/{id}</code>
                        <p>Returns full article payload including inputs and galleries.</p>

                        <h3>Articles by category (paginated)</h3>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/categories/{id}/articles</code>
                    </div>

                    {{-- Endpoints: Media --}}
                    <div class="docs-section" id="endpoints-media" data-section>
                        <h2>Media</h2>
                        <p><span class="pill">Ability: <code>media:read</code></span></p>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/media/{id}</code>
                        <p>Returns media metadata plus resolved <code>url</code> (public).</p>
                    </div>

                    {{-- Endpoints: Preview --}}
                    <div class="docs-section" id="endpoints-preview" data-section>
                        <h2>Preview Endpoints</h2>
                        <p>
                            Preview endpoints return <strong>draft/private/scheduled</strong> items so editors can preview content before publishing.
                            Keep these protected with a dedicated preview key.
                        </p>

                        <p><span class="pill">Ability: <code>preview:read</code></span></p>

                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/preview/sites/{slug}</code><br>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/preview/articles/{id}</code>
                    </div>

                    {{-- Endpoints: Admin --}}
                    <div class="docs-section" id="endpoints-admin" data-section>
                        <h2>Admin / Integration Endpoints</h2>
                        <p>
                            These endpoints are meant for server-to-server integrations (reporting tools, synchronizers, migrations).
                            Do not expose these to browsers. Prefer a dedicated key and revoke it if compromised.
                        </p>

                        <p><span class="pill">Ability: <code>admin:read</code></span></p>

                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/admin/users</code><br>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/admin/roles</code><br>
                        <div class="pill"><strong>GET</strong></div> <code>/api/v1/admin/permissions</code>

                        <div class="alert alert-warning" style="margin-top: var(--space-lg);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10 2h4l8 20H2L10 2z"/>
                            </svg>
                            <div>
                                <strong>Write endpoints (future):</strong> When you add create/update/delete endpoints, introduce separate abilities like
                                <code>admin:write</code> and <code>content:write</code>. Never reuse read-only keys for write operations.
                            </div>
                        </div>
                    </div>

                    {{-- Security --}}
                    <div class="docs-section" id="security" data-section>
                        <h2>Security Notes</h2>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Per-app keys:</strong> Create a new key for each app/environment (prod/staging/dev).</li>
                            <li><strong>Least privilege:</strong> Assign only the abilities that app needs.</li>
                            <li><strong>Revocation:</strong> If a key leaks, revoke it in Admin → API.</li>
                            <li><strong>Do not store keys in frontend JS:</strong> Browser apps can’t keep secrets.</li>
                            <li><strong>Use preview keys carefully:</strong> Preview endpoints can expose draft content.</li>
                            <li><strong>Rate limit:</strong> Keep throttle enabled to prevent abuse.</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </section>

@once
    @push('scripts')
        <script src="{{ asset('/assets/admin/js/docs.js') }}"></script>
    @endpush
@endonce
</x-app-layout>
