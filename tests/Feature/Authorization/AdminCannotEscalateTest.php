<?php

namespace Tests\Feature\Authorization;

class AdminCannotEscalateTest extends AuthorizationTestCase
{
    public function test_moderator_cannot_manage_settings_or_permissions(): void
    {
        $moderator = $this->userWithRole('moderator');

        $this->actingAs($moderator)->patch('/admin/settings', [])->assertForbidden();
        $this->actingAs($moderator)->post('/admin/permissions/assign', [])->assertForbidden();
        $this->actingAs($moderator)->post('/admin/users/add', [])->assertForbidden();
        $this->actingAs($moderator)->post('/admin/api/keys', [])->assertForbidden();
    }

    public function test_moderator_can_open_articles_create(): void
    {
        $moderator = $this->userWithRole('moderator');

        // moderator has 'manage articles' so this GET should be allowed (404 if site missing, but not 403).
        $response = $this->actingAs($moderator)->get('/admin/sites/1/articles/create');
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
