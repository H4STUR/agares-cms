<?php

namespace Tests\Feature\Authorization;

class ViewerCanReadAdminTest extends AuthorizationTestCase
{
    public function test_viewer_can_open_admin_index_pages(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->get('/admin')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/sites')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/menus')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/media')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/users')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/permissions')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/settings')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/custom')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/cookies')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/cookies/settings')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/forum')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/api')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/api/documentation')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/tools/qr-generator')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/documentation')->assertStatus(200);
        $this->actingAs($viewer)->get('/admin/info')->assertStatus(200);
    }
}
