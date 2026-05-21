<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Site;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function menus()
    {
        $menus = Menu::query()
            ->with(['sites' => function ($q) {
                // IMPORTANT: only public sites
                $q->whereNull('sites.deleted_at')
                  ->where('sites.status', Site::STATUS_PUBLISHED);
            }])
            ->get()
            ->map(function (Menu $m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'sites' => $m->sites->map(fn (Site $s) => $this->siteSummary($s)),
                ];
            });

        return response()->json(['data' => $menus]);
    }

    public function sites()
    {
        $sites = Site::query()
            ->public()
            ->with(['children' => fn($q) => $q->public()->orderBy('menu_order')])
            ->orderBy('menu_order')
            ->get()
            ->map(fn (Site $s) => $this->siteSummary($s));

        return response()->json(['data' => $sites]);
    }

    public function siteBySlug(string $slug)
    {
        $site = Site::query()
            ->public()
            ->where('slug', $slug)
            ->with([
                'children' => fn($q) => $q->public()->orderBy('menu_order'),
                'inputInstances.field',
                'inputInstances.files',
                'inputInstances.gallery',
                'inputInstances.galleryMedia',   // from your InputInstance model
                'galleries.media',
            ])
            ->firstOrFail();

        return response()->json(['data' => $this->siteFull($site)]);
    }

    public function previewSiteBySlug(string $slug)
    {
        // preview: include drafts, scheduled, private, trashed if you want (I suggest no trashed)
        $site = Site::query()
            ->where('slug', $slug)
            ->with([
                'children' => fn($q) => $q->orderBy('menu_order'),
                'inputInstances.field',
                'inputInstances.files',
                'inputInstances.gallery',
                'inputInstances.galleryMedia',
                'galleries.media',
            ])
            ->firstOrFail();

        return response()->json(['data' => $this->siteFull($site)]);
    }

    public function categoriesBySite(string $siteSlug)
    {
        $site = Site::query()->public()->where('slug', $siteSlug)->firstOrFail();

        $cats = $site->categories()
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
            ]);

        return response()->json(['data' => $cats]);
    }

    public function articlesBySite(string $siteSlug, Request $request)
    {
        $site = Site::query()->public()->where('slug', $siteSlug)->firstOrFail();

        $q = Article::query()
            ->public()
            ->where('site_id', $site->id)
            ->with('categories');

        if ($request->filled('category_id')) {
            $q->whereHas('categories', fn($qq) => $qq->where('categories.id', (int)$request->category_id));
        }

        $articles = $q->orderByDesc('published_at')->paginate(12);

        // transform
        $articles->getCollection()->transform(fn (Article $a) => $this->articleSummary($a));

        return response()->json($articles);
    }

    public function article(Article $article)
    {
        // public-only
        abort_unless(
            $article->status === Article::STATUS_PUBLISHED
            || ($article->status === Article::STATUS_SCHEDULED && $article->published_at && $article->published_at->isPast()),
            404
        );

        $article->load([
            'site',
            'categories',
            'inputInstances.field',
            'inputInstances.files',
            'inputInstances.gallery',
            'inputInstances.galleryMedia',
            'galleries.media',
        ]);

        return response()->json(['data' => $this->articleFull($article)]);
    }

    public function previewArticle(Article $article)
    {
        $article->load([
            'site',
            'categories',
            'inputInstances.field',
            'inputInstances.files',
            'inputInstances.gallery',
            'inputInstances.galleryMedia',
            'galleries.media',
        ]);

        return response()->json(['data' => $this->articleFull($article)]);
    }

    public function articlesByCategory(Category $category)
    {
        $articles = $category->articles()
            ->whereNull('articles.deleted_at')
            ->whereIn('articles.status', [Article::STATUS_PUBLISHED, Article::STATUS_SCHEDULED])
            ->where(function ($q) {
                $q->where('articles.status', Article::STATUS_PUBLISHED)
                  ->orWhere('articles.published_at', '<=', now());
            })
            ->with('categories')
            ->orderByDesc('articles.published_at')
            ->paginate(12);

        $articles->getCollection()->transform(fn (Article $a) => $this->articleSummary($a));

        return response()->json($articles);
    }

    /* =========================
       Transformers (shape API)
       ========================= */

    private function siteSummary(Site $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'title' => $s->title,
            'description' => $s->description,
            'status' => $s->status,
            'menu_order' => $s->menu_order,
            'parent_id' => $s->parent_id,
        ];
    }

    private function siteFull(Site $s): array
    {
        return array_merge($this->siteSummary($s), [
            'seo' => [
                'meta_title' => $s->title,
                'meta_description' => $s->description,
                'keywords' => $s->keywords,
            ],
            'children' => $s->children->map(fn (Site $c) => $this->siteSummary($c))->values(),
            'inputs' => $s->inputInstances->map(fn ($i) => $this->inputInstance($i))->values(),
            'galleries' => $s->galleries->map(fn ($g) => $this->gallery($g))->values(),
        ]);
    }

    private function articleSummary(Article $a): array
    {
        return [
            'id' => $a->id,
            'site_id' => $a->site_id,
            'title' => $a->title,
            'meta_title' => $a->meta_title,
            'meta_description' => $a->meta_description,
            'meta_keywords' => $a->meta_keywords,
            'description' => $a->description,
            'status' => $a->status,
            'published_at' => optional($a->published_at)->toIso8601String(),
            'categories' => $a->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),
        ];
    }

    private function articleFull(Article $a): array
    {
        return array_merge($this->articleSummary($a), [
            'content' => $a->content,
            'inputs' => $a->inputInstances->map(fn ($i) => $this->inputInstance($i))->values(),
            'galleries' => $a->galleries->map(fn ($g) => $this->gallery($g))->values(),
        ]);
    }

    private function inputInstance($i): array
    {
        // Note: value can be JSON string for some field types – keep raw + parsed
        $raw = $i->value;
        $parsed = is_string($raw) ? json_decode($raw, true) : $raw;

        return [
            'id' => $i->id,
            'label' => $i->label,
            'variable' => $i->variable,
            'description' => $i->description,
            'sort_order' => $i->sort_order,
            'field' => [
                'id' => $i->field?->id,
                'name' => $i->field?->name,
                'type' => $i->field?->field_type,
            ],
            'value_raw' => $raw,
            'value' => $parsed ?? $raw,

            // direct files
            'files' => $i->files->map(fn($m) => $this->media($m))->values(),

            // gallery linked to input (if any)
            'gallery_id' => $i->gallery_id,
            'gallery_media' => $i->galleryMedia->map(fn($m) => $this->media($m))->values(),

            // if it's a contact-form input with embedded form_id
            'form_id' => $i->form_id,
        ];
    }

    private function gallery($g): array
    {
        return [
            'id' => $g->id,
            'name' => $g->name,
            'variable' => $g->variable,
            'sort_order' => $g->sort_order,
            'media' => $g->media->map(fn($m) => $this->media($m))->values(),
        ];
    }

    private function media($m): array
    {
        return [
            'id' => $m->id,
            'original_name' => $m->original_name,
            'file_name' => $m->file_name,
            'mime_type' => $m->mime_type,
            'size' => $m->size,
            'type' => $m->type,
            'alt' => $m->alternative,
            'description' => $m->description,
            'url' => $m->url, // accessor
        ];
    }
}
