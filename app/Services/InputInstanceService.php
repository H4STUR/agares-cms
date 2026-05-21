<?php

namespace App\Services;

use App\Models\InputTemplate;
use App\Models\InputTemplateItem;

class InputInstanceService
{
    /**
     * Create input instances from templates for a given owner model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $owner  (Site/Category/Article)
     * @param  \Illuminate\Support\Collection<int, InputTemplate> $templates
     */
    public function applyTemplates($owner, $templates, ?int $createdBy = null): void
    {
        $items = $templates->flatMap(fn ($tpl) => $tpl->items);

        foreach ($items as $item) {
            // Don’t duplicate if already exists
            $owner->inputInstances()->updateOrCreate(
                ['variable' => $item->variable],
                [
                    'input_field_id' => $item->input_field_id,
                    'label' => $item->label,
                    'description' => $item->description,
                    'value' => $item->default_value,
                    'sort_order' => $item->sort_order,
                    'is_default' => true,
                    'is_locked' => $item->is_locked,
                    'created_by' => $createdBy,
                ]
            );
        }
    }
}
