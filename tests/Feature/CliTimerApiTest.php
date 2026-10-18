<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CliTimerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cli_timer_lifecycle_via_api(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'CLI Client',
            'default_rate' => 150.00,
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'CLI Test Project',
            'slug' => 'cli-test',
            'hourly_rate' => 150.00,
        ]);

        Sanctum::actingAs($user);

        // 1. Initial status -> running = false
        $statusRes = $this->getJson('/api/timer/status');
        $statusRes->assertOk()->assertJson(['running' => false, 'entry' => null]);

        // 2. Start timer
        $startRes = $this->postJson('/api/timer/start', [
            'project_slug' => 'cli-test',
            'description' => 'Fixing bug from terminal',
            'git_ref' => 'fix/cli-bug',
        ]);
        $startRes->assertCreated()
            ->assertJsonPath('entry.project', 'cli-test')
            ->assertJsonPath('entry.description', 'Fixing bug from terminal');

        // 3. Status -> running = true, paused = false
        $statusRes2 = $this->getJson('/api/timer/status');
        $statusRes2->assertOk()
            ->assertJsonPath('running', true)
            ->assertJsonPath('paused', false);

        // 4. Pause timer
        $pauseRes = $this->postJson('/api/timer/pause');
        $pauseRes->assertOk()
            ->assertJsonPath('running', true)
            ->assertJsonPath('paused', true)
            ->assertJsonPath('message', 'Timer paused successfully.');

        // 5. Status -> running = true, paused = true
        $statusResPaused = $this->getJson('/api/timer/status');
        $statusResPaused->assertOk()
            ->assertJsonPath('running', true)
            ->assertJsonPath('paused', true);

        // 6. Resume timer
        $resumeRes = $this->postJson('/api/timer/resume');
        $resumeRes->assertOk()
            ->assertJsonPath('running', true)
            ->assertJsonPath('paused', false)
            ->assertJsonPath('message', 'Timer resumed successfully.');

        // 7. Stop timer
        $stopRes = $this->postJson('/api/timer/stop', [
            'git_ref' => 'fix/cli-bug-done',
        ]);
        $stopRes->assertOk()
            ->assertJsonPath('message', 'Timer stopped successfully.');

        // 8. Status again -> running = false
        $statusRes3 = $this->getJson('/api/timer/status');
        $statusRes3->assertOk()->assertJsonPath('running', false);
    }
}
