<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileTest extends TestCase
{
    /**
     * Profile management in this app is handled by UserSettingsController
     * at /user/{user}/settings — not via a /profile route.
     * These placeholder tests exist to avoid removing the test file entirely.
     */
    public function test_profile_routes_are_managed_via_user_settings(): void
    {
        $this->markTestSkipped(
            'Profile management uses /user/{user}/settings via UserSettingsController, not /profile routes.'
        );
    }
}
