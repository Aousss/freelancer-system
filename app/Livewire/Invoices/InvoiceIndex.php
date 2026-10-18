<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Services\InvoiceAggregatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class InvoiceIndex extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public string $successMessage = '';
    public string $errorMessage = '';

    // Historical invoice modal state
    public bool $showHistoricalModal = false;
    public ?int $historicalClientId = null;
    public ?int $historicalProjectId = null;
    public string $historicalInvoiceNumber = '';
    public string $historicalIssueDate = '';
    public string $historicalDueDate = '';
    public string $historicalStatus = 'paid';
    public string $historicalPaidAt = '';
    public string $historicalCurrency = 'MYR';
    public float $historicalTaxRate = 0.00;
    public string $historicalNotes = '';
    public array $historicalItems = [];

    public function openHistoricalModal(InvoiceAggregatorService $aggregator): void
    {
        $this->resetValidation();
        $this->errorMessage = '';

        $firstClient = Client::where('status', 'active')->first();
        $this->historicalClientId = $firstClient?->id;
        $this->historicalCurrency = $firstClient?->currency ?? \App\Models\Setting::get('currency', 'MYR');
        $this->historicalProjectId = null;

        // Auto-generate next suggested invoice number continuing from highest sequence
        $this->historicalInvoiceNumber = $aggregator->generateNextInvoiceNumber();

        $dueDays = (int) \App\Models\Setting::get('invoice_due_days', 14);
        $this->historicalIssueDate = now()->toDateString();
        $this->historicalDueDate = now()->addDays($dueDays)->toDateString();
        $this->historicalPaidAt = now()->toDateString();
        $this->historicalStatus = 'paid';
        $this->historicalTaxRate = (float) \App\Models\Setting::get('default_tax_rate', 0.00);
        $this->historicalNotes = 'Historical invoice recorded prior to FreelanceOS.';

        $this->historicalItems = [
            [
                'description' => '',
                'quantity_hours' => 1.00,
                'unit_rate' => 0.00,
            ],
        ];

        $this->showHistoricalModal = true;
    }

    public function closeHistoricalModal(): void
    {
        $this->showHistoricalModal = false;
    }

    public function updatedHistoricalClientId(): void
    {
        $client = Client::find($this->historicalClientId);
        if ($client) {
            $this->historicalCurrency = $client->currency ?? 'MYR';
        }
        $this->historicalProjectId = null;
    }

    public function addHistoricalItem(): void
    {
        $this->historicalItems[] = [
            'description' => '',
            'quantity_hours' => 1.00,
            'unit_rate' => 0.00,
        ];
    }

    public function removeHistoricalItem(int $index): void
    {
        if (count($this->historicalItems) > 1) {
            unset($this->historicalItems[$index]);
            $this->historicalItems = array_values($this->historicalItems);
        }
    }

    #[Computed]
    public function historicalSubtotal(): float
    {
        $sum = 0.00;
        foreach ($this->historicalItems as $item) {
            $sum += round(((float) ($item['quantity_hours'] ?? 0)) * ((float) ($item['unit_rate'] ?? 0)), 2);
        }
        return $sum;
    }

    #[Computed]
    public function historicalTaxAmount(): float
    {
        return round($this->historicalSubtotal * ($this->historicalTaxRate / 100), 2);
    }

    #[Computed]
    public function historicalTotal(): float
    {
        return $this->historicalSubtotal + $this->historicalTaxAmount;
    }

    public function saveHistoricalInvoice(): void
    {
        $this->validate([
            'historicalClientId' => ['required', 'integer', 'exists:clients,id'],
            'historicalProjectId' => ['nullable', 'integer', 'exists:projects,id'],
            'historicalInvoiceNumber' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number'],
            'historicalIssueDate' => ['required', 'date'],
            'historicalDueDate' => ['required', 'date'],
            'historicalStatus' => ['required', 'in:paid,sent,draft'],
            'historicalPaidAt' => ['nullable', 'date'],
            'historicalCurrency' => ['required', 'string', 'size:3'],
            'historicalTaxRate' => ['numeric', 'min:0', 'max:100'],
            'historicalItems' => ['required', 'array', 'min:1'],
            'historicalItems.*.description' => ['required', 'string', 'max:500'],
            'historicalItems.*.quantity_hours' => ['required', 'numeric', 'min:0.01'],
            'historicalItems.*.unit_rate' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () {
                $subtotal = $this->historicalSubtotal;
                $taxAmount = $this->historicalTaxAmount;
                $total = $this->historicalTotal;

                $paidAt = null;
                if ($this->historicalStatus === 'paid') {
                    $paidAt = !empty($this->historicalPaidAt) ? Carbon::parse($this->historicalPaidAt) : now();
                }

                $invoice = Invoice::create([
                    'client_id' => $this->historicalClientId,
                    'project_id' => $this->historicalProjectId,
                    'invoice_number' => trim($this->historicalInvoiceNumber),
                    'issue_date' => $this->historicalIssueDate,
                    'due_date' => $this->historicalDueDate,
                    'currency' => strtoupper(trim($this->historicalCurrency)),
                    'subtotal' => $subtotal,
                    'tax_rate' => $this->historicalTaxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'status' => $this->historicalStatus,
                    'notes' => $this->historicalNotes,
                    'paid_at' => $paidAt,
                ]);

                foreach ($this->historicalItems as $idx => $item) {
                    $amount = round(((float) $item['quantity_hours']) * ((float) $item['unit_rate']), 2);
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => trim($item['description']),
                        'quantity_hours' => (float) $item['quantity_hours'],
                        'unit_rate' => (float) $item['unit_rate'],
                        'amount' => $amount,
                        'sort_order' => $idx,
                    ]);
                }
            });

            $this->showHistoricalModal = false;
            $this->successMessage = "Historical Invoice {$this->historicalInvoiceNumber} saved! Future invoices will continue after this sequence.";
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Historical Invoice Saved',
                'description' => "Invoice #{$this->historicalInvoiceNumber} recorded.",
            ]);
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Failed to Record Invoice',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function markAsPaid(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
        $this->successMessage = "Invoice {$invoice->invoice_number} marked as Paid.";
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Invoice Paid',
            'description' => "Invoice #{$invoice->invoice_number} marked as Paid.",
        ]);
    }

    public function voidInvoice(int $invoiceId, InvoiceAggregatorService $aggregator): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        if ($invoice->status === InvoiceStatus::Paid) {
            $this->errorMessage = 'Cannot void an invoice that has already been marked as paid.';
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Cannot Void Invoice',
                'description' => $this->errorMessage,
            ]);
            return;
        }

        $invoice->update(['status' => InvoiceStatus::Void]);
        // Unlock associated time entries
        $aggregator->unlockInvoiceEntries($invoice);

        $this->successMessage = "Invoice {$invoice->invoice_number} voided. Associated time entries have been unlocked.";
        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => 'Invoice Voided',
            'description' => "Invoice #{$invoice->invoice_number} voided. Time entries unlocked.",
        ]);
    }

    public function deleteInvoice(int $invoiceId, InvoiceAggregatorService $aggregator): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoiceNumber = $invoice->invoice_number;

        // Unlock any linked time entries before deletion
        $aggregator->unlockInvoiceEntries($invoice);

        // Delete items and invoice
        $invoice->items()->delete();
        $invoice->delete();

        $this->successMessage = "Invoice {$invoiceNumber} was deleted.";
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'Invoice Deleted',
            'description' => "Invoice #{$invoiceNumber} permanently removed.",
        ]);
    }

    public function render()
    {
        $query = Invoice::with(['client', 'project', 'items'])->orderBy('issue_date', 'desc')->orderBy('id', 'desc');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $invoices = $query->paginate(10);

        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $historicalProjects = $this->historicalClientId
            ? Project::where('client_id', $this->historicalClientId)->where('status', 'active')->get()
            : collect();

        return view('livewire.invoices.invoice-index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'historicalProjects' => $historicalProjects,
        ]);
    }
}
