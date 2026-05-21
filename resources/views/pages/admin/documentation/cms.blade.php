<x-app-layout>
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
            .pill code { font-size: .78em; }
            .pill-primary { background: var(--color-accent-primary); color: white; border-color: var(--color-accent-primary); }
            .pill-success { background: #10b981; color: white; border-color: #10b981; }
            .pill-warning { background: #f59e0b; color: white; border-color: #f59e0b; }
            .kvs { display:grid; grid-template-columns: 200px 1fr; gap: var(--space-sm) var(--space-lg); }
            .kvs > div { padding: var(--space-sm) 0; border-bottom: 1px dashed var(--color-border); }
            .kvs > div:nth-child(odd) { color: var(--color-text-secondary); font-size: var(--text-sm); }
            .kvs > div:nth-child(even) { font-size: var(--text-sm); }
            .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); margin: var(--space-xl) 0; }
            .feature-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); }
            .feature-card h4 { margin-bottom: var(--space-sm); color: var(--color-text-primary); }
            .feature-card p { color: var(--color-text-secondary); font-size: var(--text-sm); margin: 0; }
            .hierarchy-tree { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-lg); font-family: monospace; }
            .hierarchy-tree .level-0 { color: var(--color-accent-primary); font-weight: bold; }
            .hierarchy-tree .level-1 { margin-left: 2rem; color: #10b981; }
            .hierarchy-tree .level-2 { margin-left: 4rem; color: #f59e0b; }
            .hierarchy-tree .arrow { color: var(--color-text-secondary); }
            @media (max-width: 968px) { .docs-layout { grid-template-columns: 1fr; } .docs-sidebar { position: static; max-height: none; } .kvs { grid-template-columns: 1fr; } }
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

            <span class="badge badge-primary">CMS Guide</span>
            <h1 style="margin-top: var(--space-md);">Agares CMS Documentation</h1>
            <p style="font-size: var(--text-lg); max-width: 900px;">
                Complete guide for <strong>administrators</strong> managing content and <strong>developers</strong> building websites with Agares CMS.
                This documentation covers the admin panel features, content management, and frontend development.
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
                        <li><a href="#content-hierarchy" data-scroll-spy>Content Hierarchy</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Content Management</strong></li>
                        <li><a href="#sites" data-scroll-spy>Sites (Pages)</a></li>
                        <li><a href="#categories" data-scroll-spy>Categories</a></li>
                        <li><a href="#articles" data-scroll-spy>Articles</a></li>
                        <li><a href="#publishing" data-scroll-spy>Publishing Workflow</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Custom Fields</strong></li>
                        <li><a href="#input-system" data-scroll-spy>Input System Overview</a></li>
                        <li><a href="#input-fields" data-scroll-spy>Field Types</a></li>
                        <li><a href="#input-templates" data-scroll-spy>Templates</a></li>
                        <li><a href="#galleries" data-scroll-spy>Galleries</a></li>
                        <li><a href="#contact-forms" data-scroll-spy>Contact Forms</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Media & Assets</strong></li>
                        <li><a href="#media-library" data-scroll-spy>Media Library</a></li>
                        <li><a href="#file-uploads" data-scroll-spy>File Uploads</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Navigation</strong></li>
                        <li><a href="#menus" data-scroll-spy>Menus</a></li>
                        <li><a href="#site-hierarchy" data-scroll-spy>Site Hierarchy</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Users & Permissions</strong></li>
                        <li><a href="#users" data-scroll-spy>User Management</a></li>
                        <li><a href="#roles" data-scroll-spy>Roles & Permissions</a></li>
                        <li><a href="#site-permissions" data-scroll-spy>Site-Level Access</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Settings</strong></li>
                        <li><a href="#global-settings" data-scroll-spy>Global Settings</a></li>
                        <li><a href="#seo" data-scroll-spy>SEO Configuration</a></li>
                        <li><a href="#custom-code" data-scroll-spy>Custom Code</a></li>
                        <li><a href="#cookies" data-scroll-spy>Cookie Consent</a></li>

                        <li style="margin-top: var(--space-md);"><strong class="text-muted small">Development</strong></li>
                        <li><a href="#frontend-dev" data-scroll-spy>Frontend Development</a></li>
                        <li><a href="#blade-templates" data-scroll-spy>Blade Templates</a></li>
                        <li><a href="#helper-functions" data-scroll-spy>Helper Functions</a></li>
                        <li><a href="#api-integration" data-scroll-spy>API Integration</a></li>
                    </ul>
                </aside>

                {{-- Main Content --}}
                <div class="docs-content">

                    {{-- Overview --}}
                    <div class="docs-section" id="overview" data-section>
                        <h2>Overview</h2>
                        <p>
                            Agares CMS is a multi-site content management system built on <strong>Laravel 13</strong>.
                            It enables you to manage multiple websites from a single admin dashboard with flexible content structures,
                            role-based access control, and a powerful custom fields system.
                        </p>

                        <div class="feature-grid">
                            <div class="feature-card">
                                <h4>Multi-Site Management</h4>
                                <p>Create and manage multiple websites with hierarchical page structures from one admin panel.</p>
                            </div>
                            <div class="feature-card">
                                <h4>Flexible Custom Fields</h4>
                                <p>Add text, galleries, files, and forms to any content type using the Input System.</p>
                            </div>
                            <div class="feature-card">
                                <h4>Role-Based Access</h4>
                                <p>Control user permissions globally and per-site with granular access control.</p>
                            </div>
                            <div class="feature-card">
                                <h4>REST API</h4>
                                <p>Headless API with scoped authentication for React, Next.js, or mobile apps.</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            <div>
                                <strong>Quick Links:</strong><br>
                                <a href="{{ route('admin.sites') }}">Manage Sites</a> |
                                <a href="{{ route('admin.menus') }}">Manage Menus</a> |
                                <a href="{{ route('admin.media') }}">Media Library</a> |
                                <a href="{{ route('admin.settings') }}">Settings</a> |
                                <a href="{{ route('admin.api.documentation') }}">API Documentation</a>
                            </div>
                        </div>
                    </div>

                    {{-- Content Hierarchy --}}
                    <div class="docs-section" id="content-hierarchy" data-section>
                        <h2>Content Hierarchy</h2>
                        <p>
                            Agares CMS organizes content in a three-level hierarchy. Each level supports custom fields,
                            SEO metadata, and publishing workflows.
                        </p>

                        <div class="hierarchy-tree">
                            <div class="level-0">Menu</div>
                            <div class="level-1"><span class="arrow">└─</span> Site (Page)</div>
                            <div class="level-2"><span class="arrow">   └─</span> Category</div>
                            <div class="level-2"><span class="arrow">      └─</span> Article</div>
                        </div>

                        <div class="kvs" style="margin-top: var(--space-xl);">
                            <div>Menu</div><div>Top-level navigation container. Sites belong to menus and can be reordered.</div>
                            <div>Site</div><div>A page or section of your website. Sites can have parent-child relationships for hierarchy.</div>
                            <div>Category</div><div>Groups of articles within a site. Used for blog sections, news, products, etc.</div>
                            <div>Article</div><div>Individual content items. Can belong to multiple categories.</div>
                        </div>
                    </div>

                    {{-- Sites --}}
                    <div class="docs-section" id="sites" data-section>
                        <h2>Sites (Pages)</h2>
                        <p>
                            Sites are the primary content containers in Agares CMS. Each site represents a page or section
                            of your website with its own URL, template, and custom fields.
                        </p>

                        <h3>Creating a Site</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Navigate to <strong>Sites</strong> in the admin menu</li>
                            <li>Click <strong>Create New Site</strong></li>
                            <li>Fill in the required fields (Name, Slug)</li>
                            <li>Select a Menu to add the site to</li>
                            <li>Optionally select a parent site for hierarchy</li>
                            <li>Choose a template for frontend rendering</li>
                            <li>Add custom fields as needed</li>
                        </ol>

                        <h3>Site Properties</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>Field</th><th>Description</th><th>Required</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><code>name</code></td><td>Internal name for the site</td><td>Yes</td></tr>
                                    <tr><td><code>slug</code></td><td>URL-friendly identifier (must be unique)</td><td>Yes</td></tr>
                                    <tr><td><code>title</code></td><td>Page title for SEO</td><td>No</td></tr>
                                    <tr><td><code>description</code></td><td>Meta description for SEO</td><td>No</td></tr>
                                    <tr><td><code>keywords</code></td><td>Meta keywords for SEO</td><td>No</td></tr>
                                    <tr><td><code>template</code></td><td>Blade template for frontend rendering</td><td>No</td></tr>
                                    <tr><td><code>parent_id</code></td><td>Parent site for hierarchy</td><td>No</td></tr>
                                    <tr><td><code>status</code></td><td>draft, published, scheduled, or private</td><td>Yes</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Site Actions</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Edit:</strong> Modify site properties and custom fields</li>
                            <li><strong>Duplicate:</strong> Create a copy with all categories, articles, and inputs</li>
                            <li><strong>Delete:</strong> Soft delete (can be restored from trash)</li>
                            <li><strong>Reorder:</strong> Change position within menu using up/down arrows</li>
                        </ul>
                    </div>

                    {{-- Categories --}}
                    <div class="docs-section" id="categories" data-section>
                        <h2>Categories</h2>
                        <p>
                            Categories organize articles within a site. They're useful for blog sections, news archives,
                            product categories, or any grouped content.
                        </p>

                        <h3>Creating a Category</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Open a site in edit mode</li>
                            <li>Click <strong>Add Category</strong></li>
                            <li>Enter the category name (slug is auto-generated)</li>
                            <li>Add SEO metadata if needed</li>
                            <li>Configure article template fields for this category</li>
                        </ol>

                        <h3>Article Templates</h3>
                        <p>
                            Each category can define a template of fields that will be automatically added to new articles.
                            This ensures consistency across articles in the same category.
                        </p>

                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Example</span></div>
<pre><code>Category: "Blog Posts"
Article Template Fields:
  - Featured Image (gallery)
  - Excerpt (textarea)
  - Content (text_editor)
  - Author (short_text)</code></pre>
                        </div>
                    </div>

                    {{-- Articles --}}
                    <div class="docs-section" id="articles" data-section>
                        <h2>Articles</h2>
                        <p>
                            Articles are individual content items that belong to one or more categories.
                            They support the same custom field system as sites and categories.
                        </p>

                        <h3>Article Properties</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>Field</th><th>Description</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><code>title</code></td><td>Article title</td></tr>
                                    <tr><td><code>meta_title</code></td><td>SEO page title</td></tr>
                                    <tr><td><code>meta_description</code></td><td>SEO description</td></tr>
                                    <tr><td><code>categories</code></td><td>One or more categories (many-to-many)</td></tr>
                                    <tr><td><code>status</code></td><td>draft, published, scheduled, or private</td></tr>
                                    <tr><td><code>published_at</code></td><td>Publish date (for scheduling)</td></tr>
                                    <tr><td><code>sort_order</code></td><td>Order within category listing</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Multi-Category Support</h3>
                        <p>
                            Articles can belong to multiple categories simultaneously. This allows flexible content organization
                            without duplicating articles.
                        </p>
                    </div>

                    {{-- Publishing --}}
                    <div class="docs-section" id="publishing" data-section>
                        <h2>Publishing Workflow</h2>
                        <p>
                            Sites and articles support four publishing states that control visibility on the frontend.
                        </p>

                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>Status</th><th>Visibility</th><th>Use Case</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="pill">Draft</span></td>
                                        <td>Admin only</td>
                                        <td>Work in progress, not ready for review</td>
                                    </tr>
                                    <tr>
                                        <td><span class="pill pill-success">Published</span></td>
                                        <td>Public</td>
                                        <td>Live content visible to all visitors</td>
                                    </tr>
                                    <tr>
                                        <td><span class="pill pill-warning">Scheduled</span></td>
                                        <td>Public after date</td>
                                        <td>Publish automatically at a future date</td>
                                    </tr>
                                    <tr>
                                        <td><span class="pill pill-primary">Private</span></td>
                                        <td>Logged-in users</td>
                                        <td>Member-only content</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Scheduling Content</h3>
                        <p>
                            To schedule content, set the status to <strong>Scheduled</strong> and choose a future
                            <code>published_at</code> date. The content becomes visible automatically when the date is reached.
                        </p>

                        <h3>Soft Deletes</h3>
                        <p>
                            Deleted sites and articles are moved to trash and can be restored. Use <strong>Force Delete</strong>
                            to permanently remove content.
                        </p>
                    </div>

                    {{-- Input System --}}
                    <div class="docs-section" id="input-system" data-section>
                        <h2>Input System Overview</h2>
                        <p>
                            The Input System is Agares CMS's flexible custom fields solution. It allows you to attach
                            any type of content (text, images, files, forms) to sites, categories, or articles.
                        </p>

                        <div class="hierarchy-tree">
                            <div class="level-0">InputTemplate (Reusable template)</div>
                            <div class="level-1"><span class="arrow">└─</span> InputTemplateItem (Field definition)</div>
                            <div class="level-2"><span class="arrow">   └─</span> InputField (Field type: text, gallery, etc.)</div>
                            <br>
                            <div class="level-0">Content (Site/Category/Article)</div>
                            <div class="level-1"><span class="arrow">└─</span> InputInstance (Actual field with value)</div>
                            <div class="level-2"><span class="arrow">   └─</span> Gallery / Files (Attachments)</div>
                        </div>

                        <h3>Key Concepts</h3>
                        <div class="kvs">
                            <div>InputField</div><div>Defines field type (text, gallery, file, form, etc.)</div>
                            <div>InputTemplate</div><div>Reusable collection of fields that can be applied to content</div>
                            <div>InputInstance</div><div>Actual field attached to a site/category/article with a value</div>
                            <div>Variable</div><div>Unique identifier used to access the field value in templates</div>
                        </div>

                        <h3>Adding Custom Fields</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Open any site, category, or article in edit mode</li>
                            <li>Click <strong>Add Input</strong></li>
                            <li>Select the field type</li>
                            <li>Enter a label and variable name</li>
                            <li>Fill in the value and save</li>
                        </ol>
                    </div>

                    {{-- Input Field Types --}}
                    <div class="docs-section" id="input-fields" data-section>
                        <h2>Field Types</h2>
                        <p>
                            Agares CMS supports various field types for different content needs.
                        </p>

                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>Field Type</th><th>Description</th><th>Best For</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>short_text</code></td>
                                        <td>Single-line text input</td>
                                        <td>Titles, names, short strings</td>
                                    </tr>
                                    <tr>
                                        <td><code>text_editor</code></td>
                                        <td>Rich HTML editor (WYSIWYG)</td>
                                        <td>Main content, formatted text</td>
                                    </tr>
                                    <tr>
                                        <td><code>textarea</code></td>
                                        <td>Multi-line plain text</td>
                                        <td>Descriptions, excerpts</td>
                                    </tr>
                                    <tr>
                                        <td><code>gallery</code></td>
                                        <td>Image collection with reordering</td>
                                        <td>Photo galleries, sliders</td>
                                    </tr>
                                    <tr>
                                        <td><code>file</code></td>
                                        <td>Document/file attachments</td>
                                        <td>PDFs, downloads</td>
                                    </tr>
                                    <tr>
                                        <td><code>contact_form</code></td>
                                        <td>Auto-creates a contact form</td>
                                        <td>Contact pages, inquiries</td>
                                    </tr>
                                    <tr>
                                        <td><code>number</code></td>
                                        <td>Numeric input</td>
                                        <td>Prices, quantities</td>
                                    </tr>
                                    <tr>
                                        <td><code>email</code></td>
                                        <td>Email input with validation</td>
                                        <td>Contact emails</td>
                                    </tr>
                                    <tr>
                                        <td><code>date</code></td>
                                        <td>Date picker</td>
                                        <td>Event dates, deadlines</td>
                                    </tr>
                                    <tr>
                                        <td><code>checkbox</code></td>
                                        <td>Boolean toggle</td>
                                        <td>Yes/no options</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Input Templates --}}
                    <div class="docs-section" id="input-templates" data-section>
                        <h2>Input Templates</h2>
                        <p>
                            Templates allow you to define reusable sets of fields that can be applied to multiple content items.
                            This ensures consistency and saves time when creating similar pages.
                        </p>

                        <h3>Applying Templates</h3>
                        <p>When applying a template to content, you can choose between two modes:</p>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Missing Only:</strong> Only adds fields that don't already exist</li>
                            <li><strong>Overwrite:</strong> Replaces existing fields with template values</li>
                        </ul>

                        <h3>Template Scope</h3>
                        <p>
                            Templates are scoped to content types. A template marked as <code>applies_to: site</code>
                            can only be applied to sites, not categories or articles.
                        </p>
                    </div>

                    {{-- Galleries --}}
                    <div class="docs-section" id="galleries" data-section>
                        <h2>Galleries</h2>
                        <p>
                            Gallery fields allow you to attach multiple images to content with drag-and-drop reordering.
                        </p>

                        <h3>Gallery Features</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Upload multiple images at once</li>
                            <li>Drag-and-drop reordering</li>
                            <li>Individual image removal</li>
                            <li>Alt text and descriptions per image</li>
                            <li>Automatic thumbnail generation</li>
                        </ul>

                        <h3>Using Galleries in Templates</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Blade</span><button class="code-copy">Copy</button></div>
