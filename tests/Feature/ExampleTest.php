<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the root URL redirects to dashboard/login.
     */
    public function test_the_application_root_redirects_appropriately(): void
    {
        $response = $this->get('/');

        // Root redirects to dashboard, which redirects guest to login
        $response->assertRedirect('/dashboard');
    }
}
