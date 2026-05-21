<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InputField;

class InputFieldSeeder extends Seeder
{
    public function run()
    {
        $inputTypes = [
        ['name' => 'short_text', 'field_type' => 'short_text'],
        ['name' => 'number', 'field_type' => 'number'],
        ['name' => 'text_editor', 'field_type' => 'text_editor'],
        ['name' => 'textarea', 'field_type' => 'textarea'],
        ['name' => 'code', 'field_type' => 'code'],
        ['name' => 'file', 'field_type' => 'file'],
        ['name' => 'gallery', 'field_type' => 'gallery'],
        ['name' => 'image', 'field_type' => 'image'],
        ['name' => 'contact_form', 'field_type' => 'contact_form'],
        ['name' => 'faq', 'field_type' => 'faq'],
        ];

        foreach ($inputTypes as $type) {
            InputField::updateOrCreate(
                ['field_type' => $type['field_type']],
                ['name' => $type['name']]
            );
        }

    }
}