<pre><code>@verbatim
@@foreach($input->galleryMedia as $media)
    &lt;img src="{{ asset($media->file_path) }}"
         alt="{{ $media->alternative }}"&gt;
@@endforeach
@endverbatim</code></pre>
                        </div>
                    </div>

                    {{-- Contact Forms --}}
                    <div class="docs-section" id="contact-forms" data-section>
                        <h2>Contact Forms</h2>
                        <p>
                            The <code>contact_form</code> field type automatically creates a customizable contact form
                            with email notifications.
                        </p>

                        <h3>Default Form Fields</h3>
                        <p>New contact forms include these default fields:</p>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Name (text, required)</li>
                            <li>Phone (tel)</li>
                            <li>Email (email, required)</li>
                            <li>Message (textarea, required)</li>
                            <li>Consent checkbox (required)</li>
                        </ul>

                        <h3>Form Settings</h3>
                        <div class="kvs">
                            <div>Recipients</div><div>Email addresses to receive submissions (comma-separated)</div>
                            <div>From Email</div><div>Sender email address</div>
                            <div>From Name</div><div>Sender name</div>
                            <div>Reply-To Field</div><div>Which form field to use for reply-to</div>
                            <div>Success Message</div><div>Message shown after successful submission</div>
                        </div>

                        <h3>Adding/Editing Fields</h3>
                        <p>
                            You can add, remove, and reorder form fields. Supported field types:
                            <code>text</code>, <code>email</code>, <code>tel</code>, <code>textarea</code>,
                            <code>checkbox</code>, <code>number</code>, <code>date</code>, <code>file</code>.
                        </p>
                    </div>

                    {{-- Media Library --}}
                    <div class="docs-section" id="media-library" data-section>
                        <h2>Media Library</h2>
                        <p>
                            The Media Library is the central repository for all uploaded files and images.
                            Files are organized by upload date and type.
                        </p>

                        <h3>Storage Structure</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Filesystem</span></div>
