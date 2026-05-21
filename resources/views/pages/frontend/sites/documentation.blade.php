@extends('pages.frontend.base')

@section('content')
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('/assets/admin/css/docs.css') }}">
        <style>
            .docs-layout { display: grid; grid-template-columns: 280px 1fr; gap: var(--space-3xl); align-items: start; }
            .docs-sidebar { position: sticky; top: 100px; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); max-height: calc(100vh - 120px); overflow-y: auto; }
            .docs-nav { list-style: none; }
            .docs-nav a { display: block; padding: var(--space-sm) var(--space-md); border-radius: var(--radius-md); color: var(--color-text-secondary); transition: all var(--transition-base); font-size: var(--text-sm); }
            .docs-nav a:hover { background: var(--color-surface-hover); color: var(--color-text-primary); }
            .docs-nav a.active { background: var(--color-accent-primary); color: white; }
            .docs-section { margin-bottom: var(--space-4xl); scroll-margin-top: 100px; }

            .pill { display:inline-flex; align-items:center; gap:.35rem; padding:.15rem .5rem; border-radius: 999px; border: 1px solid var(--color-border); background: var(--color-surface); font-size: var(--text-xs); color: var(--color-text-secondary); }
            .pill-primary { background: var(--color-accent-primary); color: white; border-color: var(--color-accent-primary); }
            .pill-success { background: #10b981; color: white; border-color: #10b981; }
            .pill-warning { background: #f59e0b; color: white; border-color: #f59e0b; }

            .kvs { display:grid; grid-template-columns: 220px 1fr; gap: var(--space-sm) var(--space-lg); }
            .kvs > div { padding: var(--space-sm) 0; border-bottom: 1px dashed var(--color-border); }
            .kvs > div:nth-child(odd) { color: var(--color-text-secondary); font-size: var(--text-sm); }
            .kvs > div:nth-child(even) { font-size: var(--text-sm); }

            .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); margin: var(--space-xl) 0; }
            .feature-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); }
            .feature-card h4 { margin-bottom: var(--space-sm); color: var(--color-text-primary); }
            .feature-card p { color: var(--color-text-secondary); font-size: var(--text-sm); margin: 0; }

            .hierarchy-tree { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
            .hierarchy-tree .level-0 { color: var(--color-accent-primary); font-weight: 700; }
            .hierarchy-tree .level-1 { margin-left: 2rem; color: #10b981; }
            .hierarchy-tree .level-2 { margin-left: 4rem; color: #f59e0b; }
            .hierarchy-tree .arrow { color: var(--color-text-secondary); }

            @media (max-width: 968px) {
                .docs-layout { grid-template-columns: 1fr; }
                .docs-sidebar { position: static; max-height: none; }
                .kvs { grid-template-columns: 1fr; }
            }
        </style>
    @endpush
@endonce

<section class="section-sm" style="background: var(--color-bg-secondary);">
    <div class="container text-center">
        @if(!empty($data['header']->value))
            <h1 class="cms-title">{{ $data['header']->value }}</h1>
        @endif

        <p style="font-size: var(--text-lg); max-width: 760px; margin: 0 auto;">
            {!! safe_html($data['content']->value ?? 'A quick overview of how Agares CMS structures content and what you can build with it.') !!}
        </p>
    </div>
</section>

<section>
    <div class="container">
        <div class="docs-layout">

            {{-- Sidebar Navigation --}}
            <aside class="docs-sidebar">
                <h3 style="margin-bottom: var(--space-lg); font-size: var(--text-lg);">Contents</h3>
                <ul class="docs-nav">
                    <li><a href="#overview" data-scroll-spy>Overview</a></li>
                    <li><a href="#content-model" data-scroll-spy>Content model</a></li>
                    <li><a href="#custom-fields" data-scroll-spy>Custom fields</a></li>
                    <li><a href="#media" data-scroll-spy>Media</a></li>
                    <li><a href="#permissions" data-scroll-spy>Users & permissions</a></li>
                    <li><a href="#seo" data-scroll-spy>SEO</a></li>
                    <li><a href="#api" data-scroll-spy>API</a></li>
                </ul>
            </aside>

            {{-- Main Content --}}
            <div class="docs-content">

                {{-- Overview --}}
                <div class="docs-section" id="overview" data-section>
                    <h2>Overview</h2>
                    <p>
                        Agares CMS is a modern, flexible CMS built on Laravel. It supports multi-site content, hierarchical pages,
                        reusable templates, custom fields, and permissions — all from one admin panel.
                    </p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <h4>Multi-site & hierarchy</h4>
                            <p>Manage many websites (or sections) with parent/child page structures and menu navigation.</p>
                        </div>
                        <div class="feature-card">
                            <h4>Custom fields</h4>
                            <p>Attach text, rich content, galleries, files, FAQs and forms to any page or post.</p>
                        </div>
                        <div class="feature-card">
                            <h4>Publishing workflow</h4>
                            <p>Draft, publish, schedule, or keep content private — without rebuilding the site.</p>
                        </div>
                        <div class="feature-card">
                            <h4>Headless-ready</h4>
                            <p>Optional REST API for React / Next.js / mobile apps.</p>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        <div>
                            <strong>Admin demo links:</strong><br>
                            <a href="{{ route('admin.sites') }}">Sites</a> ·
                            <a href="{{ route('admin.menus') }}">Menus</a> ·
                            <a href="{{ route('admin.media') }}">Media</a> ·
                            <a href="{{ route('admin.settings') }}">Settings</a> ·
                            <a href="{{ route('admin.api.documentation') }}">API</a>
                        </div>
                    </div>
                </div>

                {{-- Content model --}}
                <div class="docs-section" id="content-model" data-section>
                    <h2>Content model</h2>
                    <p>
                        Content is organized in a simple hierarchy that works for websites, blogs, news sections, portfolios, or product catalogs.
                    </p>

                    <div class="hierarchy-tree">
                        <div class="level-0">Menu</div>
                        <div class="level-1"><span class="arrow">└─</span> Site (Page)</div>
                        <div class="level-2"><span class="arrow">   └─</span> Category</div>
                        <div class="level-2"><span class="arrow">      └─</span> Article</div>
                    </div>

                    <div class="kvs" style="margin-top: var(--space-xl);">
                        <div>Sites (Pages)</div><div>Main pages/sections with templates, URL slugs, and custom fields.</div>
                        <div>Categories</div><div>Groups for posts inside a site (e.g., News / Blog / Projects).</div>
                        <div>Articles</div><div>Posts/items that can belong to one or multiple categories.</div>
                        <div>Menus</div><div>Navigation containers (main menu, footer menu, etc.).</div>
                    </div>

                    <h3 style="margin-top: var(--space-xl);">Publishing states</h3>
                    <p style="margin-bottom: var(--space-md);">Control visibility without changing templates:</p>
                    <div style="display:flex; flex-wrap:wrap; gap:.5rem;">
                        <span class="pill">Draft</span>
                        <span class="pill pill-success">Published</span>
                        <span class="pill pill-warning">Scheduled</span>
                        <span class="pill pill-primary">Private</span>
                    </div>
                </div>

                {{-- Custom fields --}}
                <div class="docs-section" id="custom-fields" data-section>
                    <h2>Custom fields</h2>
                    <p>
                        Agares CMS uses a flexible “Input System” to build pages without hardcoding layouts.
                        You can add reusable field sets (templates) and attach them to sites, categories, or articles.
                    </p>

                    <div class="kvs">
                        <div>Field types</div>
                        <div>Text, rich editor, galleries, files, FAQs, contact forms and more.</div>

                        <div>Templates</div>
                        <div>Create reusable groups of fields for consistent pages (e.g., landing pages, blog posts).</div>

                        <div>Variables</div>
                        <div>Each field has a variable key, so frontend templates can render it safely.</div>
                    </div>

                    <h3 style="margin-top: var(--space-xl);">Example use cases</h3>
                    <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                        <li>Landing page: hero title, subtitle, CTA buttons, feature blocks, gallery</li>
                        <li>Blog post: cover image, excerpt, rich content, author, attachments</li>
                        <li>Contact page: content + configurable contact form + GDPR consent</li>
                    </ul>
                </div>

                {{-- Media --}}
                <div class="docs-section" id="media" data-section>
                    <h2>Media</h2>
                    <p>
                        The Media Library keeps all uploads in one place and lets you reuse assets across pages and content types.
                    </p>

                    <div class="kvs">
                        <div>Upload</div><div>Images and files can be uploaded from the Media Library and selected in fields.</div>
                        <div>Galleries</div><div>Multi-image fields with sorting and per-image metadata (alt/description).</div>
                        <div>Safe reuse</div><div>Assets can be reused across the site without duplicating files.</div>
                    </div>
                </div>

                {{-- Users & permissions --}}
                <div class="docs-section" id="permissions" data-section>
                    <h2>Users & permissions</h2>
                    <p>
                        Built-in role and permission control lets you manage editors, moderators, and admins safely.
                        Permissions can be global and (optionally) limited per-site.
                    </p>

                    <div class="kvs">
                        <div>Roles</div><div>Assign roles like owner/admin/editor and control what each role can do.</div>
                        <div>Granular access</div><div>Limit access to specific modules (sites, media, users, settings, etc.).</div>
                        <div>Site-level access</div><div>Optionally allow a role to edit only selected sites/pages.</div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="docs-section" id="seo" data-section>
                    <h2>SEO</h2>
                    <p>
                        Basic SEO controls are built in to help your content perform well in search engines.
                    </p>

                    <div class="kvs">
                        <div>Meta tags</div><div>Set page title and description per site/category/article.</div>
                        <div>Sitemap & robots</div><div>Generate sitemap and control crawler rules from settings.</div>
                        <div>Clean URLs</div><div>Slug-based routing for readable, shareable links.</div>
                    </div>
                </div>

                {{-- API --}}
                <div class="docs-section" id="api" data-section>
                    <h2>API</h2>
                    <p>
                        Agares CMS can run as a headless backend. Use API keys to securely fetch content for external apps.
                    </p>

                    <div class="alert alert-info">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        <div>
                            <strong>Want details?</strong>
                            <a href="{{ route('admin.api.documentation') }}">Open full API documentation</a>
                        </div>
                    </div>

                    <div class="kvs">
                        <div>Authentication</div><div>Send your key in the <code>X-API-Key</code> header.</div>
                        <div>Typical endpoints</div><div>Sites, menus, articles, and public settings.</div>
                    </div>

                    <div class="code-block" style="margin-top: var(--space-lg);">
                        <div class="code-header"><span class="code-language">Example</span><button class="code-copy">Copy</button></div>
<pre><code>curl -H "X-API-Key: ak_your_key" "{{ url('/api/v1/sites/home') }}"</code></pre>
                    </div>
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
@endsection
