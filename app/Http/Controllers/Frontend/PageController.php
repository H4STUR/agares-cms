<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Article;
use App\Models\InputInstance;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function showHomepage()
    {
        $homeUrl = Setting::where('key', 'home_url')->value('value');

        if (!$homeUrl) abort(404, 'Homepage is not configured.');

        $site = Site::where('slug', $homeUrl)->first();
        if (!$site) abort(404, 'Homepage site not found.');

        return $this->renderSite($site);
    }

    public function showSite(Site $site)
    {
        return $this->renderSite($site);
    }

    private function renderSite(Site $site)
    {
        $site->load([
            'categories',

            'articles' => function ($q) {
                if (!auth()->check() || !auth()->user()->can('view unpublished content')) {
                    $q->public();
                } else {
                    $q->withTrashed();
                }

                $q->with([
                    'inputInstances.field',
                    'inputInstances.files',
                ]);
            },


            'categories.articles' => function ($q) {
                if (!auth()->check() || !auth()->user()->can('view unpublished content')) {
                    $q->public();
                } else {
                    $q->withTrashed();
                }

                // 👇 REQUIRED for thumbnails
                $q->with([
                    'inputInstances.field',
                    'inputInstances.files',
                ]);
            },
        ]);


        // Site inputs
        $inputs = $this->loadOwnerInputs(Site::class, $site->id);
        $siteInputsList = $inputs['list'];
        $siteInputsByVar = $inputs['byVar'];

        // Keep your "data" contract: content = keyed collection, plus flattened vars
        $data = array_merge([
            'site'       => $site,
            'categories' => $site->categories,
            'articles'   => $site->articles,

            // BOTH shapes available in blade:
            'content_site'     => $siteInputsByVar,
            'content_site_list'=> $siteInputsList,

            'content'          => $siteInputsByVar,
            'content_list'     => $siteInputsList,
        ], $siteInputsByVar->all());


        $view = 'pages.frontend.sites.' . ($site->template ?: 'index');
        return view($view, compact('data'));
    }

    public function showCategory(Site $site, $categoryName)
    {
        $category = Category::where('site_id', $site->id)
            ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [Str::slug($categoryName)])
            ->firstOrFail();

        $category->load(['articles' => function ($q) {
            if (!auth()->check() || !auth()->user()->can('view unpublished content')) {
                $q->public();
            } else {
                $q->withTrashed();
            }
        }]);

        $siteInputs     = $this->loadOwnerInputs(Site::class, $site->id);
        $categoryInputs = $this->loadOwnerInputs(Category::class, $category->id);

        $merged = $this->mergeInputs($siteInputs['byVar'], $categoryInputs['byVar']);

        $data = array_merge([
            'site'       => $site,
            'category'   => $category,
            'articles'   => $category->articles,

            'content_site'     => $siteInputs['byVar'],
            'content_category' => $categoryInputs['byVar'],
            'content'          => $merged,
        ], $merged->all());

        $view = 'pages.frontend.categories.' . ($category->template ?: 'index');
        return view($view, compact('data'));
    }


    public function showArticle(Site $site, $categoryName, $articleId, $articleName)
    {
        $category = Category::where('site_id', $site->id)
            ->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [Str::slug($categoryName)])
            ->firstOrFail();

        $article = Article::withTrashed()
            ->where('id', $articleId)
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
            ->firstOrFail();

        if (Str::slug($article->title) !== $articleName) {
            abort(404, 'Article title mismatch.');
        }

        $isPublic =
            !$article->deleted_at
            && in_array($article->status, ['published', 'scheduled'], true)
            && (
                $article->status === 'published'
                || ($article->published_at && $article->published_at <= now())
            );

        if (!$isPublic) {
            abort_unless(
                auth()->check() && auth()->user()->can('view unpublished content'),
                404
            );
        }

        $siteInputs     = $this->loadOwnerInputs(Site::class, $site->id);
        $categoryInputs = $this->loadOwnerInputs(Category::class, $category->id);
        $articleInputs  = $this->loadOwnerInputs(Article::class, $article->id);

        $merged = $this->mergeInputs(
            $siteInputs['byVar'],
            $categoryInputs['byVar'],
            $articleInputs['byVar']
        );

        $data = array_merge([
            'site'       => $site,
            'category'   => $category,
            'article'    => $article,

            'content_site'     => $siteInputs['byVar'],
            'content_category' => $categoryInputs['byVar'],
            'content_article'  => $articleInputs['byVar'],
            'content'          => $merged,
        ], $merged->all());

        $view = 'pages.frontend.articles.' . ($article->template ?: 'index');
        return view($view, compact('data'));
    }


    /**
     * Fetch inputs for an owner (Site/Category/Article) from input_instances.
     * Returns a collection keyed by variable, sorted by sort_order.
     */
    protected function loadOwnerInputs(string $ownerClass, int $ownerId)
    {
        $relations = [];

        foreach (['field', 'gallery', 'gallery.media'] as $rel) {
            $top = explode('.', $rel)[0];
            if (method_exists(InputInstance::class, $top)) {
                $relations[] = $rel;
            }
        }

        // IMPORTANT: files relation is named "files" in your InputInstance model (belongsToMany)
        if (method_exists(InputInstance::class, 'files')) {
            $relations[] = 'files';
        }
        if (method_exists(InputInstance::class, 'galleryMedia')) {
            $relations[] = 'galleryMedia';
        }

        $list = InputInstance::query()
            ->where('owner_type', $ownerClass)
            ->where('owner_id', $ownerId)
            ->orderBy('sort_order')
            ->with($relations)
            ->get();

        // Key only those that actually have a variable
        $byVar = $list
            ->filter(fn($i) => is_string($i->variable) && trim($i->variable) !== '')
            ->keyBy('variable');

        return [
            'list' => $list,
            'byVar' => $byVar,
        ];
    }


    /**
     * Merge keyed input collections with override priority from left->right.
     * Later collections override earlier ones for the same variable.
     */
    protected function mergeInputs(...$collections)
    {
        $merged = collect();

        foreach ($collections as $c) {
            if (!$c) continue;
            foreach ($c as $variable => $instance) {
                $merged->put($variable, $instance);
            }
        }

        return $merged;
    }

}