<pre><code>/public/uploads/
├── images/YYYY/MM/     # Uploaded images
├── files/YYYY/MM/      # Uploaded documents
├── galleries/{id}/     # Gallery uploads
└── files/{id}/         # Input instance files</code></pre>
                        </div>

                        <h3>Media Actions</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Upload:</strong> Add new files (max 10MB per file)</li>
                            <li><strong>Rename:</strong> Change the filename</li>
                            <li><strong>Update:</strong> Edit alt text and description</li>
                            <li><strong>Delete:</strong> Remove file (caution: may break references)</li>
                        </ul>

                        <div class="alert alert-warning">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10 2h4l8 20H2L10 2z"/>
                            </svg>
                            <div>
                                <strong>Warning:</strong> Deleting media files may break references in galleries and content.
                                Files are shared across the CMS and not duplicated.
                            </div>
                        </div>
                    </div>

                    {{-- File Uploads --}}
                    <div class="docs-section" id="file-uploads" data-section>
                        <h2>File Uploads</h2>
                        <p>
                            Files can be uploaded in several contexts: Media Library, Gallery fields, and File input fields.
                        </p>

                        <h3>Upload Contexts</h3>
                        <div class="kvs">
                            <div><code>media</code></div><div>General media library upload</div>
                            <div><code>gallery</code></div><div>Gallery field images</div>
                            <div><code>input_file</code></div><div>File attachment field</div>
                        </div>

                        <h3>Filename Handling</h3>
                        <p>
                            You can choose to keep original filenames (slugified) or generate UUIDs.
                            Collision avoidance appends numeric suffixes when files with the same name exist.
                        </p>
                    </div>

                    {{-- Menus --}}
                    <div class="docs-section" id="menus" data-section>
                        <h2>Menus</h2>
                        <p>
                            Menus are top-level navigation containers. Sites are attached to menus and can be
                            reordered to control navigation display.
                        </p>

                        <h3>Managing Menus</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Create menus for different navigation areas (main, footer, sidebar)</li>
                            <li>Assign sites to menus during site creation or editing</li>
                            <li>Reorder sites within a menu using up/down arrows</li>
                            <li>Delete menus (cascade-deletes associated sites)</li>
                        </ul>

                        <h3>Accessing Menus in Templates</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Blade</span><button class="code-copy">Copy</button></div>
