<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Category;
use App\Models\Article;
use App\Models\InputTemplate;
use App\Models\InputInstance;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputTemplateController extends Controller
{
    protected function resolveOwner(string $type, int $id)
    {
        return match ($type) {
            'site' => Site::findOrFail($id),
            'category' => Category::findOrFail($id),
            'article' => Article::findOrFail($id),
            default => abort(404),
        };
    }

    public function applyToOwner(Request $request)
    {
        $validated = $request->validate([
            'owner_type' => 'required|in:site,category,article',
            'owner_id' => 'required|integer',
            'template_id' => 'required|integer|exists:input_templates,id',
            'mode' => 'nullable|in:missing_only,overwrite', // default missing_only
        ]);

        $owner = $this->resolveOwner($validated['owner_type'], (int)$validated['owner_id']);

        $template = InputTemplate::with('items.field')->findOrFail($validated['template_id']);

        // safety: template applies_to must match owner_type
        if ($template->applies_to !== $validated['owner_type']) {
            return back()->withErrors(['template_id' => 'This template does not apply to this model.']);
        }

        $mode = $validated['mode'] ?? 'missing_only';

        DB::beginTransaction();

        try {
            foreach ($template->items as $item) {
                $exists = InputInstance::where('owner_type', get_class($owner))
                    ->where('owner_id', $owner->id)
                    ->where('variable', $item->variable)
                    ->exists();

                if ($mode === 'missing_only' && $exists) {
                    continue;
                }

                // overwrite = delete existing with same variable first
                if ($mode === 'overwrite' && $exists) {
                    $old = InputInstance::where('owner_type', get_class($owner))
                        ->where('owner_id', $owner->id)
                        ->where('variable', $item->variable)
                        ->first();

                    if ($old) {
                        if ($old->gallery_id) {
                            $g = Gallery::find($old->gallery_id);
                            if ($g) {
                                $g->media()->detach();
                                $g->delete();
                            }
                        }
                        $old->delete();
                    }
                }

                $maxOrder = InputInstance::where('owner_type', get_class($owner))
                    ->where('owner_id', $owner->id)
                    ->max('sort_order') ?? 0;

                $instance = InputInstance::create([
                    'owner_type'     => get_class($owner),
                    'owner_id'       => $owner->id,
                    'input_field_id' => $item->input_field_id,

                    'label'       => $item->label,
                    'variable'    => $item->variable,
                    'value'       => $item->default_value,
                    'description' => $item->description,

                    'sort_order'  => $maxOrder + 1,
                    'is_default'  => true,
                    'is_locked'   => (bool)$item->is_locked,

                    'created_by'  => auth()->id(),
                ]);

                // auto create gallery row if this template item is gallery
                if ($item->field && $item->field->field_type === 'gallery') {
                    $gallery = Gallery::create([
                        'owner_type' => get_class($owner),
                        'owner_id'   => $owner->id,
                        'name'       => $instance->label ?: 'Gallery',
                        'variable'   => $instance->variable,
                        'sort_order' => $instance->sort_order,
                    ]);

                    $instance->update(['gallery_id' => $gallery->id]);
                }
            }

            DB::commit();
            return back()->with('success', 'Template applied.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to apply template: ' . $e->getMessage()]);
        }
    }
}
