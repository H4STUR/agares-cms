<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InputField;
use App\Models\InputTemplate;
use App\Models\InputTemplateItem;

class InputTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSiteTemplate();
        $this->seedCategoryTemplate();
        $this->seedArticleTemplate();
    }

    private function seedSiteTemplate(): void
    {
        $tpl = InputTemplate::updateOrCreate(
            ['name' => 'Default Site Template', 'applies_to' => 'site'],
            []
        );

        $this->syncItems($tpl->id, [
            ['field_type' => 'gallery',    'label' => 'Gallery',  'variable' => 'gallery',   'sort_order' => 1],
            ['field_type' => 'short_text', 'label' => 'Header',   'variable' => 'header',    'sort_order' => 2],
            ['field_type' => 'text_editor',   'label' => 'Content',  'variable' => 'content',   'sort_order' => 3],
            ['field_type' => 'file',       'label' => 'Files',    'variable' => 'files',     'sort_order' => 4],
        ]);
    }

    private function seedCategoryTemplate(): void
    {
        $tpl = InputTemplate::updateOrCreate(
            ['name' => 'Default Category Template', 'applies_to' => 'category'],
            []
        );

        $this->syncItems($tpl->id, [
            ['field_type' => 'gallery',    'label' => 'Gallery',  'variable' => 'gallery',   'sort_order' => 1],
            ['field_type' => 'short_text', 'label' => 'Header',   'variable' => 'header',    'sort_order' => 2],
            ['field_type' => 'text_editor',   'label' => 'Content',  'variable' => 'content',   'sort_order' => 3],
            ['field_type' => 'file',       'label' => 'Files',    'variable' => 'files',     'sort_order' => 4],
        ]);
    }

    private function seedArticleTemplate(): void
    {
        $tpl = InputTemplate::updateOrCreate(
            ['name' => 'Default Article Template', 'applies_to' => 'article'],
            []
        );

        $this->syncItems($tpl->id, [
            ['field_type' => 'image',      'label' => 'Thumbnail','variable' => 'thumbnail', 'sort_order' => 1],
            ['field_type' => 'gallery',    'label' => 'Gallery',  'variable' => 'gallery',   'sort_order' => 2],
            ['field_type' => 'short_text', 'label' => 'Header',   'variable' => 'header',    'sort_order' => 3],
            ['field_type' => 'text_editor',   'label' => 'Content',  'variable' => 'content',   'sort_order' => 4],
            ['field_type' => 'file',       'label' => 'Files',    'variable' => 'files',     'sort_order' => 5],
        ]);
    }

    private function syncItems(int $templateId, array $items): void
    {
        // wipe and recreate (simple + deterministic)
        InputTemplateItem::where('input_template_id', $templateId)->delete();

        foreach ($items as $item) {
            $fieldId = InputField::where('field_type', $item['field_type'])->value('id');

            if (!$fieldId) {
                throw new \RuntimeException("Missing input_fields row for field_type='{$item['field_type']}' (seed InputFieldSeeder first).");
            }

            InputTemplateItem::create([
                'input_template_id' => $templateId,
                'input_field_id' => $fieldId,
                'label' => $item['label'],
                'variable' => $item['variable'],
                'default_value' => null,
                'description' => null,
                'sort_order' => $item['sort_order'],
                'is_locked' => false,
            ]);
        }
    }
}
