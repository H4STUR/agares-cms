<?php

namespace App\Services\AiSeo;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Site;
use App\Models\Ecommerce\Product;
use Illuminate\Support\Str;

class SeoPayloadBuilder
{
    private const MAX_BODY_CHARS = 2000;

    /**
     * Build the AI SEO payload for an Article.
     *
     * Supported $options keys:
     *   - mode: 'generate'|'improve'|'translate' (default 'generate')
     *   - focus_keyword: ?string
     *   - intent: ?string
     *   - current_meta_title: ?string         override existing for improve mode
     *   - current_meta_description: ?string
     *   - current_slug: ?string
     *   - include_schema_jsonld, include_og, include_slug_suggestion, include_image_alt: bool (default true)
     *   - model: ?string
     */
    public function forArticle(Article $article, array $options = []): array
    {
        $article->loadMissing(['categories', 'site']);

        $bodyRaw  = (string) ($article->content ?? '');
        $excerpt  = (string) ($article->description ?? '');
        $title    = (string) $article->title;

        $categoryNames = $article->categories->pluck('name')->filter()->values()->all();
        $firstCategorySlug = $article->categories->first()?->name;
        $urlPath = $firstCategorySlug
            ? '/' . trim((string) ($article->site?->slug ?? ''), '/') . '/' . $firstCategorySlug . '/' . $article->id . '/' . Str::slug($title)
            : null;

        return $this->buildPayload(
            contentType:        'article',
            title:              $title,
            excerpt:            $excerpt,
            bodyHtml:           $bodyRaw,
            categories:         $categoryNames,
            urlPath:            $urlPath,
            existingMetaTitle:  $options['current_meta_title']       ?? $article->meta_title,
            existingMetaDesc:   $options['current_meta_description'] ?? $article->meta_description,
            options:            $options,
        );
    }

    /**
     * Build the AI SEO payload for an Ecommerce Product.
     */
    public function forProduct(Product $product, array $options = []): array
    {
        $product->loadMissing(['categories']);

        $bodyHtml = (string) ($product->description ?? '');
        $excerpt  = (string) ($product->short_description ?? '');
        $title    = (string) $product->name;

        $categoryNames = method_exists($product, 'categories')
            ? $product->categories->pluck('name')->filter()->values()->all()
            : [];

        $urlPath = '/shop/product/' . ($product->slug ?: $product->id);

        return $this->buildPayload(
            contentType:        'product',
            title:              $title,
            excerpt:            $excerpt,
            bodyHtml:           $bodyHtml,
            categories:         $categoryNames,
            urlPath:            $urlPath,
            existingMetaTitle:  $options['current_meta_title']       ?? ($product->meta_title ?? null),
            existingMetaDesc:   $options['current_meta_description'] ?? ($product->meta_description ?? null),
            options:            $options,
        );
    }

    /**
     * Build the AI SEO payload for a Site (treated as `page` on the SaaS side, since AgaresCMS has
     * no separate Page model — a Site's SEO tab IS its landing-page metadata).
     *
     * Site fields don't follow the `meta_*` naming convention: `title` / `description` / `keywords`
     * ARE the meta fields. The modal's `current_*` values are passed in for improve mode.
     */
    public function forSite(Site $site, array $options = []): array
    {
        $title    = (string) ($site->title ?: $site->name);
        $excerpt  = (string) ($site->description ?? '');
        $bodyHtml = $excerpt;
        $urlPath  = '/' . trim((string) $site->slug, '/');

        return $this->buildPayload(
            contentType:        'page',
            title:              $title,
            excerpt:            $excerpt,
            bodyHtml:           $bodyHtml,
            categories:         [],
            urlPath:            $urlPath,
            existingMetaTitle:  $options['current_meta_title']       ?? $site->title,
            existingMetaDesc:   $options['current_meta_description'] ?? $site->description,
            options:            $options,
        );
    }

    /**
     * Build the AI SEO payload for a Category.
     */
    public function forCategory(Category $category, array $options = []): array
    {
        $title    = (string) $category->name;
        $excerpt  = (string) ($category->description ?? '');
        $bodyHtml = $excerpt;
        $urlPath  = '/' . trim((string) ($category->site?->slug ?? ''), '/') . '/' . $category->name;

        return $this->buildPayload(
            contentType:        'category',
            title:              $title,
            excerpt:            $excerpt,
            bodyHtml:           $bodyHtml,
            categories:         [],
            urlPath:            $urlPath,
            existingMetaTitle:  $options['current_meta_title']       ?? ($category->meta_title ?? null),
            existingMetaDesc:   $options['current_meta_description'] ?? ($category->meta_description ?? null),
            options:            $options,
        );
    }