<pre><code>@verbatim
// By menu name
@@php $menuTree = \App\Support\MenuTree::byName('main'); @@endphp

// By menu ID
@@php $menuTree = \App\Support\MenuTree::byId(1); @@endphp

// Render navigation
@@foreach($menuTree as $item)
    &lt;a href="/{{ $item['slug'] }}"&gt;{{ $item['name'] }}&lt;/a&gt;
    @@if(!empty($item['children']))
        @@foreach($item['children'] as $child)
            &lt;a href="/{{ $child['slug'] }}"&gt;{{ $child['name'] }}&lt;/a&gt;
        @@endforeach
    @@endif
@@endforeach
@endverbatim</code></pre>
                        </div>
                    </div>

                    {{-- Site Hierarchy --}}
                    <div class="docs-section" id="site-hierarchy" data-section>
                        <h2>Site Hierarchy</h2>
                        <p>
                            Sites can have parent-child relationships to create hierarchical navigation structures.
                            Child sites inherit their parent's menu position.
                        </p>

                        <h3>Example Structure</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Hierarchy</span></div>
<pre><code>About Us (parent)
├── Our Team (child)
├── History (child)
└── Careers (child)
    └── Engineering (grandchild)</code></pre>
                        </div>

                        <h3>URL Structure</h3>
                        <p>
                            Site URLs are based on slugs. Hierarchy doesn't affect URLs by default.
                            Each site has its own unique slug regardless of parent relationship.
                        </p>
                    </div>

                    {{-- Users --}}
                    <div class="docs-section" id="users" data-section>
                        <h2>User Management</h2>
                        <p>
                            Manage admin users with role-based access control. Each user has a role that determines
                            their permissions.
                        </p>

                        <h3>Creating Users</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Navigate to <strong>Users</strong></li>
                            <li>Click <strong>Add User</strong></li>
                            <li>Enter username, email, name, and password</li>
                            <li>Assign a role</li>
                            <li>Save the user</li>
                        </ol>

                        <h3>User Properties</h3>
                        <div class="kvs">
                            <div><code>username</code></div><div>Unique login identifier</div>
                            <div><code>email</code></div><div>Unique email address</div>
                            <div><code>name</code></div><div>First name</div>
                            <div><code>surname</code></div><div>Last name</div>
                            <div><code>role</code></div><div>Permission role (owner, editor, etc.)</div>
                        </div>
                    </div>

                    {{-- Roles & Permissions --}}
                    <div class="docs-section" id="roles" data-section>
                        <h2>Roles & Permissions</h2>
                        <p>
                            Agares CMS uses Spatie Laravel Permission for role-based access control.
                            Roles have global permissions and optional per-site permissions.
                        </p>

                        <h3>Built-in Permissions</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>Permission</th><th>Allows</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><code>manage dashboard</code></td><td>Access admin dashboard</td></tr>
                                    <tr><td><code>manage sites</code></td><td>Create, edit, delete sites</td></tr>
                                    <tr><td><code>manage categories</code></td><td>Manage categories</td></tr>
                                    <tr><td><code>manage articles</code></td><td>Manage articles</td></tr>
                                    <tr><td><code>manage users</code></td><td>Create and manage users</td></tr>
                                    <tr><td><code>manage permissions</code></td><td>Edit roles and permissions</td></tr>
                                    <tr><td><code>manage settings</code></td><td>Access global settings</td></tr>
                                    <tr><td><code>manage media</code></td><td>Access media library</td></tr>
                                    <tr><td><code>manage menus</code></td><td>Create and manage menus</td></tr>
                                    <tr><td><code>manage custom</code></td><td>Edit custom code</td></tr>
                                    <tr><td><code>view analytics</code></td><td>View GA4 analytics data</td></tr>
                                    <tr><td><code>view unpublished content</code></td><td>See draft/private content</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Owner Role</h3>
                        <p>
                            The <strong>owner</strong> role is a special system role with full access.
                            It cannot be edited or deleted and always has all permissions.
                        </p>
                    </div>

                    {{-- Site-Level Permissions --}}
                    <div class="docs-section" id="site-permissions" data-section>
                        <h2>Site-Level Access</h2>
                        <p>
                            Beyond global permissions, you can control access to individual sites per role.
                            This allows complex multi-tenant scenarios.
                        </p>

                        <h3>Site Permissions</h3>
                        <div class="kvs">
                            <div><code>can_view</code></div><div>Can see the site in admin</div>
                            <div><code>can_edit</code></div><div>Can modify site properties</div>
                            <div><code>can_categories</code></div><div>Can manage site's categories</div>
                            <div><code>can_articles</code></div><div>Can manage site's articles</div>
                        </div>

                        <h3>Editing Site Permissions</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Navigate to <strong>Permissions</strong></li>
                            <li>Click <strong>Edit</strong> on a role</li>
                            <li>Scroll to <strong>Per-Site Permissions</strong></li>
                            <li>Toggle permissions for each site</li>
                            <li>Save changes</li>
                        </ol>
                    </div>

                    {{-- Global Settings --}}
                    <div class="docs-section" id="global-settings" data-section>
                        <h2>Global Settings</h2>
                        <p>
                            Global settings control site-wide configuration. Settings are cached for performance
                            and automatically invalidated when changed.
                        </p>

                        <h3>Accessing Settings</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">PHP</span><button class="code-copy">Copy</button></div>
