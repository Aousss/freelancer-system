<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebaseAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.firebase.api_key', 'test-fake-api-key');
    }

    public function test_login_page_renders_for_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_login_page_redirects_if_already_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_callback_requires_id_token(): void
    {
        $response = $this->postJson(route('auth.firebase.callback'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }

    public function test_callback_returns_401_when_token_is_invalid(): void
    {
        Http::fake([
            'https://identitytoolkit.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 400,
                    'message' => 'INVALID_ID_TOKEN',
                ],
            ], 400),
        ]);

        $response = $this->postJson(route('auth.firebase.callback'), [
            'id_token' => 'invalid-token',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'INVALID_ID_TOKEN']);
        $this->assertFalse(Auth::check());
    }

    public function test_callback_authenticates_and_creates_new_user(): void
    {
        Http::fake([
            'https://identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'firebase-uid-123',
                        'email' => 'jane@example.com',
                        'displayName' => 'Jane Doe',
                        'photoUrl' => 'https://example.com/avatar.jpg',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('auth.firebase.callback'), [
            'id_token' => 'valid-token',
        ]);

        $response->assertOk()
            ->assertJson(['redirect' => route('dashboard')]);

        $this->assertTrue(Auth::check());
        $this->assertDatabaseHas('users', [
            'firebase_uid' => 'firebase-uid-123',
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
    }

    public function test_callback_links_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'firebase_uid' => null,
            'name' => 'Old Name',
        ]);

        Http::fake([
            'https://identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'google-uid-456',
                        'email' => 'existing@example.com',
                        'displayName' => 'Google Name',
                        'photoUrl' => 'https://example.com/photo.jpg',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('auth.firebase.callback'), [
            'id_token' => 'valid-token',
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'firebase_uid' => 'google-uid-456',
            'email' => 'existing@example.com',
            'name' => 'Google Name',
        ]);
        $this->assertEquals($existingUser->id, Auth::id());
    }

    public function test_callback_handles_curl_ssl_error_in_local_environment(): void
    {
        $attempts = 0;
        Http::fake(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new ConnectionException('cURL error 60: SSL certificate problem: unable to get local issuer certificate');
            }

            return Http::response([
                'users' => [
                    [
                        'localId' => 'ssl-uid-789',
                        'email' => 'ssl@example.com',
                        'displayName' => 'SSL User',
                    ],
                ],
            ], 200);
        });

        $response = $this->postJson(route('auth.firebase.callback'), [
            'id_token' => 'valid-token',
        ]);

        $response->assertOk()
            ->assertJson(['redirect' => route('dashboard')]);
        $this->assertEquals(2, $attempts);
        $this->assertDatabaseHas('users', [
            'firebase_uid' => 'ssl-uid-789',
            'email' => 'ssl@example.com',
        ]);
    }

    public function test_logout_invalidates_session_and_redirects(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertFalse(Auth::check());
    }
}
