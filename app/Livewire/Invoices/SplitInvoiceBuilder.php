<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\InvoiceAggregatorService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SplitInvoiceBuilder extends Component
{
    public ?int $clientId = null;
    public ?int $projectId = null;
    public string $startDate = '';
    public string $endDate = '';
    public string $issueDate = '';
    public string $dueDate = '';
    public string $aggregationMode = 'detailed'; // detailed, grouped_by_task, single_line
    public float $taxRate = 0.00;
    public string $notes = "Payment due within 14 days of issue date.\nPlease remit payment via bank transfer.";

    public array $selectedEntryIds = [];
    public bool $selectAll = true;
    public string $errorMessage = '';
    public ?int $createdInvoiceId = null;

    public function mount(): void
    {
        $this->clientId = Client::where('status', 'active')->value('id');
        $this->taxRate = (float) \App\Models\Setting::get('default_tax_rate', 0.00);
        $dueDays = (int) \App\Models\Setting::get('invoice_due_days', 14);
        $this->notes = \App\Models\Setting::get('invoice_notes_template', "Payment due within {$dueDays} days of issue date.\nPlease remit payment via bank transfer.");

        $this->issueDate = now()->toDateString();
        $this->dueDate = now()->addDays($dueDays)->toDateString();
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();

        $this->loadUnbilledEntries();
    }

    public function updatedClientId(): void
    {
        $this->projectId = null;
        $this->loadUnbilledEntries();
    }

    public function updatedProjectId(): void
    {
        $this->loadUnbilledEntries();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedEntryIds = $this->getEligibleEntries()->pluck('id')->map(fn($id) => (int)$id)->toArray();
        } else {
            $this->selectedEntryIds = [];
        }
    }

    public function loadUnbilledEntries(): void
    {
        $entries = $this->getEligibleEntries();
        $this->selectedEntryIds = $entries->pluck('id')->map(fn($id) => (int)$id)->toArray();
        $this->selectAll = true;
    }

    public function getEligibleEntries(): Collection
    {
        if (!$this->clientId) {
            return collect();
        }

        $query = TimeEntry::with(['project.client', 'task'])
            ->whereHas('project', function ($q) {
                $q->where('client_id', $this->clientId);
            })
            ->where('is_billable', true)
            ->where('is_invoiced', false)
            ->whereNotNull('ended_at')
            ->orderBy('started_at', 'asc');

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        return $query->get();
    }

    public function calculatePreview(): array
    {
        $client = Client::find($this->clientId);
        $entries = TimeEntry::with(['task', 'project'])
            ->whereIn('id', $this->selectedEntryIds)
            ->where('is_invoiced', false)
            ->whereNotNull('ended_at')
            ->orderBy('started_at', 'asc')
            ->get();

        $previewItems = [];
        $subtotal = 0.00;

        if ($this->aggregationMode === 'detailed') {
            foreach ($entries as $entry) {
                $hours = round($entry->duration_seconds / 3600, 2);
                $rate = (float) $entry->hourly_rate_snapshot;
                $amount = round($hours * $rate, 2);

                $dateStr = $entry->started_at->format('Y-m-d');
                $gitSuffix = $entry->git_ref ? " ({$entry->git_ref})" : '';
                $taskPrefix = $entry->task ? "[{$entry->task->title}] " : '';
                $description = "[{$dateStr}] {$taskPrefix}{$entry->description}{$gitSuffix}";

                $previewItems[] = [
                    'description' => $description,
                    'quantity_hours' => max(0.01, $hours),
                    'unit_rate' => $rate,
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }
        } elseif ($this->aggregationMode === 'grouped_by_task') {
            $grouped = $entries->groupBy(function ($entry) {
                $key = $entry->task_id ? 'task_' . $entry->task_id : 'desc_' . md5($entry->description);
                return $key . '_' . $entry->hourly_rate_snapshot;
            });

            foreach ($grouped as $group) {
                $first = $group->first();
                $totalSec = $group->sum('duration_seconds');
                $hours = round($totalSec / 3600, 2);

                if ($first->task && !is_null($first->task->fixed_amount) && (float) $first->task->fixed_amount > 0) {
                    $rate = (float) $first->task->fixed_amount;
                    $amount = $rate;
                    $quantityHours = 1.00;
                    $desc = "Task Deliverable: {$first->task->title} (" . $group->count() . " logs, {$hours}h tracked)";
                } else {
                    $rate = (float) $first->hourly_rate_snapshot;
                    $amount = round($hours * $rate, 2);
                    $quantityHours = max(0.01, $hours);
                    $desc = $first->task ? "Task: {$first->task->title} (" . $group->count() . " log entries)" : "{$first->description} (" . $group->count() . " log entries)";
                }

                $previewItems[] = [
                    'description' => $desc,
                    'quantity_hours' => $quantityHours,
                    'unit_rate' => $rate,
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            }
        } else {
            // single_line summary
            $startDate = $entries->isNotEmpty() ? $entries->min('started_at')->format('M j, Y') : now()->format('M j, Y');
            $endDate = $entries->isNotEmpty() ? $entries->max('started_at')->format('M j, Y') : now()->format('M j, Y');

            $firstProject = $entries->first()->project ?? null;

            if ($firstProject && $firstProject->billing_type->value === 'monthly' && $firstProject->monthly_retainer_amount) {
                $amount = (float) $firstProject->monthly_retainer_amount;
                $previewItems[] = [
                    'description' => "Monthly Retainer Services: {$firstProject->name} ({$startDate} to {$endDate})",
                    'quantity_hours' => 1.00,
                    'unit_rate' => $amount,
                    'amount' => $amount,
                ];
                $subtotal += $amount;
            } else {
                $groupedByRate = $entries->groupBy('hourly_rate_snapshot');
                foreach ($groupedByRate as $rate => $group) {
                    $totalSec = $group->sum('duration_seconds');
                    $hours = round($totalSec / 3600, 2);
                    $unitRate = (float) $rate;
                    $amount = round($hours * $unitRate, 2);

                    $desc = "Software Engineering & Consulting Services ({$startDate} to {$endDate})";

                    $previewItems[] = [
                        'description' => $desc,
                        'quantity_hours' => max(0.01, $hours),
                        'unit_rate' => $unitRate,
                        'amount' => $amount,
                    ];
                    $subtotal += $amount;
                }
            }
        }

        $taxAmount = round($subtotal * ($this->taxRate / 100), 2);
        $total = $subtotal + $taxAmount;

        return [
            'client' => $client,
            'items' => $previewItems,
            'subtotal' => $subtotal,
            'taxAmount' => $taxAmount,
            'total' => $total,
            'currency' => $client->currency ?? 'MYR',
        ];
    }

    public function generateInvoice(InvoiceAggregatorService $aggregator): void
    {
        $this->errorMessage = '';

        if (empty($this->selectedEntryIds)) {
            $this->errorMessage = 'Please select at least one unbilled time entry to generate an invoice.';
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'No Entries Selected',
                'description' => $this->errorMessage,
            ]);
            return;
        }

        try {
            $invoice = $aggregator->generateInvoice([
                'client_id' => $this->clientId,
                'project_id' => $this->projectId,
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'selected_time_entry_ids' => $this->selectedEntryIds,
                'aggregation_mode' => $this->aggregationMode,
                'tax_rate' => $this->taxRate,
                'notes' => $this->notes,
            ]);

            $this->createdInvoiceId = $invoice->id;
            $this->dispatch('timesheet-updated');
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Invoice Generated',
                'description' => "Invoice #{$invoice->invoice_number} generated successfully.",
            ]);
            $this->redirect("/invoices/{$invoice->id}/pdf", navigate: false);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Generation Failed',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $projects = $this->clientId
            ? Project::where('client_id', $this->clientId)->where('status', 'active')->get()
            : collect();

        $eligibleEntries = $this->getEligibleEntries();
        $previewData = $this->calculatePreview();

        return view('livewire.invoices.split-invoice-builder', [
            'clients' => $clients,
            'projects' => $projects,
            'eligibleEntries' => $eligibleEntries,
            'preview' => $previewData,
        ]);
    }
}
