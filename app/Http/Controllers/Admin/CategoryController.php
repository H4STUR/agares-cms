<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InputTemplate;
use App\Models\Category;
use App\Models\Site;
use App\Models\Media;
use App\Models\InputInstance;
use App\Models\InputField;
use App\Models\InputTemplateItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function create(Site $site)
    {
        return view('pages.admin.categories.create', compact('site'));
    }

    public function store(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $category = $site->categories()->create([
                'name' => $validated['name'],
                'template' => 'index',
                'default_article_template' => 'index',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $headerField = InputField::where('field_type', 'short_text')->first();
            $contentField = InputField::where('field_type', 'text_editor')->first();

            $maxOrder = (int) (InputInstance::where('owner_type', \App\Models\Category::class)
                ->where('owner_id', $category->id)
                ->max('sort_order') ?? 0);

            if ($headerField) {
                $maxOrder++;
                $inst = new InputInstance();
                $inst->forceFill([
                    'owner_type'     => \App\Models\Category::class,
                    'owner_id'       => $category->id,
                    'input_field_id' => $headerField->id,
                    'label'          => 'Header',
                    'variable'       => 'header',
                    'value'          => null,
                    'description'    => null,
                    'sort_order'     => $maxOrder,
                    'is_default'     => true,
                    'is_locked'      => false,
                    'created_by'     => auth()->id(),
                ])->save();
            }

            if ($contentField) {
                $maxOrder++;
                $inst = new InputInstance();
                $inst->forceFill([
                    'owner_type'     => \App\Models\Category::class,
                    'owner_id'       => $category->id,
                    'input_field_id' => $contentField->id,
                    'label'          => 'Content',
                    'variable'       => 'content',
                    'value'          => null,
                    'description'    => null,
                    'sort_order'     => $maxOrder,
                    'is_default'     => true,
                    'is_locked'      => false,
                    'created_by'     => auth()->id(),
                ])->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.categories.edit', [$site->id, $category->id])
                ->with('success', 'Category created and default fields applied.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(Site $site, Category $category)
    {
        $category->load([
            'inputInstances.field',
            'inputInstances.files',

            'inputInstances.gallery.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },
        ]);

        // Blade-friendly property like sites edit uses:
        $category->inputInstances->each(function ($input) {
            $input->galleryMedia = $input->gallery?->media ?? collect();
        });

        $inputTypes = InputField::select('id', 'name', 'field_type')
            ->orderBy('field_type')
            ->get();

        // Category templates (global list, if you still use it anywhere)
        $categoryTemplates = InputTemplate::where('applies_to', 'category')
            ->orderBy('name')
            ->get();


        $categoryArticleTemplate = InputTemplate::firstOrCreate(
            [
                'applies_to' => 'article',
                'scope_type' => Category::class,
                'scope_id'   => $category->id,
            ],
            [
                'name' => "Category {$category->id} Article Template",
            ]
        );


        $categoryArticleTemplateItems = InputTemplateItem::with('field')
            ->where('input_template_id', $categoryArticleTemplate->id)
            ->orderBy('sort_order')
            ->get();

        // (optional but useful) Global "Default Article Template" items to show reference
        $globalArticleTemplate = InputTemplate::where('applies_to', 'article')
            ->whereNull('scope_type')
            ->whereNull('scope_id')
            ->first();

        $globalArticleTemplateItems = $globalArticleTemplate
            ? InputTemplateItem::with('field')
                ->where('input_template_id', $globalArticleTemplate->id)
                ->orderBy('sort_order')
                ->get()
            : collect();

        $mediaFiles = Media::orderByDesc('id')->get();

        // Frontend templates for categories (resources/views/pages/frontend/categories)
        $frontendCategoryTemplates = collect(File::files(resource_path('views/pages/frontend/categories')))
            ->filter(fn($f) => $f->getExtension() === 'php' && str_ends_with($f->getFilename(), '.blade.php'))
            ->map(fn($f) => str_replace('.blade.php', '', $f->getFilename()))
            ->values();

        // Frontend templates for articles (resources/views/pages/frontend/articles)
        $frontendArticleTemplates = collect(File::files(resource_path('views/pages/frontend/articles')))
            ->filter(fn($f) => $f->getExtension() === 'php' && str_ends_with($f->getFilename(), '.blade.php'))
            ->map(fn($f) => str_replace('.blade.php', '', $f->getFilename()))
            ->values();

        $data = [
            'site'       => $site,
            'category'   => $category,

            // Category inputs (Content tab)
            'inputTypes' => $inputTypes,
            'inputs'     => $category->inputInstances,
            'templates'  => $categoryTemplates,
            'mediaFiles' => $mediaFiles,

            'frontendCategoryTemplates' => $frontendCategoryTemplates,
            'frontendArticleTemplates'  => $frontendArticleTemplates,

            'categoryArticleTemplate'       => $categoryArticleTemplate,
            'categoryArticleTemplateItems'  => $categoryArticleTemplateItems,
            'globalArticleTemplate'         => $globalArticleTemplate,
            'globalArticleTemplateItems'    => $globalArticleTemplateItems,
        ];

        return view('pages.admin.categories.edit', compact('data', 'site', 'category'));
    }


    public function update(Request $request, Site $site, Category $category)
    {
        if ($category->site_id !== $site->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'nullable|string|max:255',
            'default_article_template' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $category->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    public function delete(Site $site, Category $category)
    {
        if ($category->site_id !== $site->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $category->delete();

            DB::commit();

            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error deleting category: ' . $e->getMessage());
        }
    }

    protected function getCategoryArticleTemplate(Category $category): InputTemplate
    {
        return InputTemplate::firstOrCreate([
            'applies_to' => 'article',
            'scope_type' => Category::class,
            'scope_id'   => $category->id,
        ], [
            'name' => "Category {$category->id} Article Template",
        ]);
    }

    public function storeArticleTemplateItem(Request $request, Site $site, Category $category)
    {
        if ($category->site_id !== $site->id) {
            abort(404);
        }

        $validated = $request->validate([
            'input_field_id' => ['required', 'exists:input_fields,id'],
            'label'          => ['nullable', 'string', 'max:255'],
            'variable'       => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'default_value'  => ['nullable'], // string/textarea content
            'is_locked'      => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $tpl = $this->getCategoryArticleTemplate($category);

            $field = InputField::findOrFail($validated['input_field_id']);

            // variable: required for templates
            $variable = trim($validated['variable'] ?? '');
            if ($variable === '') {
                $base = Str::slug($validated['label'] ?? $field->name ?? $field->field_type ?? 'field');
                $variable = $base !== '' ? $base : ('field_' . $field->id);
            }

            $prefix = 'cat_' . $category->id . '_';
            if (!str_starts_with($variable, $prefix)) {
                $variable = $prefix . $variable;
            }

            // ensure unique inside template
            $candidate = $variable;
            $i = 1;
            while (InputTemplateItem::where('input_template_id', $tpl->id)->where('variable', $candidate)->exists()) {
                $i++;
                $candidate = $variable . '_' . $i;
            }
            $variable = $candidate;

            $maxSort = (int) (InputTemplateItem::where('input_template_id', $tpl->id)->max('sort_order') ?? 0);

            $item = InputTemplateItem::create([
                'input_template_id' => $tpl->id,
                'input_field_id'    => $field->id,
                'label'             => $validated['label'] ?? null,
                'variable'          => $variable,
                'default_value'     => $validated['default_value'] ?? null,
                'description'       => $validated['description'] ?? null,
                'sort_order'        => $maxSort + 1,
                'is_locked'         => (bool)($validated['is_locked'] ?? false),
            ]);

            $articles = $category->articles()->get();

            foreach ($articles as $article) {
                // If article already has this variable (maybe from another category), DO NOT overwrite.
                $exists = InputInstance::where('owner_type', \App\Models\Article::class)
                    ->where('owner_id', $article->id)
                    ->where('variable', $item->variable)
                    ->exists();

                if ($exists) continue;

                $maxOrder = (int) (InputInstance::where('owner_type', \App\Models\Article::class)
                    ->where('owner_id', $article->id)
                    ->max('sort_order') ?? 0);

                $inst = new InputInstance();
                $inst->forceFill([
                    'owner_type'     => \App\Models\Article::class,
                    'owner_id'       => $article->id,
                    'input_field_id' => $item->input_field_id,
                    'label'          => $item->label,
                    'variable'       => $item->variable,
                    'value'          => $item->default_value,
                    'description'    => $item->description,
                    'sort_order'     => $maxOrder + 1,
                    'is_default'     => true,
                    'is_locked'      => (bool)$item->is_locked,
                    'created_by'     => auth()->id(),
                ])->save();

                // If it's a gallery field, you likely want to auto-create gallery row here.
                // (You already do that in other places. Tell me if you want it included here too.)
            }

            DB::commit();

            return back()->with('success', 'Article default field added and applied to existing articles in this category.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function deleteArticleTemplateItem(Request $request, Site $site, Category $category, InputTemplateItem $item)
    {
        if ($category->site_id !== $site->id) {
            abort(404);
        }

        $tpl = $this->getCategoryArticleTemplate($category);

        if ((int)$item->input_template_id !== (int)$tpl->id) {
            abort(404);
        }

        $item->delete();

        return back()->with('success', 'Default article field removed from this category template (existing articles unchanged).');
    }

    public function reorderArticleTemplateItems(Request $request, Site $site, Category $category)
    {
        if ($category->site_id !== $site->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $tpl = $this->getCategoryArticleTemplate($category);

        // Only reorder items that belong to this template
        $allowedIds = InputTemplateItem::where('input_template_id', $tpl->id)
            ->whereIn('id', $validated['ids'])
            ->pluck('id')
            ->all();

        DB::beginTransaction();
        try {
            $order = 1;
            foreach ($validated['ids'] as $id) {
                if (!in_array($id, $allowedIds, true)) continue;

                InputTemplateItem::where('id', $id)
                    ->where('input_template_id', $tpl->id)
                    ->update(['sort_order' => $order++]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



}