    /**
     * Shared payload assembly. Handles HTML stripping, outline extraction, and merging site brand context.
     */
    private function buildPayload(
        string $contentType,
        string $title,
        string $excerpt,
        string $bodyHtml,
        array $categories,
        ?string $urlPath,
        ?string $existingMetaTitle,
        ?string $existingMetaDesc,
        array $options,
    ): array {
        $mode = $options['mode'] ?? 'generate';
        if (! in_array($mode, ['generate', 'improve', 'translate'], true)) {
            $mode = 'generate';
        }

        $strippedBody = $this->stripAndTruncate($bodyHtml);
        $outline      = $this->extractOutline($bodyHtml);
        $locale       = $this->resolveLocale();

        $payload = [
            'site_name'        => Setting::str('site_name', config('app.name', 'Site')),
            'site_description' => $this->nullIfBlank(Setting::str('site_description')),
            'site_locale'      => $locale,
            'site_industry'    => $this->nullIfBlank(Setting::str('ai_seo_industry')),
            'site_audience'    => $this->nullIfBlank(Setting::str('ai_seo_audience')),
            'site_tone'        => $this->nullIfBlank(Setting::str('ai_seo_tone')),

            'content_type'   => $contentType,
            'content_locale' => $locale,
            'title'          => $title,
            'excerpt'        => $this->nullIfBlank($excerpt),
            'body'           => $strippedBody,
            'outline'        => $outline,
            'categories'     => $categories,
            'url_path'       => $this->nullIfBlank((string) $urlPath),

            'existing_meta_title'       => $mode === 'improve' ? $this->nullIfBlank((string) $existingMetaTitle) : null,
            'existing_meta_description' => $mode === 'improve' ? $this->nullIfBlank((string) $existingMetaDesc)  : null,

            'mode'          => $mode,
            'focus_keyword' => $this->nullIfBlank((string) ($options['focus_keyword'] ?? '')),
            'intent'        => $this->nullIfBlank((string) ($options['intent'] ?? '')),

            'include_schema_jsonld'   => (bool) ($options['include_schema_jsonld']   ?? true),
            'include_og'              => (bool) ($options['include_og']              ?? true),
            'include_slug_suggestion' => (bool) ($options['include_slug_suggestion'] ?? true),
            'include_image_alt'       => (bool) ($options['include_image_alt']       ?? true),

            'model' => $this->nullIfBlank((string) ($options['model'] ?? '')),
        ];

        // Drop nulls that the SaaS treats as "not provided" — keeps the payload tidy and avoids
        // accidentally sending empty-string overrides as "real" values.
        return array_filter(
            $payload,
            fn ($v) => $v !== null && $v !== '',
        );
    }

    /**
     * Strip HTML, collapse whitespace, truncate to MAX_BODY_CHARS.
     */
    private function stripAndTruncate(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string) $text);

        return Str::limit($text, self::MAX_BODY_CHARS, '');
    }

    /**
     * Extract H1/H2/H3 headings in document order as ["H1: title", "H2: subtitle"].
     *
     * @return array<int, string>
     */
    private function extractOutline(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $outline = [];

        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new \DOMDocument();
            // Wrap to coax DOMDocument into UTF-8 + a single document root.
            $wrapped = '<?xml encoding="UTF-8"><div>' . $html . '</div>';
            if (! @$doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                return [];
            }

            $xpath = new \DOMXPath($doc);
            $nodes = $xpath->query('//h1 | //h2 | //h3');

            if ($nodes !== false) {
                foreach ($nodes as $node) {
                    $level = strtoupper($node->nodeName);
                    $text  = trim(preg_replace('/\s+/u', ' ', (string) $node->textContent) ?? '');
                    if ($text === '') {
                        continue;
                    }
                    $outline[] = "{$level}: {$text}";
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $outline;
    }

    private function resolveLocale(): string
    {
        $fromSetting = Setting::str('site_default_locale', '');
        if (filled($fromSetting)) {
            return $fromSetting;
        }

        return (string) config('app.locale', 'en');
    }

    private function nullIfBlank(?string $v): ?string
    {
        $v = $v === null ? null : trim($v);
        return ($v === null || $v === '') ? null : $v;
    }
}
