<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Site;
use App\Models\Category;
use App\Models\InputField;
use App\Models\InputInstance;
use App\Models\InputTemplate;
use App\Models\InputTemplateItem;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ArticleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view articles', only: ['edit']),
            new Middleware('can:manage articles', only: [
                'create', 'store', 'update', 'delete', 'reorder',
                'restore', 'forceDelete', 'duplicate',
            ]),
        ];
    }

    public function create(Site $site)
    {
        // for create view (dual list)
        $categories = $site->categories()->orderBy('name')->get();

        return view('pages.admin.articles.create', compact('site', 'categories'));
    }

    public function store(Request $request, Site $site)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'selectedCategoryIds' => 'required|string', // comma-separated ids
            'status'       => 'sometimes|required|in:draft,published,scheduled,private',
            'published_at' => 'sometimes|nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $categoryIds = array_values(array_filter(array_map('intval', explode(',', $validated['selectedCategoryIds']))));

            $validCategoryIds = Category::where('site_id', $site->id)
                ->whereIn('id', $categoryIds)
                ->pluck('id')
                ->toArray();

            if (count($validCategoryIds) !== count($categoryIds)) {
                throw new \Exception('Some selected categories do not belong to this site.');
            }

            $firstCategoryId = $categoryIds[0] ?? null;

            $defaultArticleTpl = 'index';
            if ($firstCategoryId) {
                $firstCategory = Category::where('site_id', $site->id)->find($firstCategoryId);
                $defaultArticleTpl = $firstCategory?->default_article_template ?: 'index';
            }


            $article = Article::create([
                'site_id'     => $site->id,
                'title'       => $validated['title'],
                'template'     => $defaultArticleTpl,
                'status'      => Article::STATUS_DRAFT,
                'published_at'=> null,
                'created_by'  => auth()->id(),
                'updated_by'  => auth()->id(),
            ]);

            $article->categories()->sync($validCategoryIds);

            $this->applyGlobalArticleTemplate($article);
            $this->applyCategoryArticleTemplates($article, $validCategoryIds);

            DB::commit();

            return redirect()
                ->route('admin.articles.edit', [$site->id, $article->id])
                ->with('success', 'Article created and default fields applied.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('An error occurred while adding the article. ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Site $site, Article $article)
    {
        if ($article->site_id !== $site->id) abort(404);

        $article->load([
            'categories',
            'inputInstances.field',
            'inputInstances.files',
            'inputInstances.gallery.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },
        ]);

        // Blade-friendly property (like Site/Category)
        $article->inputInstances->each(function ($input) {
            $input->galleryMedia = $input->gallery?->media ?? collect();
        });

        $inputTypes = InputField::select('id', 'name', 'field_type')
            ->orderBy('field_type')
            ->get();

        $frontendArticleTemplates = collect(File::files(resource_path('views/pages/frontend/articles')))
            ->filter(fn($f) => $f->getExtension() === 'php' && str_ends_with($f->getFilename(), '.blade.php'))
            ->map(fn($f) => str_replace('.blade.php', '', $f->getFilename()))
            ->values();

        $data = [
            'site'       => $site,
            'article'    => $article,
            'categories' => $site->categories()->orderBy('name')->get(),
            'inputTypes' => $inputTypes,
            'inputs'     => $article->inputInstances,
            'frontendArticleTemplates' => $frontendArticleTemplates,
        ];

        return view('pages.admin.articles.edit', compact('data', 'site', 'article'));
    }

    public function update(Request $request, Site $site, Article $article)
{
    if ($article->site_id !== $site->id) abort(404);

    $validated = $request->validate([
        // allow status-only quick updates
        'title'               => 'sometimes|required|string|max:255',
        'selectedCategoryIds' => 'sometimes|required|string', // comma-separated

        'content'          => 'nullable|string',
        'template'         => 'nullable|string|max:255',
        'meta_title'       => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'meta_keywords'    => 'nullable|string',
        'description'      => 'nullable|string',

        'status'           => 'sometimes|required|in:draft,published,scheduled,private',
        'published_at'     => 'sometimes|nullable|date',

        // Input instances (for unified save across all tabs)
        'inputs' => 'sometimes|array',
        'inputs.*.value' => 'nullable',
    ]);

    DB::beginTransaction();

    try {
        // snapshot old category ids BEFORE we sync (only if we will sync)
        $oldIds = $article->categories()->pluck('categories.id')->toArray();

        // -------------------------
        // Normalize status & publish date
        // -------------------------
        $extra = [];

        if (array_key_exists('status', $validated)) {
            $status = $validated['status'];

            if ($status === Article::STATUS_PUBLISHED) {
                $extra['published_at'] = now();
            }

            if ($status === Article::STATUS_DRAFT || $status === Article::STATUS_PRIVATE) {
                $extra['published_at'] = null;
            }

            if ($status === Article::STATUS_SCHEDULED) {
                if (empty($validated['published_at'])) {
                    DB::rollBack();
                    return back()
                        ->withErrors(['published_at' => 'Publish date is required for scheduled articles.'])
                        ->withInput();
                }
                $extra['published_at'] = $validated['published_at'];
            }

            $extra['status'] = $status;
        }

        // -------------------------
        // Update article fields (only set those provided)
        // -------------------------
        $updateData = [
            'updated_by' => auth()->id(),
        ];

        if (array_key_exists('title', $validated))            $updateData['title'] = $validated['title'];
        if (array_key_exists('content', $validated))          $updateData['content'] = $validated['content'];
        if (array_key_exists('template', $validated))         $updateData['template'] = $validated['template'] ?? $article->template;
        if (array_key_exists('description', $validated))      $updateData['description'] = $validated['description'];

        if (array_key_exists('meta_title', $validated))       $updateData['meta_title'] = $validated['meta_title'];
        if (array_key_exists('meta_description', $validated)) $updateData['meta_description'] = $validated['meta_description'];
        if (array_key_exists('meta_keywords', $validated))    $updateData['meta_keywords'] = $validated['meta_keywords'];

        $article->update(array_merge($updateData, $extra));

        // -------------------------
        // Sync categories only if provided
        // -------------------------
        if (array_key_exists('selectedCategoryIds', $validated)) {
            $newIds = array_values(array_filter(array_map('intval', explode(',', $validated['selectedCategoryIds']))));

            $validNewIds = Category::where('site_id', $site->id)
                ->whereIn('id', $newIds)
                ->pluck('id')
                ->toArray();

            if (count($validNewIds) !== count($newIds)) {
                throw new \Exception('Some selected categories do not belong to this site.');
            }

            $article->categories()->sync($validNewIds);

            $added = array_values(array_diff($validNewIds, $oldIds));
            if (!empty($added)) {
                $this->applyCategoryArticleTemplates($article, $added);
            }
        }

        // Handle input instances if provided (unified save)
        if (isset($validated['inputs']) && is_array($validated['inputs'])) {
            $this->updateInputInstances($article, $validated['inputs']);
        }

        DB::commit();

        return back()->with('success', 'Article updated successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return back()
            ->withErrors('An error occurred while updating the article. ' . $e->getMessage())
            ->withInput();
    }
}


    public function duplicate(Site $site, Article $article)
    {
        if ($article->site_id !== $site->id) abort(404);

        DB::beginTransaction();

        try {
            $article->load('categories');

            // 1) duplicate the article row
            $new = $article->replicate();
            $new->title = $article->title . ' (Copy)';
            $new->created_by = auth()->id();
            $new->updated_by = auth()->id();
            $new->save();

            // 2) copy categories pivot (same site so no mapping needed)
            $new->categories()->sync($article->categories->pluck('id')->toArray());

            // 3) duplicate input instances (+ galleries/files)
            $this->duplicateOwnerInputInstances(
                Article::class,
                $article->id,
                Article::class,
                $new->id
            );

            DB::commit();

            return redirect()
                ->route('admin.articles.edit', [$site->id, $new->id])
                ->with('success', 'Article duplicated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Duplicate failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Update input instances for an article (used by unified save).
     */
    protected function updateInputInstances(Article $article, array $inputs): void
    {
        $morphType = Article::class;
        $ownerId = $article->id;

        foreach ($inputs as $instanceId => $payload) {
            $instance = InputInstance::query()
                ->where('id', $instanceId)
                ->where('owner_type', $morphType)
                ->where('owner_id', $ownerId)
                ->first();

            if (!$instance) {
                continue;
            }

            // Locked defaults should not be editable
            if ($instance->is_default && $instance->is_locked) {
                continue;
            }

            $value = $payload['value'] ?? null;

            // Normalize arrays into JSON
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $instance->value = $value;
            $instance->save();
        }
    }

    /**
     * Same helper as in SiteController. You can copy-paste,
     * or extract it to a trait later.
     */
    protected function duplicateOwnerInputInstances(string $fromOwnerType, int $fromOwnerId, string $toOwnerType, int $toOwnerId): void
    {
        $instances = InputInstance::where('owner_type', $fromOwnerType)
            ->where('owner_id', $fromOwnerId)
            ->with(['galleryMedia', 'files'])
            ->orderBy('sort_order')
            ->get();

        foreach ($instances as $old) {
            $new = $old->replicate();
            $new->owner_type = $toOwnerType;
            $new->owner_id   = $toOwnerId;
            $new->created_by = auth()->id();
            $new->updated_by = auth()->id();

            $oldGalleryId = $old->gallery_id;
            $new->gallery_id = null;

            $new->save();

            if ($oldGalleryId) {
                $newGallery = new Gallery();
                $newGallery->forceFill([
                    'owner_type' => $toOwnerType,
                    'owner_id'   => $toOwnerId,
                    'name'       => $new->label ?: 'Gallery',
                    'variable'   => $new->variable,
                    'sort_order' => $new->sort_order,
                ])->save();

                $new->forceFill(['gallery_id' => $newGallery->id])->save();

                if (method_exists($old, 'galleryMedia') && $old->galleryMedia) {
                    $mediaIds = $old->galleryMedia->pluck('id')->toArray();
                    if (!empty($mediaIds) && method_exists($newGallery, 'media')) {
                        $newGallery->media()->sync($mediaIds);
                    }
                }
            }

            if (method_exists($old, 'files') && $old->files) {
                $fileIds = $old->files->pluck('id')->toArray();
                if (!empty($fileIds)) {
                    $new->files()->sync($fileIds);
                }
            }
        }
    }



    public function delete(Site $site, Article $article)
    {
        if ($article->site_id !== $site->id) abort(404);

        DB::beginTransaction();

        try {
            $article->delete();
            DB::commit();
            return back()->with('success', 'Article deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error deleting article: ' . $e->getMessage()]);
        }
    }

    public function restore(Site $site, $articleId)
    {
        $article = Article::withTrashed()
            ->where('site_id', $site->id)
            ->findOrFail($articleId);

        $article->restore();
        return back()->with('success', 'Article restored.');
    }

    public function forceDelete(Site $site, $articleId)
    {
        $article = Article::withTrashed()
            ->where('site_id', $site->id)
            ->findOrFail($articleId);

        $article->forceDelete();
        return back()->with('success', 'Article permanently deleted.');
    }


    // =========================
    // Template application
    // =========================

    protected function applyGlobalArticleTemplate(Article $article): void
    {
        $tpl = InputTemplate::where('applies_to', 'article')
            ->whereNull('scope_type')
            ->whereNull('scope_id')
            ->first();

        if (!$tpl) return;

        $items = InputTemplateItem::with('field')
            ->where('input_template_id', $tpl->id)
            ->orderBy('sort_order')
            ->get();

        $this->applyTemplateItemsToArticle($article, $items);
    }

    protected function applyCategoryArticleTemplates(Article $article, array $categoryIds): void
    {
        // Get all scoped templates for those categories
        $templates = InputTemplate::where('applies_to', 'article')
            ->where('scope_type', Category::class)
            ->whereIn('scope_id', $categoryIds)
            ->get()
            ->keyBy('scope_id');

        foreach ($categoryIds as $catId) {
            $tpl = $templates->get($catId);
            if (!$tpl) continue;

            $items = InputTemplateItem::with('field')
                ->where('input_template_id', $tpl->id)
                ->orderBy('sort_order')
                ->get();

            $this->applyTemplateItemsToArticle($article, $items);
        }
    }

    protected function applyTemplateItemsToArticle(Article $article, $items): void
    {
        $ownerType = \App\Models\Article::class;
        $ownerId   = $article->id;

        foreach ($items as $item) {
            if (!$item->variable) continue;

            // Do not override if exists (important for multi-category)
            $exists = InputInstance::where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('variable', $item->variable)
                ->exists();

            if ($exists) continue;

            $maxOrder = (int) (InputInstance::where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->max('sort_order') ?? 0);

            $instance = new InputInstance();
            $instance->forceFill([
                'owner_type'     => $ownerType,
                'owner_id'       => $ownerId,
                'input_field_id' => $item->input_field_id,
                'label'          => $item->label,
                'variable'       => $item->variable,
                'value'          => $item->default_value,
                'description'    => $item->description,
                'sort_order'     => $maxOrder + 1,
                'is_default'     => true,
                'is_locked'      => (bool) $item->is_locked,
                'created_by'     => auth()->id(),
            ])->save();

            // Auto-create gallery if needed
            $fieldType = $item->field?->field_type;
            if ($fieldType === 'gallery') {
                $gallery = new Gallery();
                $gallery->forceFill([
                    'owner_type' => $ownerType,
                    'owner_id'   => $ownerId,
                    'name'       => $instance->label ?: 'Gallery',
                    'variable'   => $instance->variable,
                    'sort_order' => $instance->sort_order,
                ])->save();

                $instance->forceFill(['gallery_id' => $gallery->id])->save();
            }
        }
    }

    public function reorder(Request $request, Site $site)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) $ids = [];

        $ids = array_values(array_filter(array_map('intval', $ids)));

        // Only allow ids that belong to this site
        $valid = Article::where('site_id', $site->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        if (count($valid) !== count($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid article list.'], 422);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                Article::where('id', $id)->update(['sort_order' => $i + 1]);
            }
        });

        return response()->json(['success' => true]);
    }

}
