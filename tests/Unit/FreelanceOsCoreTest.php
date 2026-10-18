<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\InvoiceAggregatorService;
use App\Services\RateResolverService;
use App\Services\TimeParserService;
use App\Services\TimerStateService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreelanceOsCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_active_timer_enforcement(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Acme Corp',
            'default_rate' => 100.00,
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Web App',
            'slug' => 'web-app',
            'hourly_rate' => 120.00,
        ]);

        $service = app(TimerStateService::class);

        // Start 1st timer
        $entry1 = $service->startTimer([
            'project_id' => $project->id,
            'description' => 'First feature',
        ], $user->id);

        $this->assertNull($entry1->ended_at);
        $this->assertEquals(0, $entry1->duration_seconds);

        // Fast-forward 1 second or ensure duration
        sleep(1);

        // Start 2nd timer
        $entry2 = $service->startTimer([
            'project_id' => $project->id,
            'description' => 'Second feature',
        ], $user->id);

        $entry1->refresh();

        // Verify entry 1 was stopped
        $this->assertNotNull($entry1->ended_at);
        $this->assertGreaterThanOrEqual(1, $entry1->duration_seconds);

        // Verify entry 2 is active
        $this->assertNull($entry2->ended_at);
        $this->assertEquals(0, $entry2->duration_seconds);
    }

    public function test_rate_resolution_hierarchy(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Client A',
            'default_rate' => 100.00,
        ]);

        $projectWithRate = Project::create([
            'client_id' => $client->id,
            'name' => 'Project 1',
            'slug' => 'project-1',
            'hourly_rate' => 150.00,
        ]);

        $projectWithoutRate = Project::create([
            'client_id' => $client->id,
            'name' => 'Project 2',
            'slug' => 'project-2',
            'hourly_rate' => null,
        ]);

        $taskWithRate = Task::create([
            'project_id' => $projectWithRate->id,
            'title' => 'Task with override',
            'hourly_rate' => 200.00,
        ]);

        $taskWithoutRate = Task::create([
            'project_id' => $projectWithRate->id,
            'title' => 'Task without override',
            'hourly_rate' => null,
        ]);

        $resolver = new RateResolverService();

        // 1. Task rate overrides project and client
        $this->assertEquals(200.00, $resolver->resolve($projectWithRate, $taskWithRate));

        // 2. Project rate overrides client when task rate is null
        $this->assertEquals(150.00, $resolver->resolve($projectWithRate, $taskWithoutRate));

        // 3. Client rate is used when project rate and task rate are null
        $this->assertEquals(100.00, $resolver->resolve($projectWithoutRate, null));
    }

    public function test_locked_invoiced_entries_protection(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Client']);
        $project = Project::create(['client_id' => $client->id, 'name' => 'Proj', 'slug' => 'proj']);

        $entry = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Completed work',
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'duration_seconds' => 7200,
            'hourly_rate_snapshot' => 100.00,
            'is_invoiced' => true, // Locked!
        ]);

        // Attempting to modify locked entry description must throw DomainException
        $this->expectException(DomainException::class);
        $entry->update(['description' => 'Hacked description']);
    }

    public function test_locked_invoiced_entries_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['user_id' => $user->id, 'name' => 'Client']);
        $project = Project::create(['client_id' => $client->id, 'name' => 'Proj', 'slug' => 'proj']);

        $entry = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Completed work',
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'duration_seconds' => 7200,
            'hourly_rate_snapshot' => 100.00,
            'is_invoiced' => true,
        ]);

        $this->expectException(DomainException::class);
        $entry->delete();
    }

    public function test_natural_language_time_parser(): void
    {
        $parser = new TimeParserService();

        $this->assertEquals(5400, $parser->parseToSeconds('1h 30m'));
        $this->assertEquals(5400, $parser->parseToSeconds('1h30m'));
        $this->assertEquals(2700, $parser->parseToSeconds('45m'));
        $this->assertEquals(8100, $parser->parseToSeconds('02:15'));
        $this->assertEquals(6300, $parser->parseToSeconds('1.75'));
        $this->assertEquals(6300, $parser->parseToSeconds('1.75h'));
        $this->assertEquals(7200, $parser->parseToSeconds('2h'));
    }

    public function test_invoice_aggregator_generates_and_locks_entries(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Global Corp',
            'currency' => 'USD',
            'default_rate' => 150.00,
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Enterprise Portal',
            'slug' => 'ent-portal',
            'hourly_rate' => 150.00,
        ]);

        $entry1 = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Feature 1',
            'started_at' => now()->subDays(2),
            'ended_at' => now()->subDays(2)->addHours(2),
            'duration_seconds' => 7200,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 300.00,
            'is_billable' => true,
            'is_invoiced' => false,
        ]);

        $entry2 = TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'description' => 'Feature 2',
            'started_at' => now()->subDays(1),
            'ended_at' => now()->subDays(1)->addHour(),
            'duration_seconds' => 3600,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 150.00,
            'is_billable' => true,
            'is_invoiced' => false,
        ]);

        $aggregator = app(InvoiceAggregatorService::class);
        $invoice = $aggregator->generateInvoice([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'selected_time_entry_ids' => [$entry1->id, $entry2->id],
            'aggregation_mode' => 'detailed',
            'tax_rate' => 10.00,
        ]);

        $this->assertEquals(450.00, (float) $invoice->subtotal);
        $this->assertEquals(45.00, (float) $invoice->tax_amount);
        $this->assertEquals(495.00, (float) $invoice->total);
        $this->assertCount(2, $invoice->items);

        $entry1->refresh();
        $entry2->refresh();

        $this->assertTrue($entry1->is_invoiced);
        $this->assertEquals($invoice->id, $entry1->invoice_id);
        $this->assertTrue($entry2->is_invoiced);
        $this->assertEquals($invoice->id, $entry2->invoice_id);
    }

    public function test_timer_pause_and_resume_accumulates_duration_accurately(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Pause Test Client',
            'default_rate' => 120.00,
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Pause Test Project',
            'slug' => 'pause-test',
            'hourly_rate' => 120.00,
        ]);

        $service = app(TimerStateService::class);

        // T0: 10:00:00
        \Illuminate\Support\Carbon::setTestNow('2026-10-16 10:00:00');
        $entry = $service->startTimer([
            'project_id' => $project->id,
            'description' => 'Coding with break',
        ], $user->id);

        $this->assertFalse($entry->is_paused);
        $this->assertEquals(0, $entry->duration_seconds);

        // T1: 10:10:00 (10 minutes running)
        \Illuminate\Support\Carbon::setTestNow('2026-10-16 10:10:00');
        $this->assertEquals(600, $entry->fresh()->elapsed_seconds);

        // Pause timer
        $service->pauseTimer($entry);
        $entry->refresh();

        $this->assertTrue($entry->is_paused);
        $this->assertEquals(600, $entry->duration_seconds);

        // T2: 10:40:00 (30 minutes passed while paused)
        \Illuminate\Support\Carbon::setTestNow('2026-10-16 10:40:00');
        // Elapsed seconds should remain frozen at 600
        $this->assertEquals(600, $entry->fresh()->elapsed_seconds);

        // Resume timer
        $service->resumeTimer($entry);
        $entry->refresh();

        $this->assertFalse($entry->is_paused);
        $this->assertEquals(600, $entry->duration_seconds);

        // T3: 10:55:00 (15 more minutes running)
        \Illuminate\Support\Carbon::setTestNow('2026-10-16 10:55:00');
        // Elapsed seconds should now be 600 + 900 = 1500 seconds
        $this->assertEquals(1500, $entry->fresh()->elapsed_seconds);

        // Stop timer
        $stopped = $service->stopTimer($entry);
        $this->assertFalse($stopped->is_paused);
        $this->assertNotNull($stopped->ended_at);
        $this->assertEquals(1500, $stopped->duration_seconds);
        // 1500s / 3600s = 0.416666... * 120 = 50.00
        $this->assertEquals(50.00, (float) $stopped->billable_amount);

        \Illuminate\Support\Carbon::setTestNow();
    }
}
