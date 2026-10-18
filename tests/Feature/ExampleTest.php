<?php

namespace Tests\Feature;

use Database\Seeders\DemoFreelanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the dashboard returns a successful response.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(DemoFreelanceSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
