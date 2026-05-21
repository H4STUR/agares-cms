<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Article;
use App\Models\Category;

class ArticleTest extends TestCase
{
    public function test_article_has_categories_relationship(): void
    {
        $article = new Article();

        $this->assertTrue(
            method_exists($article, 'categories')
        );
    }
}

