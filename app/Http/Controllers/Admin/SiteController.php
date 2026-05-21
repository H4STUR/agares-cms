<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Site;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\InputField;
use App\Models\InputTemplate;
use App\Models\InputInstance;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Article;
use App\Models\Gallery;
use Illuminate\Support\Facades\Schema;
use App\Models\RoleSitePermission;
use Spatie\Permission\Models\Role;


class SiteController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'published'); // published|draft|all|trash
        $q   = trim((string) $request->get('q', ''));

        $base = Site::query();

        // Apply tab filter
        if ($tab === 'published') {
            $base->whereNull('deleted_at')->where('status', Site::STATUS_PUBLISHED);
        } elseif ($tab === 'draft') {
            $base->whereNull('deleted_at')->where('status', Site::STATUS_DRAFT);
        } elseif ($tab === 'trash') {
            $base->onlyTrashed();
        } else { // all
            $base->withTrashed();
        }

        // Apply search
        $this->applySiteSearch($base, $q);

        // List
        $sites = $base
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        // Counts (respect search too, so badges show “matches”)
        $counts = $this->siteTabCounts($q);

        $shopUrl = EcommerceSetting::where('key', 'shop_url')->value('value');

        return view('pages.admin.sites.index', compact('sites', 'tab', 'counts', 'q', 'shopUrl'));
    }

    /**
     * Searches by: id (exact if numeric) + partial matches in name/slug/title/description/keywords/template.
     */
    private function applySiteSearch(\Illuminate\Database\Eloquent\Builder $query, string $q): void
    {
        if ($q === '') return;

        $query->where(function ($qq) use ($q) {
            // if numeric -> also search exact id
            if (ctype_digit($q)) {
                $qq->orWhere('id', (int) $q);
            }

            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q).'%';

            $qq->orWhere('name', 'like', $like)
            ->orWhere('slug', 'like', $like)
            ->orWhere('title', 'like', $like)
            ->orWhere('description', 'like', $like)
            ->orWhere('keywords', 'like', $like)
            ->orWhere('template', 'like', $like);
        });
    }

    private function siteTabCounts(string $q): array
    {
        $make = function (\Illuminate\Database\Eloquent\Builder $builder) use ($q) {
            $this->applySiteSearch($builder, $q);
            return $builder->count();
        };

        return [
            'published' => $make(Site::query()->whereNull('deleted_at')->where('status', Site::STATUS_PUBLISHED)),
            'draft'     => $make(Site::query()->whereNull('deleted_at')->where('status', Site::STATUS_DRAFT)),
            'all'       => $make(Site::query()->withTrashed()),
            'trash'     => $make(Site::query()->onlyTrashed()),
        ];
    }

    public function create()
    {
        $menus = \App\Models\Menu::orderBy('name')->get();
        $siteTemplates = \App\Models\InputTemplate::where('applies_to', 'site')->orderBy('name')->get();

        return view('pages.admin.sites.create', compact('menus', 'siteTemplates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:sites,slug',
            'description' => 'nullable|string',

            // you currently have these fields in the form, but your Site model doesn't have them in $fillable.
            // Keep them validated if you want, but DO NOT pass to Site::create unless you add columns+fillable.
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',

            'menu_id' => 'required|exists:menus,id',
            'parent_id' => 'nullable|exists:sites,id',

            // template chosen in create
            'site_template_id' => 'nullable|exists:input_templates,id',

            'publish_immediately' => 'nullable|in:on,1,true,0,false',

            'action' => 'nullable|in:create,create_and_edit',
        ]);

        DB::beginTransaction();

        try {

            $publishNow = $request->boolean('publish_immediately');

            $status = $publishNow ? Site::STATUS_PUBLISHED : Site::STATUS_DRAFT;
            $publishedAt = $publishNow ? now() : null;

            $site = Site::create([
                'name'        => $validated['name'],
                'slug'        => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'template'    => 'index',
                'parent_id'   => $validated['parent_id'] ?? null,
                'status'       => $status,
                'published_at' => $publishedAt,
                'created_by'  => auth()->id(),
                'updated_by'  => auth()->id(),
            ]);

            // Attach to menu with next menu_order
            $menuId = (int) $validated['menu_id'];

            $maxOrder = DB::table('menu_site')
                ->where('menu_id', $menuId)
                ->max('menu_order');

            $site->menus()->attach($menuId, [
                'menu_order' => ((int) ($maxOrder ?? 0)) + 1,
            ]);

            // Apply default inputs template (same idea as articles)
            $tplId = $validated['site_template_id'] ?? null;

            if (!empty($tplId)) {
                $tpl = InputTemplate::with('items')->findOrFail($tplId);

                if ($tpl->applies_to !== 'site') {
                    throw new \Exception('Selected template does not apply to sites.');
                }

                foreach ($tpl->items as $item) {
                    $site->inputInstances()->create([
                        'input_field_id' => $item->input_field_id,
                        'label'          => $item->label,
                        'variable'       => $item->variable,
                        'value'          => $item->default_value,
                        'description'    => $item->description,
                        'sort_order'     => $item->sort_order ?? 0,
                        'is_default'     => true,
                        'is_locked'      => (bool) ($item->is_locked ?? false),
                        'created_by'     => auth()->id(),

                        // set only if you actually have this column + want it:
                        // 'updated_by'  => auth()->id(),
                    ]);
                }
            }
            
            //add default site permissions
            $this->seedRoleSitePermissionsForNewSite($site);

            DB::commit();

            $action = $validated['action'] ?? 'create';

            if ($action === 'create_and_edit') {
                return redirect()
                    ->route('admin.sites.edit', $site->id)
                    ->with('success', 'Site created successfully.');
            }

            return redirect()
                ->route('admin.sites', $site->id)
                ->with('success', 'Site created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withErrors(['create_site' => $e->getMessage()])
                ->withInput();
        }
    }

    public function duplicate(Site $site)
    {
        DB::beginTransaction();

        try {
            // Load everything we need
            $site->load([
                'menus', // many-to-many
                'categories.articles.categories', // categories -> articles -> categories pivot
                'inputInstances.galleryMedia', // if you have this relation on Site
            ]);

            // 1) Duplicate Site row
            $newSite = $site->replicate();
            $newSite->name = $site->name . ' (Copy)';
            $newSite->slug = $this->makeUniqueSlug(Site::class, $site->slug);
            $newSite->created_by = auth()->id();
            $newSite->updated_by = auth()->id();
            $newSite->push();

            // 2) Duplicate menu pivot attachments (keep same menus)
            // If you have pivot columns (like menu_order), keep them too.
            if ($site->menus && $site->menus->count()) {
                foreach ($site->menus as $menu) {
                    $pivot = $menu->pivot ? $menu->pivot->toArray() : [];
                    unset($pivot['site_id'], $pivot['menu_id'], $pivot['created_at'], $pivot['updated_at']);

                    // If there is menu_order and you want it appended at end, do this:
                    // $pivot['menu_order'] = ($menu->sites()->max('menu_order') ?? 0) + 1;

                    $newSite->menus()->attach($menu->id, $pivot);
                }
            }

            // 3) Duplicate Site input instances (+ galleries/files)
            $this->duplicateOwnerInputInstances(
                Site::class,
                $site->id,
                Site::class,
                $newSite->id
            );

            // 4) Duplicate Categories + Category input instances
            $categoryIdMap = []; // oldCatId => newCatId

            $oldCategories = Category::where('site_id', $site->id)->get();
            foreach ($oldCategories as $oldCat) {
                $newCat = $oldCat->replicate();
                $newCat->site_id = $newSite->id;
                $newCat->created_by = auth()->id();
                $newCat->updated_by = auth()->id();
                $newCat->save();

                $categoryIdMap[$oldCat->id] = $newCat->id;

                // Duplicate category input instances (+ galleries/files)
                $this->duplicateOwnerInputInstances(
                    Category::class,
                    $oldCat->id,
                    Category::class,
                    $newCat->id
                );
            }

            // 5) Duplicate Articles + article inputs + categories pivot (mapped)
            $oldArticles = Article::where('site_id', $site->id)
                ->with('categories')
                ->get();

            foreach ($oldArticles as $oldArticle) {
                $newArticle = $oldArticle->replicate();
                $newArticle->site_id = $newSite->id;
                $newArticle->title = $oldArticle->title . ' (Copy)';
                $newArticle->created_by = auth()->id();
                $newArticle->updated_by = auth()->id();
                $newArticle->save();

                // sync categories (mapped to new site categories)
                $mappedCatIds = $oldArticle->categories
                    ->pluck('id')
                    ->map(fn($oldId) => $categoryIdMap[$oldId] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                if (!empty($mappedCatIds)) {
                    $newArticle->categories()->sync($mappedCatIds);
                }

                // Duplicate article input instances (+ galleries/files)
                $this->duplicateOwnerInputInstances(
                    Article::class,
                    $oldArticle->id,
                    Article::class,
                    $newArticle->id
                );
            }

            // Copy role_site_permissions from old site to new site
            $oldPerms = \App\Models\RoleSitePermission::where('site_id', $site->id)->get();
            foreach ($oldPerms as $p) {
                \App\Models\RoleSitePermission::updateOrCreate(
                    ['role_id' => $p->role_id, 'site_id' => $newSite->id],
                    [
                        'can_view'       => (bool) $p->can_view,
                        'can_edit'       => (bool) $p->can_edit,
                        'can_categories' => (bool) $p->can_categories,
                        'can_articles'   => (bool) $p->can_articles,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.sites.edit', $newSite->id)
                ->with('success', 'Site duplicated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Duplicate failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate InputInstance rows for an owner, including gallery/files
     * (re-uses existing media; does NOT duplicate physical files).
     */
    protected function duplicateOwnerInputInstances(string $fromOwnerType, int $fromOwnerId, string $toOwnerType, int $toOwnerId): void
    {
        // Do NOT eager load galleryMedia/files because those relations may not exist
        $instances = InputInstance::where('owner_type', $fromOwnerType)
            ->where('owner_id', $fromOwnerId)
            ->orderBy('sort_order')
            ->get();

        foreach ($instances as $old) {
            $new = $old->replicate();
            $new->owner_type = $toOwnerType;
            $new->owner_id   = $toOwnerId;
            $new->created_by = auth()->id();
            $new->updated_by = auth()->id();

            // if the old instance had a gallery, we must not keep old gallery_id
            $oldGalleryId = $old->gallery_id ?? null;
            $new->gallery_id = null;

            $new->save();

            /**
             * 1) Duplicate gallery (create a new gallery row for the new owner)
             *    and copy media links from the old gallery pivot table.
             */
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

                // copy gallery media pivot
                $mediaIds = $this->getGalleryMediaIds($oldGalleryId);

                if (!empty($mediaIds)) {
                    $this->attachMediaToGallery($newGallery->id, $mediaIds);
                }
            }

            /**
             * 2) Duplicate file attachments (InputInstance <-> Media pivot)
             */
            $fileIds = $this->getInputInstanceFileIds($old->id);

            if (!empty($fileIds)) {
                $this->attachFilesToInputInstance($new->id, $fileIds);
            }
        }
    }

    protected function getGalleryMediaIds(int $galleryId): array
    {
        // Most common: gallery_media (gallery_id, media_id, sort_order?)
        if (Schema::hasTable('gallery_media')) {
            return DB::table('gallery_media')
                ->where('gallery_id', $galleryId)
                ->orderBy('sort_order')
                ->pluck('media_id')
                ->toArray();
        }

        // Alternative naming
        if (Schema::hasTable('gallery_media_items')) {
            return DB::table('gallery_media_items')
                ->where('gallery_id', $galleryId)
                ->orderBy('sort_order')
                ->pluck('media_id')
                ->toArray();
        }

        // If you have a different name, add it here.
        return [];
    }

    protected function attachMediaToGallery(int $newGalleryId, array $mediaIds): void
    {
        // Must match the same pivot table you used above.
        if (Schema::hasTable('gallery_media')) {
            // If you have unique constraint, avoid duplicates
            $rows = [];
            $order = 1;

            foreach ($mediaIds as $mid) {
                $rows[] = [
                    'gallery_id' => $newGalleryId,
                    'media_id'   => (int) $mid,
                    'sort_order' => $order++,
                ];
            }

            DB::table('gallery_media')->insert($rows);
            return;
        }

        if (Schema::hasTable('gallery_media_items')) {
            $rows = [];
            $order = 1;

            foreach ($mediaIds as $mid) {
                $rows[] = [
                    'gallery_id' => $newGalleryId,
                    'media_id'   => (int) $mid,
                    'sort_order' => $order++,
                ];
            }

            DB::table('gallery_media_items')->insert($rows);
            return;
        }
    }

    protected function getInputInstanceFileIds(int $inputInstanceId): array
    {
        // Common pivot: input_instance_media (input_instance_id, media_id, sort_order?)
        if (Schema::hasTable('input_instance_media')) {
            return DB::table('input_instance_media')
                ->where('input_instance_id', $inputInstanceId)
                ->orderBy('sort_order')
                ->pluck('media_id')
                ->toArray();
        }

        // Alternative pivot naming
        if (Schema::hasTable('input_instance_files')) {
            return DB::table('input_instance_files')
                ->where('input_instance_id', $inputInstanceId)
                ->orderBy('sort_order')
                ->pluck('media_id')
                ->toArray();
        }

        return [];
    }

    protected function attachFilesToInputInstance(int $newInputInstanceId, array $fileIds): void
    {
        if (Schema::hasTable('input_instance_media')) {
            $rows = [];
            $order = 1;

            foreach ($fileIds as $mid) {
                $rows[] = [
                    'input_instance_id' => $newInputInstanceId,
                    'media_id'          => (int) $mid,
                    'sort_order'        => $order++,
                ];
            }

            DB::table('input_instance_media')->insert($rows);
            return;
        }

        if (Schema::hasTable('input_instance_files')) {
            $rows = [];
            $order = 1;

            foreach ($fileIds as $mid) {
                $rows[] = [
                    'input_instance_id' => $newInputInstanceId,
                    'media_id'          => (int) $mid,
                    'sort_order'        => $order++,
                ];
            }

            DB::table('input_instance_files')->insert($rows);
            return;
        }
    }


    protected function makeUniqueSlug(string $modelClass, string $slug): string
    {
        $base = Str::slug($slug);
        $candidate = $base . '-copy';

        $i = 2;
        while ($modelClass::where('slug', $candidate)->exists()) {
            $candidate = $base . '-copy-' . $i;
            $i++;
        }

        return $candidate;
    }



    public function getSitesByMenu($menuId)
    {
        $menu = Menu::with('sites')->findOrFail($menuId);
        return response()->json($menu->sites);
    }

    public function show(Site $site)
    {
        $site->load([
            'createdBy',
            'categories',
            
            'articles' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },

            'inputInstances.field',
            'inputInstances.files',

            'inputInstances.gallery.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },

            'galleries.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },
        ]);

        $data = [
            'site' => $site,
        ];

        return view('pages.admin.sites.info', compact('data'));
    }


    public function edit(Site $site)
    {
        $site->load([
            'inputInstances.field',
            'inputInstances.files',

            // IMPORTANT: order gallery media by pivot sort_order
            'inputInstances.gallery.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },

            // if you also use $site->galleries anywhere, keep this ordered too
            'galleries.media' => function ($q) {
                $q->orderBy('gallery_media.sort_order');
            },

            'categories',
            'articles',
            'menus',
        ]);

        // Make Blade-friendly property used by your edit page:
        $site->inputInstances->each(function ($input) {
            $input->galleryMedia = $input->gallery?->media ?? collect();
        });

        $inputTypes = InputField::select('id', 'name', 'field_type')
            ->orderBy('field_type')
            ->get();

        // Input templates (your existing “apply template” feature)
        $inputTemplates = \App\Models\InputTemplate::where('applies_to', 'site')
            ->orderBy('name')
            ->get();

        // Frontend page templates (blade files in resources/views/pages/frontend/sites)
        $frontendTemplates = collect(File::files(resource_path('views/pages/frontend/sites')))
            ->filter(fn($f) => $f->getExtension() === 'php' && str_ends_with($f->getFilename(), '.blade.php'))
            ->map(fn($f) => str_replace('.blade.php', '', $f->getFilename()))
            ->values();

        $mediaFiles = Media::orderByDesc('id')->get();

        $data = [
            'site' => $site,
            'inputTypes' => $inputTypes,
            'inputs' => $site->inputInstances,  // has ->galleryMedia + ->files loaded
            'inputTemplates' => $inputTemplates,      // <-- rename
            'frontendTemplates' => $frontendTemplates, // <-- new
            'galleries' => $site->galleries,
            'mediaFiles' => $mediaFiles,
        ];

        // NOTE: your blade uses $menus (not $data['menus']), so keep this:
        $menus = \App\Models\Menu::with(['sites' => function ($q) {
            $q->orderBy('menu_site.menu_order');
            }])
            ->orderBy('name')
            ->get();


        return view('pages.admin.sites.edit', compact('data', 'menus'));
    }




    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:sites,slug,' . $site->id,
            ],

            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'keywords' => 'sometimes|nullable|string',

            'template' => 'sometimes|nullable|string|max:255',
            'privileges' => 'sometimes|nullable|json',
            'menu_order' => 'sometimes|nullable|integer',
            'menu_id' => 'sometimes|required|exists:menus,id',
            'parent_id' => [
                'sometimes',
                'nullable',
                'exists:sites,id',

                function ($attribute, $value, $fail) use ($site) {
                    if ((int)$value === (int)$site->id) {
                        $fail('The parent site cannot be the site itself.');
                    }
                },
            ],
            'status' => 'sometimes|required|in:draft,published,scheduled,private',
            'published_at' => 'sometimes|nullable|date',

            // Input instances (for unified save across all tabs)
            'inputs' => 'sometimes|array',
            'inputs.*.value' => 'nullable',

            // Redirect / forward page
            'is_redirect' => 'sometimes|boolean',
            'redirect_url' => 'sometimes|nullable|string|max:2048',
            'redirect_type' => 'sometimes|nullable|in:301,302',
            'redirect_new_tab' => 'sometimes|boolean',
        ]);


        DB::beginTransaction();

        try {
            // normalize status timestamps
            if (isset($validated['status'])) {
                $status = $validated['status'];

                if ($status === Site::STATUS_PUBLISHED) {
                    $validated['published_at'] = now();
                }

                if ($status === Site::STATUS_DRAFT || $status === Site::STATUS_PRIVATE) {
                    $validated['published_at'] = null;
                }

                if ($status === Site::STATUS_SCHEDULED) {
                    // require a date if scheduled
                    if (empty($validated['published_at'])) {
                        return back()->withErrors(['published_at' => 'Publish date is required for scheduled pages.']);
                    }
                }
            }

            $validated['updated_by'] = auth()->id();

            if (array_key_exists('menu_id', $validated)) {
                $newMenuId = (int) $validated['menu_id'];
                unset($validated['menu_id']); // not a sites column

                // current (first) menu - assuming 1 menu per site in UI
                $currentMenuId = (int) ($site->menus()->pluck('menus.id')->first() ?? 0);

                if ($currentMenuId !== $newMenuId) {
                    // attach to new menu at the end
                    $maxOrder = (int) (DB::table('menu_site')
                        ->where('menu_id', $newMenuId)
                        ->max('menu_order') ?? 0);

                    $site->menus()->sync([
                        $newMenuId => ['menu_order' => $maxOrder + 1],
                    ]);

                    // parent might not belong to new menu tree
                    $site->parent_id = null;
                    $site->save();
                }
            }

            // Handle input instances if provided (unified save)
            if (isset($validated['inputs']) && is_array($validated['inputs'])) {
                $this->updateInputInstances($site, $validated['inputs']);
                unset($validated['inputs']);
            }

            // Redirect normalization / safety
            $isRedirect = $request->boolean('is_redirect');

            // If checkbox is OFF, wipe redirect fields
            if (!$isRedirect) {
                $validated['is_redirect'] = false;
                $validated['redirect_url'] = null;
                $validated['redirect_type'] = 302;
                $validated['redirect_new_tab'] = false;
            } else {
                $validated['is_redirect'] = true;

                $raw = trim((string)($validated['redirect_url'] ?? ''));

                if ($raw === '') {
                    return back()->withErrors(['redirect_url' => 'Redirect URL is required when forward is enabled.'])->withInput();
                }

                // Block dangerous schemes
                $lower = strtolower($raw);
                if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:') || str_starts_with($lower, 'file:')) {
                    return back()->withErrors(['redirect_url' => 'This redirect URL scheme is not allowed.'])->withInput();
                }

                // Allow internal paths: "kontakt" => "/kontakt"
                if (!preg_match('#^https?://#i', $raw)) {
                    $raw = '/' . ltrim($raw, '/');
                }

                // Prevent redirecting to itself (simple guard)
                $selfPath = '/' . ltrim($site->slug, '/');
                if ($raw === $selfPath || $raw === url($selfPath)) {
                    return back()->withErrors(['redirect_url' => 'Redirect URL cannot point to the same page.'])->withInput();
                }

                $validated['redirect_url'] = $raw;

                $code = (int)($validated['redirect_type'] ?? 302);
                $validated['redirect_type'] = in_array($code, [301, 302], true) ? $code : 302;

                $validated['redirect_new_tab'] = $request->boolean('redirect_new_tab');
            }

            // update site columns
            if (!empty($validated)) {
                $site->update($validated);
            }

            DB::commit();

            return back()->with('success', 'Site updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('An error occurred while saving the site. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update input instances for a site (used by unified save).
     */
    protected function updateInputInstances(Site $site, array $inputs): void
    {
        $morphType = Site::class;
        $ownerId = $site->id;

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

    public function restore($siteId)
    {
        $site = Site::withTrashed()->findOrFail($siteId);
        $site->restore();

        return back()->with('success', 'Site restored.');
    }

    public function forceDelete($siteId)
    {
        $site = Site::withTrashed()->findOrFail($siteId);
        $site->forceDelete();

        return back()->with('success', 'Site permanently deleted.');
    }


    public function delete(Site $site)
    {
        try {
            $site->delete();
            return redirect()->route('admin.sites')->with('success', 'Site deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while deleting the site. ' . $e->getMessage());
        }
    }

    public function moveUp(Request $request, Menu $menu, Site $site)
    {
        return $this->moveInMenu($request, $menu, $site, -1);
    }

    public function moveDown(Request $request, Menu $menu, Site $site)
    {
        return $this->moveInMenu($request, $menu, $site, +1);
    }

    private function moveInMenu(Request $request, Menu $menu, Site $site, int $direction)
{
    // IMPORTANT: roots are NULL in DB, but UI sends 0 => normalize:
    $parentId = $request->input('parent_id', null);
    $parentId = is_null($parentId) || $parentId === '' ? null : (int)$parentId;
    if ($parentId === 0) $parentId = null;

    // Safety: site must be attached to that menu
    $exists = DB::table('menu_site')
        ->where('menu_id', $menu->id)
        ->where('site_id', $site->id)
        ->exists();

    if (!$exists) {
        return back()->withErrors(['menu_order' => 'This site is not assigned to this menu.']);
    }

    DB::beginTransaction();

    try {
        // Get siblings in SAME menu and SAME parent_id (NULL-safe)
        $siblings = DB::table('menu_site')
            ->join('sites', 'sites.id', '=', 'menu_site.site_id')
            ->where('menu_site.menu_id', $menu->id)
            ->when($parentId === null, function ($q) {
                $q->whereNull('sites.parent_id');
            }, function ($q) use ($parentId) {
                $q->where('sites.parent_id', $parentId);
            })
            ->orderBy('menu_site.menu_order')
            ->orderBy('menu_site.site_id') // deterministic tiebreaker
            ->select('menu_site.site_id', 'menu_site.menu_order')
            ->get()
            ->values();

        $currentIndex = $siblings->search(fn($row) => (int)$row->site_id === (int)$site->id);

        if ($currentIndex === false) {
            DB::rollBack();
            return back()->withErrors(['menu_order' => 'Site not found in this sibling group.']);
        }

        $swapIndex = $currentIndex + $direction;

        if ($swapIndex < 0 || $swapIndex >= $siblings->count()) {
            DB::rollBack();
            return back();
        }

        $current = $siblings[$currentIndex];
        $swap    = $siblings[$swapIndex];

        // If menu_order duplicates exist, swapping equal values changes nothing.
        // Force a swap that changes values even when duplicates exist:
        $currentOrder = (int)$current->menu_order;
        $swapOrder    = (int)$swap->menu_order;

        if ($currentOrder === $swapOrder) {
            // bump one temporarily
            DB::table('menu_site')
                ->where('menu_id', $menu->id)
                ->where('site_id', $current->site_id)
                ->update(['menu_order' => $currentOrder + 1]);

            $currentOrder = $currentOrder + 1;
        }

        DB::table('menu_site')
            ->where('menu_id', $menu->id)
            ->where('site_id', $current->site_id)
            ->update(['menu_order' => $swapOrder]);

        DB::table('menu_site')
            ->where('menu_id', $menu->id)
            ->where('site_id', $swap->site_id)
            ->update(['menu_order' => $currentOrder]);

        DB::commit();

        return back()->with('success', 'Order updated.');
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);
        return back()->withErrors(['menu_order' => 'Failed to update order: ' . $e->getMessage()]);
    }
}

protected function seedRoleSitePermissionsForNewSite(\App\Models\Site $site): void
{
    // These are the global permissions that decide defaults per-site
    $map = [
        'can_view'       => ['name' => 'view sites',        'category' => 'cms'],
        'can_edit'       => ['name' => 'manage sites',      'category' => 'cms'],
        'can_categories' => ['name' => 'manage categories', 'category' => 'content'],
        'can_articles'   => ['name' => 'manage articles',   'category' => 'content'],
    ];

    // Load roles + their permissions once
    $roles = Role::query()
        ->with(['permissions' => function ($q) {
            // if you added "category" column to permissions table, keep it selected
            $q->select('id', 'name', 'category');
        }])
        ->get();

    foreach ($roles as $role) {
        // If you have a true "owner" role that should ALWAYS have full access:
        $isOwner = ($role->name === 'owner');

        $values = [
            'can_view'       => $isOwner ? true : false,
            'can_edit'       => $isOwner ? true : false,
            'can_categories' => $isOwner ? true : false,
            'can_articles'   => $isOwner ? true : false,
        ];

        if (!$isOwner) {
            foreach ($map as $column => $perm) {
                $has = $role->permissions->contains(function ($p) use ($perm) {
                    // category check is optional, but you asked for it:
                    return $p->name === $perm['name']
                        && (property_exists($p, 'category') ? $p->category === $perm['category'] : true);
                });

                $values[$column] = (bool) $has;
            }
        }

        // Only create rows for roles that have at least one permission enabled
        // (owner always gets a row).
        $any = $isOwner || in_array(true, $values, true);
        if (!$any) {
            continue;
        }

        RoleSitePermission::updateOrCreate(
            [
                'role_id' => $role->id,
                'site_id' => $site->id,
            ],
            $values
        );
    }
}

}
