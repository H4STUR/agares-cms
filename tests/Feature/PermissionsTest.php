<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }

    public function test_admin_can_access_admin(): void
    {
        // Seed real role+permission set; the admin role is then fully populated.
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200);
    }
}
