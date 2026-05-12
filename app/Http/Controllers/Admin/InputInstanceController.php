<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Category;
use App\Models\Article;
use App\Models\InputField;
use App\Models\InputInstance;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FaqItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;



class InputInstanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        // Coarse route-level gate. Site-scoped check still runs in authorizeInstance().
        return [
            new Middleware('can:manage sites', only: [
                'store', 'delete', 'move', 'bulkUpdate', 'applyDefaults',
                'uploadFiles', 'detachFile', 'reorderFiles',
                'updateValue', 'replaceImage',
                'faqSaveSettings', 'faqItemStore', 'faqItemsBulkUpdate',
                'faqItemMove', 'faqItemDestroy',
            ]),
        ];
    }

    /**
     * Resolve owner model from route param.
     * type: site|category|article
     */
    protected function resolveOwner(string $type, int $id)
    {
        return match ($type) {
            'site' => Site::findOrFail($id),
            'category' => Category::findOrFail($id),
            'article' => Article::findOrFail($id),
            default => abort(404),
        };
    }

    /**
     * Walk up the ownership chain to find the Site this instance belongs to.
     */
    protected function resolveInstanceSite(InputInstance $instance): ?Site
    {
        return match ($instance->owner_type) {
            Site::class     => Site::find($instance->owner_id),
            Category::class => Category::find($instance->owner_id)?->site,
            Article::class  => Article::find($instance->owner_id)?->site,
            default         => null,
        };
    }

    /**
     * Abort 403 unless the authenticated user may edit the site that owns $instance.
     * Super-admins bypass the site-scoped check.
     */
    protected function authorizeInstance(InputInstance $instance): void
    {
        $user = auth()->user();

        // Owner bypasses everything (Gate::before also handles this globally).
        if ($user->hasRole('owner')) {
            return;
        }

        $site = $this->resolveInstanceSite($instance);

        abort_if(! $site || ! $user->canOn('edit', $site), 403);
    }

    /**
     * Create a new input instance for an owner (site/category/article).
     * Expects: input_field_id OR field_type, label, variable, description, value (optional)
     */
    public function store(Request $request, string $type, int $id)
{
    $owner = $this->resolveOwner($type, $id);

    $validated = $request->validate([
        'input_field_id' => 'required|integer|exists:input_fields,id',
        'label'          => 'nullable|string|max:255',
        'variable'       => 'nullable|string|max:255',
        'description'    => 'nullable|string',
        'value'          => 'nullable|string',
        'is_default'     => 'nullable|boolean',
        'is_locked'      => 'nullable|boolean',
    ]);

    $field = InputField::findOrFail($validated['input_field_id']);

    $ownerType = get_class($owner);
    $ownerId   = $owner->id;

    $maxOrder = (int) (InputInstance::where('owner_type', $ownerType)
        ->where('owner_id', $ownerId)
        ->max('sort_order') ?? 0);

    $variable = $validated['variable'] ?? null;

    // auto variable for gallery
    if ($field->field_type === 'gallery' && empty($variable)) {
        $base = \Illuminate\Support\Str::slug($validated['label'] ?? 'gallery');
        $base = $base !== '' ? $base : 'gallery';

        $candidate = $base;
        $i = 1;

        while (InputInstance::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('variable', $candidate)
            ->exists()) {
            $i++;
            $candidate = $base . '_' . $i;
        }

        $variable = $candidate;
    }

    if (!empty($variable)) {
        $exists = InputInstance::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('variable', $variable)
            ->exists();

        if ($exists) {
            return back()->withErrors(['variable' => "Variable '{$variable}' already exists for this {$type}."])
                         ->withInput();
        }
    }

        DB::beginTransaction();

        try {
            $value = $validated['value'] ?? null;

            // For gallery we keep value null (you already do that)
            if ($field->field_type === 'gallery') {
                $value = null;
            }

            /**
             * contact_form auto-create proper Form + FormFields
             * Store pointer JSON in input_instances.value: {"form_id": X}
             */
            if ($field->field_type === 'contact_form') {

                // auto variable (optional but recommended)
                if (empty($variable)) {
                    $base = 'contact_form';
                    $candidate = $base;
                    $i = 1;

                    while (InputInstance::where('owner_type', $ownerType)
                        ->where('owner_id', $ownerId)
                        ->where('variable', $candidate)
                        ->exists()) {
                        $i++;
                        $candidate = $base . '_' . $i;
                    }

                    $variable = $candidate;
                }

                // Create the Form definition
                $form = Form::create([
                    'name' => $validated['label'] ?: 'Contact form',
                    'type' => 'contact',
                    'settings' => [
                        'mail' => [
                            // store as array internally; UI can show ; separated
                            'recipients' => [],
                            'from_email' => null,
                            'from_name'  => null,

                            // IMPORTANT deliverability:
                            // reply-to should usually be the user's email field
                            'reply_to_field' => 'email',

                            'subject' => 'New contact form message',
                        ],
                        'success_message' => 'Thanks! We will contact you soon.',
                    ],
                ]);

                // Default fields
                $defaults = [
                    ['key' => 'name',    'type' => 'text',     'label' => 'Name',             'required' => true],
                    ['key' => 'phone',   'type' => 'tel',      'label' => 'Phone number',     'required' => false],
                    ['key' => 'email',   'type' => 'email',    'label' => 'Email',            'required' => true],
                    ['key' => 'message', 'type' => 'textarea', 'label' => 'Message',          'required' => true],
                    ['key' => 'consent', 'type' => 'checkbox', 'label' => 'I consent to the processing of my personal data in order to respond to this inquiry, in accordance with the <a href="/privacy-policy" target="_blank">Privacy Policy</a>.', 'required' => true],
                ];

                $order = 10;
                foreach ($defaults as $f) {
                    FormField::create([
                        'form_id'     => $form->id,
                        'key'         => $f['key'],
                        'type'        => $f['type'],
                        'label'       => $f['label'],
                        'required'    => (bool) $f['required'],
                        'placeholder' => null,
                        'options'     => null,
                        'sort_order'  => $order,
                    ]);
                    $order += 10;
                }

                // Pointer stored in InputInstance value
                $value = json_encode(['form_id' => $form->id], JSON_UNESCAPED_UNICODE);
            }

            if ($field->field_type === 'faq') {

                // auto variable (recommended)
                if (empty($variable)) {
                    $base = 'faq';
                    $candidate = $base;
                    $i = 1;

                    while (InputInstance::where('owner_type', $ownerType)
                        ->where('owner_id', $ownerId)
                        ->where('variable', $candidate)
                        ->exists()) {
                        $i++;
                        $candidate = $base . '_' . $i;
                    }

                    $variable = $candidate;
                }

                // store settings JSON in value
                $value = json_encode([
                    'heading' => $validated['label'] ?: 'FAQ',
                ], JSON_UNESCAPED_UNICODE);
            }

            $instance = new InputInstance();
            $instance->forceFill([
                'owner_type'     => $ownerType,
                'owner_id'       => $ownerId,
                'input_field_id' => $field->id,
                'label'          => $validated['label'] ?? null,
                'variable'       => $variable,
                'value'          => $value,
                'description'    => $validated['description'] ?? null,
                'sort_order'     => $maxOrder + 1,
                'is_default'     => (bool)($validated['is_default'] ?? false),
                'is_locked'      => (bool)($validated['is_locked'] ?? false),
                'created_by'     => auth()->id(),
            ]);
            $instance->save();

            // your existing gallery auto-create remains unchanged


        if ($field->field_type === 'gallery') {
            $gallery = new Gallery();
            $gallery->forceFill([
                'owner_type' => $ownerType,
                'owner_id'   => $ownerId,
                'name'       => $instance->label ?: 'Gallery',
                'variable'   => $instance->variable, // guaranteed
                'sort_order' => $instance->sort_order,
            ]);
            $gallery->save();

            $instance->forceFill(['gallery_id' => $gallery->id])->save();
        }

        if ($field->field_type === 'faq') {
            // seed 1 default item so UI isn't empty
            FaqItem::create([
                'input_instance_id' => $instance->id,
                'question' => 'Example question',
                'answer' => 'Example answer',
                'sort_order' => 10,
                'is_active' => true,
            ]);
        }

        DB::commit();
        return back()->with('success', 'Input added successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return back()->withErrors([
            'error' => 'Failed to add input: ' . $e->getMessage()
        ])->withInput();
    }
}




    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'owner_type' => 'required|string',
            'owner_id'   => 'required|integer',
            'inputs'     => 'required|array',
            'inputs.*.value' => 'nullable', // can be string/array
        ]);

        $ownerType = $validated['owner_type'];
        $ownerId   = (int) $validated['owner_id'];
        $inputs    = $validated['inputs'];

        // If you store morph as full class names, map here.
        // If you store as "site"/"category"/"article", keep as-is.
        $ownerTypeMap = [
            'site'     => \App\Models\Site::class,
            'category' => \App\Models\Category::class,
            'article'  => \App\Models\Article::class,
        ];
        $morphType = $ownerTypeMap[$ownerType] ?? $ownerType;

        DB::transaction(function () use ($inputs, $morphType, $ownerId) {
            foreach ($inputs as $instanceId => $payload) {
                // Only update instances that belong to this owner
                /** @var InputInstance|null $instance */
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

                $instance->loadMissing('field');
                $fieldType = $instance->field?->field_type;

                // These are managed by dedicated endpoints / should never be overwritten here
                if (in_array($fieldType, ['contact_form', 'faq', 'gallery'], true)) {
                    continue;
                }

                $value = $payload['value'] ?? null;

                // Normalize arrays into JSON (useful for checkbox/multi inputs)
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                $instance->value = $value;
                $instance->save();

            }
        });

        return back()->with('success', 'Inputs saved successfully.');
    }

    /**
     * Delete an input instance (site/category/article).
     * If it has gallery_id, deletes gallery too (optional, but usually desired).
     */
    public function delete(string $type, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $instance->loadMissing('field');

        // (optional) basic protection for locked/default
        if ($instance->is_locked) {
            return back()->withErrors(['error' => 'This input is locked and cannot be deleted.']);
        }

        DB::beginTransaction();

        try {
            try {
                if ($instance->field?->field_type === 'contact_form' && is_string($instance->value)) {
                    $arr = json_decode($instance->value, true);
                    $formId = is_array($arr) ? ($arr['form_id'] ?? null) : null;

                    if ($formId) {
                        Form::where('id', (int) $formId)->delete();
                    }
                }
            } catch (\Throwable $e) {
                // Don't block deleting the input if form cleanup fails
                report($e);
            }

            // If instance uses a gallery, delete the gallery row + pivot rows
            if ($instance->gallery_id) {
                $gallery = Gallery::find($instance->gallery_id);
                if ($gallery) {
                    $gallery->media()->detach();
                    $gallery->delete();
                }
            }

            DB::table('input_instance_media')
                ->where('input_instance_id', $instance->id)
                ->delete();

            $instance->delete();

            DB::commit();

            return back()->with('success', 'Input deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete input: ' . $e->getMessage()]);
        }
    }

    /**
     * Move input up/down within same owner by swapping sort_order.
     * direction: up|down
     */
    public function move(Request $request, string $type, int $instanceId)
    {
        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $direction = $validated['direction'];

        $query = InputInstance::where('owner_type', $instance->owner_type)
            ->where('owner_id', $instance->owner_id);

        $swap = $query
            ->where('sort_order', $direction === 'up' ? '<' : '>', $instance->sort_order)
            ->orderBy('sort_order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if (!$swap) {
            return back()->withErrors(['error' => 'Cannot move further.']);
        }

        DB::transaction(function () use ($instance, $swap) {
            $tmp = $instance->sort_order;
            $instance->update(['sort_order' => $swap->sort_order]);
            $swap->update(['sort_order' => $tmp]);

            // keep gallery sort_order in sync if present
            if ($instance->gallery_id) {
                Gallery::where('id', $instance->gallery_id)->update(['sort_order' => $instance->sort_order]);
            }
            if ($swap->gallery_id) {
                Gallery::where('id', $swap->gallery_id)->update(['sort_order' => $swap->sort_order]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * Attach media to gallery (many images).
     * Expects media_ids[].
     */
    public function attachMediaToGallery(Request $request, int $galleryId)
    {
        $validated = $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer|exists:media,id',
        ]);

        $gallery = Gallery::findOrFail($galleryId);

        DB::beginTransaction();

        try {
            $max = DB::table('gallery_media')
                ->where('gallery_id', $gallery->id)
                ->max('sort_order') ?? 0;

            $order = $max;

            foreach ($validated['media_ids'] as $mediaId) {
                $order++;

                // attach ignoring duplicates
                $gallery->media()->syncWithoutDetaching([
                    $mediaId => ['sort_order' => $order]
                ]);
            }

            DB::commit();

            return back()->with('success', 'Images added to gallery.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to attach media: ' . $e->getMessage()]);
        }
    }

    /**
     * Detach media from gallery.
     */
    public function detachMediaFromGallery(int $galleryId, int $mediaId)
    {
        $gallery = Gallery::findOrFail($galleryId);

        DB::beginTransaction();

        try {
            $gallery->media()->detach($mediaId);

            DB::commit();

            return back()->with('success', 'Image removed from gallery.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to detach media: ' . $e->getMessage()]);
        }
    }

    public function applyDefaults(Request $request, string $type, int $id)
    {
        $owner = $this->resolveOwner($type, $id);

        $tpl = \App\Models\InputTemplate::with('items.field')
            ->where('applies_to', $type)
            ->first();

        if (!$tpl) {
            return back()->withErrors(['error' => "Default {$type} InputTemplate not found (applies_to='{$type}')."]);
        }

        DB::beginTransaction();

        try {
            $maxOrder = InputInstance::where('owner_type', get_class($owner))
                ->where('owner_id', $owner->id)
                ->max('sort_order') ?? 0;

            foreach ($tpl->items as $item) {
                // prevent duplicates: per owner + variable + is_default
                $exists = InputInstance::where('owner_type', get_class($owner))
                    ->where('owner_id', $owner->id)
                    ->where('variable', $item->variable)
                    ->where('is_default', true)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $maxOrder++;

                $instance = InputInstance::create([
                    'owner_type'     => get_class($owner),
                    'owner_id'       => $owner->id,
                    'input_field_id' => $item->input_field_id,

                    'label'          => $item->label,
                    'variable'       => $item->variable,
                    'value'          => $item->default_value,
                    'description'    => $item->description,

                    'sort_order'     => $maxOrder,
                    'is_default'     => true,
                    'is_locked'      => (bool) $item->is_locked,

                    'created_by'     => auth()->id(),
                ]);

                // auto-create gallery row if this field is a gallery
                if ($item->field?->field_type === 'gallery') {
                    $gallery = Gallery::create([
                        'owner_type'  => get_class($owner),
                        'owner_id'    => $owner->id,
                        'name'        => $instance->label ?: 'Gallery',
                        'variable'    => $instance->variable,
                        'sort_order'  => $instance->sort_order,
                    ]);

                    $instance->update(['gallery_id' => $gallery->id]);
                }
            }

            DB::commit();

            return back()->with('success', 'Default inputs applied.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to apply defaults: ' . $e->getMessage()]);
        }
    }

    public function updateValue(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $validated = $request->validate([
            'value' => 'nullable|string',
        ]);

        if ($instance->is_default && $instance->is_locked) {
            return response()->json(['success' => false, 'message' => 'Locked'], 403);
        }

        $instance->loadMissing('field');
        $fieldType = $instance->field?->field_type;

        if (in_array($fieldType, ['contact_form', 'faq', 'gallery'], true)) {
            return response()->json(['success' => false, 'message' => 'Managed elsewhere'], 422);
        }

        $instance->value = $validated['value'] ?? null;
        $instance->save();

        return response()->json(['success' => true]);
    }



    public function uploadFiles(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $allowedMimes = 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,mp4,mp3,ogg,wav,weba,webm';

        $validated = $request->validate([
            'files'   => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:102400', "mimes:{$allowedMimes}"],
            'keep_original_name' => ['nullable', 'in:0,1'],
        ]);

        $keep = ($validated['keep_original_name'] ?? '1') === '1';

        DB::beginTransaction();

        try {
            $max = DB::table('input_instance_media')
                ->where('input_instance_id', $instance->id)
                ->max('sort_order');

            $order = (int)($max ?? 0);
            $mediaIds = [];

            $files = $request->file('files', []);
            if (!is_array($files) || count($files) === 0) {
                throw new \RuntimeException('No files received (files[]).');
            }

            $relativeDir = "uploads/files/{$instance->id}";
            $absoluteDir = public_path($relativeDir);

            if (!File::exists($absoluteDir)) {
                File::makeDirectory($absoluteDir, 0755, true);
            }

            foreach ($files as $file) {
                if (!$file || !$file->isValid()) {
                    $msg = $file ? $file->getErrorMessage() : 'No file received.';
                    throw new \RuntimeException('Upload error: ' . $msg);
                }

                // Read meta BEFORE move
                $mime = $file->getClientMimeType();
                $size = $file->getSize();
                $originalName = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();

                // Decide type
                $isImage = is_string($mime) && str_starts_with($mime, 'image/');
                $type = $isImage ? 'image' : 'file';

                // Filename
                if ($keep) {
                    $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                    $base = $base !== '' ? $base : 'file';
                    $fileName = $ext ? ($base . '.' . $ext) : $base;
                } else {
                    $fileName = (string) Str::uuid() . ($ext ? '.' . $ext : '');
                }

                // Avoid collisions when keeping name
                $finalName = $fileName;
                if ($keep) {
                    $i = 1;
                    while (File::exists($absoluteDir . DIRECTORY_SEPARATOR . $finalName)) {
                        $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);
                        $extOnly  = pathinfo($fileName, PATHINFO_EXTENSION);
                        $finalName = $extOnly
                            ? "{$nameOnly}_{$i}.{$extOnly}"
                            : "{$nameOnly}_{$i}";
                        $i++;
                    }
                }

                $file->move($absoluteDir, $finalName);

                // Store relative path for asset()
                $filePath = $relativeDir . '/' . $finalName; // e.g. uploads/files/123/doc.pdf

                $media = Media::create([
                    'file_name'  => $finalName,
                    'file_path'  => $filePath,
                    'mime_type'  => $mime,
                    'size'       => $size,
                    'type'       => $type,
                    'created_by' => auth()->id(),
                ]);

                $mediaIds[] = $media->id;

                $order++;
                DB::table('input_instance_media')->updateOrInsert(
                    ['input_instance_id' => $instance->id, 'media_id' => $media->id],
                    ['sort_order' => $order, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            DB::commit();

            return response()->json(['success' => true, 'media_ids' => $mediaIds]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }


    public function detachFile(int $instanceId, int $mediaId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        DB::table('input_instance_media')
            ->where('input_instance_id', $instance->id)
            ->where('media_id', $mediaId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function reorderFiles(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:media,id',
        ]);

        DB::beginTransaction();
        try {
            $order = 1;
            foreach ($validated['ids'] as $mediaId) {
                DB::table('input_instance_media')
                    ->where('input_instance_id', $instance->id)
                    ->where('media_id', $mediaId)
                    ->update(['sort_order' => $order++]);
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function replaceImage(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->authorizeInstance($instance);

        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'keep_original_name' => ['nullable', 'in:0,1'],
        ]);

        DB::beginTransaction();
        try {
            // 1) Upload image using your existing logic (reuse code, or call a helper)
            // Simplest: mimic your uploadFiles but with one file and type=image

            $file = $request->file('image');
            $keep = ($validated['keep_original_name'] ?? '1') === '1';

            $relativeDir = "uploads/files/{$instance->id}";
            $absoluteDir = public_path($relativeDir);
            if (!File::exists($absoluteDir)) File::makeDirectory($absoluteDir, 0755, true);

            $mime = $file->getClientMimeType();
            $size = $file->getSize();
            $originalName = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();

            if ($keep) {
                $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $base = $base !== '' ? $base : 'image';
                $fileName = $ext ? ($base . '.' . $ext) : $base;
            } else {
                $fileName = (string) Str::uuid() . ($ext ? '.' . $ext : '');
            }

            $finalName = $fileName;
            if ($keep) {
                $i = 1;
                while (File::exists($absoluteDir . DIRECTORY_SEPARATOR . $finalName)) {
                    $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);
                    $extOnly  = pathinfo($fileName, PATHINFO_EXTENSION);
                    $finalName = $extOnly ? "{$nameOnly}_{$i}.{$extOnly}" : "{$nameOnly}_{$i}";
                    $i++;
                }
            }

            $file->move($absoluteDir, $finalName);
            $filePath = $relativeDir . '/' . $finalName;

            $media = Media::create([
                'file_name'  => $finalName,
                'file_path'  => $filePath,
                'mime_type'  => $mime,
                'size'       => $size,
                'type'       => 'image',
                'created_by' => auth()->id(),
            ]);

            // 2) Remove existing attachments (single image)
            DB::table('input_instance_media')
                ->where('input_instance_id', $instance->id)
                ->delete();

            // 3) Attach this one as sort_order = 1
            DB::table('input_instance_media')->insert([
                'input_instance_id' => $instance->id,
                'media_id' => $media->id,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'media_id' => $media->id]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    //faq
    protected function assertInstanceIsFaq(InputInstance $instance): void
    {
        $instance->loadMissing('field');
        if (($instance->field?->field_type ?? null) !== 'faq') {
            abort(404);
        }
    }

    public function faqSaveSettings(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->assertInstanceIsFaq($instance);

        $data = $request->validate([
            'heading' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = [];
        if (is_string($instance->value) && $instance->value !== '') {
            $arr = json_decode($instance->value, true);
            if (is_array($arr)) $settings = $arr;
        }

        $settings['heading'] = $data['heading'] ?? null;

        $instance->value = json_encode($settings, JSON_UNESCAPED_UNICODE);
        $instance->save();

        return back()->with('success', 'FAQ settings saved.');
    }

    public function faqItemStore(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->assertInstanceIsFaq($instance);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer'   => ['nullable', 'string'],
        ]);

        $max = (int) FaqItem::where('input_instance_id', $instance->id)->max('sort_order');
        $next = $max > 0 ? $max + 10 : 10;

        $item = FaqItem::create([
            'input_instance_id' => $instance->id,
            'question' => $data['question'],
            'answer' => $data['answer'] ?? null,
            'sort_order' => $next,
            'is_active' => true,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'question' => $item->question,
                    'answer' => $item->answer,
                    'is_active' => (bool) $item->is_active,
                ],
                // optional: so UI can enable/disable arrows precisely
                'count' => FaqItem::where('input_instance_id', $instance->id)->count(),
            ]);
        }

        return back()->with('success', 'FAQ item added.');
    }


    public function faqItemsBulkUpdate(Request $request, int $instanceId)
    {
        $instance = InputInstance::findOrFail($instanceId);
        $this->assertInstanceIsFaq($instance);

        $data = $request->validate([
            'items' => ['array'],
            'items.*.question' => ['required', 'string', 'max:255'],
            'items.*.answer' => ['nullable', 'string'],
            'items.*.is_active' => ['nullable', 'in:0,1'],
        ]);

        DB::transaction(function () use ($instance, $data) {
            foreach (($data['items'] ?? []) as $itemId => $row) {
                $item = FaqItem::where('id', $itemId)
                    ->where('input_instance_id', $instance->id)
                    ->first();

                if (!$item) continue;

                $item->question = $row['question'];
                $item->answer = $row['answer'] ?? null;
                $item->is_active = (bool)($row['is_active'] ?? false);
                $item->save();
            }
        });

        return back()->with('success', 'FAQ saved.');
    }

    public function faqItemMove(Request $request, int $itemId)
    {
        $data = $request->validate([
            'dir' => ['required', 'in:up,down'],
        ]);

        $item = FaqItem::findOrFail($itemId);
        $instance = InputInstance::findOrFail($item->input_instance_id);
        $this->assertInstanceIsFaq($instance);

        $items = FaqItem::where('input_instance_id', $instance->id)
            ->orderBy('sort_order')
            ->get();

        $idx = $items->search(fn($i) => $i->id === $item->id);
        if ($idx === false) return back();

        $swapWith = $data['dir'] === 'up' ? $idx - 1 : $idx + 1;
        if ($swapWith < 0 || $swapWith >= $items->count()) return back();

        $a = $items[$idx];
        $b = $items[$swapWith];

        DB::transaction(function () use ($a, $b) {
            $tmp = $a->sort_order;
            $a->sort_order = $b->sort_order;
            $b->sort_order = $tmp;
            $a->save();
            $b->save();
        });

        return back()->with('success', 'Reordered.');
    }

    public function faqItemDestroy(int $itemId)
    {
        $item = FaqItem::findOrFail($itemId);
        $instance = InputInstance::findOrFail($item->input_instance_id);
        $this->assertInstanceIsFaq($instance);

        $item->delete();

        return back()->with('success', 'FAQ item deleted.');
    }
}
