<?php

namespace Database\Seeders;

use App\Models\CustomCode;
use Illuminate\Database\Seeder;

class CustomCodeSeeder extends Seeder
{
    public function run()
    {
        CustomCode::updateOrCreate(
            ['type' => 'script', 'description' => 'Sample script for custom code'],
            ['content' => '<script></script>']
        );

        CustomCode::updateOrCreate(
            ['type' => 'style', 'description' => 'Sample style for custom code'],
            ['content' => '.exampl-class-css{display: block;}']
        );

    }
}

/*
INSERT INTO `custom_codes` (`type`, `content`, `description`, `created_at`, `updated_at`)
VALUES 
('script', '<script></script>', 'Sample script for custom code', NOW(), NOW()),
('style', '<style></style>', 'Sample style for custom code', NOW(), NOW());
*/