<pre><code>// Get setting value
$value = \App\Models\Setting::get('site_name', 'Default');

// Get as boolean
$enabled = \App\Models\Setting::bool('enable_registration', false);

// Get as integer
$limit = \App\Models\Setting::int('items_per_page', 10);</code></pre>
                        </div>

                        <h3>Setting Types</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><code>string</code> - Text values</li>
                            <li><code>integer</code> - Numeric values</li>
                            <li><code>boolean</code> - True/false values</li>
                            <li><code>json</code> - Complex data structures</li>
                        </ul>

                        <h3>Custom Settings</h3>
                        <p>
                            You can add custom key-value settings from the Settings page.
                            These are useful for site-specific configuration.
                        </p>
                    </div>

                    {{-- SEO --}}
                    <div class="docs-section" id="seo" data-section>
                        <h2>SEO Configuration</h2>
                        <p>
                            Agares CMS provides built-in SEO features including meta tags, robots.txt, and sitemap generation.
                        </p>

                        <h3>Meta Tags</h3>
                        <p>Each site, category, and article can have:</p>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li><strong>Title:</strong> Page title for search engines</li>
                            <li><strong>Description:</strong> Meta description</li>
                            <li><strong>Keywords:</strong> Meta keywords (less important for modern SEO)</li>
                        </ul>

                        <h3>robots.txt</h3>
                        <p>
                            Edit your robots.txt file directly from Settings. This controls which pages
                            search engines should crawl.
                        </p>

                        <h3>Sitemap</h3>
                        <p>
                            Generate a sitemap.xml from Settings. The sitemap includes all published
                            sites and articles with proper priority and change frequency.
                        </p>
                    </div>

                    {{-- Custom Code --}}
                    <div class="docs-section" id="custom-code" data-section>
                        <h2>Custom Code</h2>
                        <p>
                            Inject custom HTML, CSS, and JavaScript across all pages without modifying templates.
                        </p>

                        <h3>Use Cases</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Google Analytics / Tag Manager</li>
                            <li>Custom fonts (Google Fonts, Adobe Fonts)</li>
                            <li>Third-party widgets (chat, feedback)</li>
                            <li>Site-wide CSS customizations</li>
                            <li>Tracking pixels</li>
                        </ul>

                        <h3>Code Types</h3>
                        <div class="kvs">
                            <div><code>script</code></div><div>JavaScript code (added before &lt;/body&gt;)</div>
                            <div><code>style</code></div><div>CSS code (added in &lt;head&gt;)</div>
                        </div>
                    </div>

                    {{-- Cookies --}}
                    <div class="docs-section" id="cookies" data-section>
                        <h2>Cookie Consent</h2>
                        <p>
                            Manage GDPR-compliant cookie consent with automatic cookie scanning and consent banners.
                        </p>

                        <h3>Cookie Scanner</h3>
                        <p>
                            Scan your site to detect cookies and categorize them. Results help you
                            configure the consent banner accurately.
                        </p>

                        <h3>Consent Settings</h3>
                        <ul style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Customize consent banner text</li>
                            <li>Configure cookie categories</li>
                            <li>Set default consent preferences</li>
                            <li>Manage scan history</li>
                        </ul>
                    </div>

                    {{-- Frontend Development --}}
                    <div class="docs-section" id="frontend-dev" data-section>
                        <h2>Frontend Development</h2>
                        <p>
                            Build custom frontend themes using Laravel Blade templates.
                            Templates receive content data and render the public-facing website.
                        </p>

                        <h3>Frontend Routes</h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr><th>URL Pattern</th><th>Controller Method</th><th>Description</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><code>/</code></td><td>showHomepage()</td><td>Homepage (configured via home_url setting)</td></tr>
                                    <tr><td><code>/{site:slug}</code></td><td>showSite()</td><td>Site/page view</td></tr>
                                    <tr><td><code>/{site:slug}/{category}</code></td><td>showCategory()</td><td>Category listing</td></tr>
                                    <tr><td><code>/{site:slug}/{category}/{id}/{slug}</code></td><td>showArticle()</td><td>Article view</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Template Location</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Path</span></div>
