<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoFreelanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the dashboard returns a successful response for authenticated users.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(DemoFreelanceSeeder::class);
        $user = User::first();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    public function test_profile_page_loads_and_saves_profile(): void
    {
        $this->seed(DemoFreelanceSeeder::class);
        $user = User::first();

        $this->actingAs($user)->get('/profile')
            ->assertStatus(200)
            ->assertSee('Freelancer & Studio Branding', false);
    }
}
