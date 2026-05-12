<?php

namespace Tests\Feature\Authorization;

use Illuminate\Support\Facades\Gate;

class OwnerHasFullAccessTest extends AuthorizationTestCase
{
    public function test_owner_passes_every_gate_via_before_callback(): void
    {
        $owner = $this->userWithRole('owner');
        $this->actingAs($owner);

        // Gate::before in AuthServiceProvider must return true for owner regardless of the ability string —
        // including a brand-new permission that hasn't been seeded yet.
        $this->assertTrue(Gate::allows('manage sites'));
        $this->assertTrue(Gate::allows('manage permissions'));
        $this->assertTrue(Gate::allows('manage ecommerce'));
        $this->assertTrue(Gate::allows('manage orders'));
        $this->assertTrue(Gate::allows('some-future-unseeded-permission'));
    }

    public function test_owner_can_open_mutating_get_routes(): void
    {
        $owner = $this->userWithRole('owner');

        $this->actingAs($owner)->get('/admin')->assertStatus(200);
        $this->actingAs($owner)->get('/admin/sites/create')->assertStatus(200);
        $this->actingAs($owner)->get('/admin/permissions')->assertStatus(200);
    }
}
