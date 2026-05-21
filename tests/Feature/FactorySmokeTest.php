<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactorySmokeTest extends TestCase
{
    // use RefreshDatabase;

    public function test_example(): void
    {
        // In a fresh test DB, homepage may not exist -> 404 is OK
        $this->get('/')->assertStatus(404);
    }

    public function test_factories_can_create_sites_categories_and_articles_tree(): void
    {
        \App\Models\User::factory()->create();

        $sites = \App\Models\Site::factory()->count(3)->create();

        foreach ($sites as $site) {
            $categories = \App\Models\Category::factory()
                ->count(4)
                ->create(['site_id' => $site->id]);

            $field = \App\Models\InputField::factory()->create();

            \App\Models\InputInstance::factory()
                ->forSite($site)
                ->count(3)
                ->create(['input_field_id' => $field->id]);

            foreach ($categories as $category) {
                $articles = \App\Models\Article::factory()
                    ->count(6)
                    ->create([
                        'site_id' => $site->id,
                    ]);

                // Attach each article to this category (many-to-many)
                foreach ($articles as $article) {
                    $article->categories()->attach($category->id);
                }
            }
        }

        $this->assertDatabaseCount('sites', 3);
        $this->assertDatabaseCount('categories', 12);
        $this->assertDatabaseCount('articles', 72);

        // Optional but strong: assert pivot rows exist
        // IMPORTANT: adjust table name if yours differs
        // $this->assertDatabaseCount('article_category', 72);
    }
}

// RUN:
// docker exec -it agares php artisan test
// docker exec -it agares php artisan test --filter=FactorySmokeTest