<pre><code>resources/views/pages/frontend/sites/{template}.blade.php</code></pre>
                        </div>
                    </div>

                    {{-- Blade Templates --}}
                    <div class="docs-section" id="blade-templates" data-section>
                        <h2>Blade Templates</h2>
                        <p>
                            Templates receive a <code>$data</code> array with all content and input values.
                        </p>

                        <h3>Available Variables</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">PHP</span><button class="code-copy">Copy</button></div>
<pre><code>$site        // Site model
$category    // Category model (if applicable)
$article     // Article model (if applicable)
$content     // Keyed array of input values by variable name
$content_list // Collection of all InputInstance objects</code></pre>
                        </div>

                        <h3>Accessing Input Values</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Blade</span><button class="code-copy">Copy</button></div>
<pre><code>@verbatim
{{-- By variable name --}}
{{ $content['header_text'] ?? '' }}

{{-- Using helper function --}}
{{ input_value('header_text', $site, $category ?? null, $article ?? null) }}

{{-- Rich content (unescaped HTML) --}}
{!! $content['main_content'] ?? '' !!}

{{-- Looping through inputs --}}
@@foreach($content_list as $input)
    @@if($input->field->field_type === 'gallery')
        @@foreach($input->galleryMedia as $media)
            &lt;img src="{{ asset($media->file_path) }}"&gt;
        @@endforeach
    @@endif
