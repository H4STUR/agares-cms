<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Site;
use App\Models\Category;
use App\Models\Article;

class SiteTreeTest extends TestCase
{
    // use RefreshDatabase;

    public function test_site_can_have_categories_and_articles(): void
    {
        $site = Site::factory()->create();

        $category = Category::factory()->create([
            'site_id' => $site->id,
        ]);

        $article = Article::factory()->create([
            'site_id' => $site->id,
        ]);

        $article->categories()->attach($category->id);

        $this->assertTrue(
            $site->categories->contains($category)
        );

        $this->assertTrue(
            $category->articles->contains($article)
        );
    }
}
