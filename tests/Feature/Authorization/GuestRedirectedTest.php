<?php

namespace Tests\Feature\Authorization;

class GuestRedirectedTest extends AuthorizationTestCase
{
    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/sites')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/permissions')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_guest_cannot_post_mutating_endpoints(): void
    {
        // POST without a session — Laravel redirects unauthenticated to /login.
        $this->post('/admin/sites', ['name' => 'X'])->assertRedirect('/login');
        $this->delete('/admin/sites/1')->assertRedirect('/login');
        $this->patch('/admin/settings', [])->assertRedirect('/login');
    }
}