@@endforeach
@endverbatim</code></pre>
                        </div>
                    </div>

                    {{-- Helper Functions --}}
                    <div class="docs-section" id="helper-functions" data-section>
                        <h2>Helper Functions</h2>
                        <p>
                            Agares CMS provides helper functions for common tasks in templates.
                        </p>

                        <h3>input_value()</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">PHP</span><button class="code-copy">Copy</button></div>
<pre><code>/**
 * Get input value with fallback chain
 * Checks: article → category → site → empty string
 */
input_value(
    string $variable,
    $site = null,
    $category = null,
    $article = null
): string</code></pre>
                        </div>

                        <h3>contact_form_from_instance()</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">PHP</span><button class="code-copy">Copy</button></div>
<pre><code>/**
 * Get Form model from contact_form InputInstance
 */
contact_form_from_instance(?InputInstance $instance): ?Form</code></pre>
                        </div>

                        <h3>MenuTree</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">PHP</span><button class="code-copy">Copy</button></div>
<pre><code>// Get hierarchical menu tree
MenuTree::byId(int $menuId): Collection
MenuTree::byName(string $name): Collection

// Results cached for 10 minutes</code></pre>
                        </div>
                    </div>

                    {{-- API Integration --}}
                    <div class="docs-section" id="api-integration" data-section>
                        <h2>API Integration</h2>
                        <p>
                            Agares CMS includes a REST API for headless usage with React, Next.js, or mobile apps.
                        </p>

                        <div class="alert alert-info">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            <div>
                                <strong>Full API Documentation:</strong>
                                <a href="{{ route('admin.api.documentation') }}">View API Documentation</a>
                            </div>
                        </div>

                        <h3>Quick Start</h3>
                        <ol style="color: var(--color-text-secondary); padding-left: var(--space-xl);">
                            <li>Create an API key in <a href="{{ route('admin.api.index') }}">Admin → API</a></li>
                            <li>Assign appropriate abilities (content:read, etc.)</li>
                            <li>Use the key in the <code>X-API-Key</code> header</li>
                        </ol>

                        <h3>Example Request</h3>
                        <div class="code-block">
                            <div class="code-header"><span class="code-language">Bash</span><button class="code-copy">Copy</button></div>
<pre><code>curl -H "X-API-Key: ak_your_key" "{{ url('/api/v1/sites/home') }}"</code></pre>
                        </div>

                        <h3>API Endpoints</h3>
                        <div class="kvs">
                            <div><code>GET /api/v1/sites</code></div><div>List all public sites</div>
                            <div><code>GET /api/v1/sites/{slug}</code></div><div>Get site with inputs</div>
                            <div><code>GET /api/v1/menus</code></div><div>Get navigation menus</div>
                            <div><code>GET /api/v1/articles/{id}</code></div><div>Get article with inputs</div>
                            <div><code>GET /api/v1/settings</code></div><div>Get public settings</div>
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
</x-app-layout>
