<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'office@agares.co.uk')->value('id');

        $mainMenu = Menu::firstOrCreate(
            ['name' => 'Main Menu'],
            [
                'is_system'  => 1,
                'created_by' => $ownerId,
                'updated_by' => $ownerId,
            ]
        );

        $homePage = Site::where('slug', 'home')->first();
        if ($homePage && ! $mainMenu->sites()->where('site_id', $homePage->id)->exists()) {
            $mainMenu->sites()->attach($homePage->id, ['menu_order' => 1]);
        }

        Menu::firstOrCreate(
            ['name' => 'Static Pages'],
            [
                'is_system'  => 1,
                'created_by' => $ownerId,
                'updated_by' => $ownerId,
            ]
        );
    }
}
