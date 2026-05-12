<?php

namespace Tests\Feature\Authorization;

use App\Models\ApiKey;
use App\Models\Article;
use App\Models\Category;
use App\Models\CookieScan;
use App\Models\Menu;
use App\Models\Site;
use Spatie\Permission\Models\Permission;

class ViewerCannotMutateTest extends AuthorizationTestCase
{
    public function test_viewer_gets_403_on_sites_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $site = Site::factory()->create();

        $this->actingAs($viewer)->post('/admin/sites', ['name' => 'New Site'])->assertForbidden();
        $this->actingAs($viewer)->patch("/admin/sites/{$site->id}", ['name' => 'Renamed'])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/sites/{$site->id}")->assertForbidden();
        $this->actingAs($viewer)->get('/admin/sites/create')->assertForbidden();
    }

    public function test_viewer_gets_403_on_articles_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $site = Site::factory()->create();
        $article = Article::factory()->create(['site_id' => $site->id]);

        $this->actingAs($viewer)->post("/admin/sites/{$site->id}/articles", [])->assertForbidden();
        $this->actingAs($viewer)->patch("/admin/sites/{$site->id}/articles/{$article->id}", [])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/sites/{$site->id}/articles/{$article->id}")->assertForbidden();
        $this->actingAs($viewer)->post("/admin/sites/{$site->id}/articles/reorder", [])->assertForbidden();
    }

    public function test_viewer_gets_403_on_categories_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $site = Site::factory()->create();
        $category = Category::factory()->create(['site_id' => $site->id]);

        $this->actingAs($viewer)->post("/admin/sites/{$site->id}/categories", [])->assertForbidden();
        $this->actingAs($viewer)->patch("/admin/sites/{$site->id}/categories/{$category->id}", [])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/sites/{$site->id}/categories/{$category->id}")->assertForbidden();
    }

    public function test_viewer_gets_403_on_menus_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $menu = Menu::create(['name' => 'Test Menu']);

        $this->actingAs($viewer)->post('/admin/menus', ['name' => 'X'])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/menus/{$menu->id}")->assertForbidden();
    }

    public function test_viewer_gets_403_on_media_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->post('/admin/media/upload')->assertForbidden();
        $this->actingAs($viewer)->delete('/admin/media/1')->assertForbidden();
        $this->actingAs($viewer)->patch('/admin/media/1/rename', ['name' => 'x'])->assertForbidden();
    }

    public function test_viewer_gets_403_on_settings_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->patch('/admin/settings', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/settings/addcustom', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/cache/clear')->assertForbidden();
        $this->actingAs($viewer)->post('/admin/settings/robots', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/settings/sitemap/generate', [])->assertForbidden();
    }

    public function test_viewer_gets_403_on_users_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->post('/admin/users/add', [
            'username' => 'attacker',
            'name'     => 'A',
            'email'    => 'a@a.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role_id'  => 1,
        ])->assertForbidden();
    }

    public function test_viewer_gets_403_on_permission_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $permission = Permission::first();
        $role = \Spatie\Permission\Models\Role::where('name', 'viewer')->first();

        $this->actingAs($viewer)->post('/admin/permissions/assign', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/permissions/create', [])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/permissions/{$permission->id}")->assertForbidden();
        $this->actingAs($viewer)->post('/admin/roles/create', [])->assertForbidden();
        $this->actingAs($viewer)->delete("/admin/roles/{$role->id}")->assertForbidden();
        $this->actingAs($viewer)->patch("/admin/permissions/roles/{$role->id}", [])->assertForbidden();
    }

    public function test_viewer_gets_403_on_cookies_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $scan = CookieScan::create([
            'domain'      => 'example.com',
            'url'         => 'https://example.com',
            'status'      => 'pending',
            'scanned_at'  => now(),
        ]);

        $this->actingAs($viewer)->post('/admin/cookies/settings', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/cookies/saas-settings', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/cookies/scan-async', [])->assertForbidden();
        $this->actingAs($viewer)->post("/admin/cookies/scan-cancel/{$scan->id}", [])->assertForbidden();
    }

    public function test_viewer_gets_403_on_api_key_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');
        $apiKey = ApiKey::create([
            'name'      => 'test',
            'key_hash'  => hash('sha256', 'whatever'),
            'abilities' => ['content:read'],
        ]);

        $this->actingAs($viewer)->post('/admin/api/keys', ['name' => 'X'])->assertForbidden();
        $this->actingAs($viewer)->post("/admin/api/keys/{$apiKey->id}/revoke")->assertForbidden();
    }

    public function test_viewer_gets_403_on_custom_code_mutation(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->patch('/admin/custom', [])->assertForbidden();
    }

    public function test_viewer_gets_403_on_input_instance_mutations(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->post('/admin/site/1/inputs', [])->assertForbidden();
        $this->actingAs($viewer)->delete('/admin/site/inputs/1')->assertForbidden();
        $this->actingAs($viewer)->patch('/admin/input-instances/bulk', [])->assertForbidden();
        $this->actingAs($viewer)->post('/admin/input-instances/1/files/upload')->assertForbidden();
    }
}
