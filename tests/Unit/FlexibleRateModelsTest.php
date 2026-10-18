<?php

namespace Tests\Unit;

use App\Enums\BillingType;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlexibleRateModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_hour_rate_model(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Hourly Client', 'default_rate' => 120.00]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Hourly Project',
            'slug' => 'hourly-proj',
            'billing_type' => BillingType::PerHour,
            'hourly_rate' => 200.00,
            'budget_hours' => 50.00,
        ]);

        $this->assertEquals(BillingType::PerHour, $project->billing_type);
        $this->assertEquals('Per Hour', $project->billing_type->label());
        $this->assertEquals(200.00, $project->effective_hourly_rate);
    }

    public function test_per_task_rate_model_and_effective_hourly_yield(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Task Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Per Task Project',
            'slug' => 'task-proj',
            'billing_type' => BillingType::PerTask,
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Build Webhook Engine',
            'fixed_amount' => 800.00, // Fixed price RM 800
        ]);

        // Log 2 hours of work on this RM 800 task
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'description' => 'Working on webhooks',
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'duration_seconds' => 7200, // 2 hours
            'hourly_rate_snapshot' => 100.00,
            'is_billable' => true,
        ]);

        $task->refresh();
        $project->refresh();

        $this->assertEquals(2.0, $task->logged_hours);
        // RM 800 / 2 hours = RM 400.00/h effective yield!
        $this->assertEquals(400.00, $task->effective_hourly_rate);
        $this->assertEquals(400.00, $project->effective_hourly_rate);
    }

    public function test_per_project_rate_model_and_burn(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Fixed Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Fixed Lump-Sum Project',
            'slug' => 'fixed-proj',
            'billing_type' => BillingType::PerProject,
            'fixed_budget_amount' => 10000.00,
            'budget_hours' => 100.00,
        ]);

        // Log 25 hours
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Development milestones',
            'started_at' => now()->subHours(25),
            'ended_at' => now(),
            'duration_seconds' => 90000, // 25 hours
            'hourly_rate_snapshot' => 100.00,
            'is_billable' => true,
        ]);

        $project->refresh();

        $this->assertEquals(25.0, $project->logged_hours);
        $this->assertEquals(25.0, $project->burn_percentage);
        // RM 10,000 / 25 hours = RM 400/h effective yield
        $this->assertEquals(400.00, $project->effective_hourly_rate);
    }

    public function test_monthly_retainer_rate_model(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Retainer Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Cloud Ops Retainer',
            'slug' => 'cloud-retainer',
            'billing_type' => BillingType::Monthly,
            'monthly_retainer_amount' => 6000.00,
            'budget_hours' => 40.00, // 40h retainer allowance
        ]);

        // Log 15 hours
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Terraform ECS updates',
            'started_at' => now()->subHours(15),
            'ended_at' => now(),
            'duration_seconds' => 54000, // 15 hours
            'hourly_rate_snapshot' => 150.00,
            'is_billable' => true,
        ]);

        $project->refresh();

        $this->assertEquals(15.0, $project->logged_hours);
        $this->assertEquals(37.5, $project->burn_percentage); // 15 / 40 = 37.5%
        // RM 6,000 / 15 hours = RM 400.00/h effective rate
        $this->assertEquals(400.00, $project->effective_hourly_rate);
    }
}
