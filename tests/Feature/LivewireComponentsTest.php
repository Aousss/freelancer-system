<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\BentoOverview;
use App\Livewire\GlobalTimerBar;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Invoices\SplitInvoiceBuilder;
use App\Livewire\Projects\ProjectIndex;
use App\Livewire\Projects\ProjectWorkspace;
use App\Livewire\Timesheet\TimesheetManager;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\DemoFreelanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoFreelanceSeeder::class);
        $this->actingAs(User::first());
    }

    public function test_global_timer_bar_starts_pauses_resumes_and_stops(): void
    {
        $project = Project::first();

        Livewire::test(GlobalTimerBar::class)
            ->set('projectId', $project->id)
            ->set('description', 'Livewire timer test')
            ->call('start')
            ->assertSet('isRunning', true)
            ->assertSet('isPaused', false)
            ->assertDispatched('timer-started')
            ->assertDispatched('toast')
            ->call('pause')
            ->assertSet('isRunning', true)
            ->assertSet('isPaused', true)
            ->assertDispatched('timer-paused')
            ->assertDispatched('toast')
            ->call('resume')
            ->assertSet('isRunning', true)
            ->assertSet('isPaused', false)
            ->assertDispatched('timer-resumed')
            ->assertDispatched('toast')
            ->call('stop')
            ->assertSet('isRunning', false)
            ->assertSet('isPaused', false)
            ->assertDispatched('timer-stopped')
            ->assertDispatched('toast');
    }

    public function test_timesheet_manager_manual_entry_flow(): void
    {
        $project = Project::first();

        Livewire::test(TimesheetManager::class)
            ->set('manualProjectId', $project->id)
            ->set('manualDescription', 'Manual test entry')
            ->set('manualDuration', '1h 30m')
            ->set('manualDate', now()->toDateString())
            ->call('saveManualEntry')
            ->assertSet('showManualModal', false)
            ->assertDispatched('timesheet-updated')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('time_entries', [
            'description' => 'Manual test entry',
            'duration_seconds' => 5400,
        ]);
    }

    public function test_project_workspace_renders_and_updates_task_status(): void
    {
        $project = Project::where('slug', 'pinnacle-cms')->first();
        $task = $project->tasks()->first();

        Livewire::test(ProjectWorkspace::class, ['slug' => 'pinnacle-cms'])
            ->assertSee($project->name)
            ->call('updateTaskStatus', $task->id, 'done');

        $task->refresh();
        $this->assertEquals('done', $task->status->value);
    }

    public function test_split_invoice_builder_generates_and_locks(): void
    {
        $client = Client::first();

        Livewire::test(SplitInvoiceBuilder::class)
            ->set('clientId', $client->id)
            ->set('aggregationMode', 'grouped_by_task')
            ->set('taxRate', 8.0)
            ->call('generateInvoice');

        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
            'tax_rate' => 8.0,
        ]);
    }

    public function test_record_historical_paid_invoice_and_continue_numbering(): void
    {
        $client = Client::first();
        $aggregator = app(\App\Services\InvoiceAggregatorService::class);

        Livewire::test(InvoiceIndex::class)
            ->call('openHistoricalModal')
            ->assertSet('showHistoricalModal', true)
            ->set('historicalClientId', $client->id)
            ->set('historicalInvoiceNumber', 'INV-2026-042')
            ->set('historicalIssueDate', '2026-08-01')
            ->set('historicalDueDate', '2026-08-15')
            ->set('historicalStatus', 'paid')
            ->set('historicalPaidAt', '2026-08-10')
            ->set('historicalCurrency', 'MYR')
            ->set('historicalTaxRate', 10.0)
            ->set('historicalItems', [
                [
                    'description' => 'Legacy Web Development Retainer',
                    'quantity_hours' => 1.0,
                    'unit_rate' => 5000.0,
                ],
            ])
            ->call('saveHistoricalInvoice')
            ->assertSet('showHistoricalModal', false)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-2026-042',
            'status' => 'paid',
            'subtotal' => 5000.00,
            'tax_amount' => 500.00,
            'total' => 5500.00,
        ]);

        // Verify the aggregator now automatically continues from 42 -> 43!
        $nextNumber = $aggregator->generateNextInvoiceNumber();
        $this->assertEquals('INV-2026-043', $nextNumber);
    }
}
