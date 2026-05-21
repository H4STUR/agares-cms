<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Site;

class FrontendPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_page_renders(): void
    {
        $this->markTestSkipped(
            'Requires view pages.frontend.sites.default to exist. Create the frontend template to enable this test.'
        );
    }
}
