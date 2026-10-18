<?php

namespace App\Services;

use App\Enums\BillingType;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceAggregatorService
{
    /**
     * Aggregates selected unbilled time entries into an invoice and locks the entries.
     */
    public function generateInvoice(array $params): Invoice
    {
        $clientId = $params['client_id'];
        $projectId = $params['project_id'] ?? null;
        $entryIds = $params['selected_time_entry_ids'] ?? [];
        $aggregationMode = $params['aggregation_mode'] ?? 'detailed';
        $taxRate = (float) ($params['tax_rate'] ?? 0.00);
        $issueDate = !empty($params['issue_date']) ? Carbon::parse($params['issue_date']) : now()->toDateString();
        $dueDate = !empty($params['due_date']) ? Carbon::parse($params['due_date']) : now()->addDays(14)->toDateString();
        $notes = $params['notes'] ?? 'Payment due within 14 days of issue.';

        if (empty($entryIds)) {
            throw new InvalidArgumentException('At least one time entry must be selected to generate an invoice.');
        }

        return DB::transaction(function () use (
            $clientId,
            $projectId,
            $entryIds,
            $aggregationMode,
            $taxRate,
            $issueDate,
            $dueDate,
            $notes
        ) {
            $client = Client::findOrFail($clientId);

            $entries = TimeEntry::with(['task', 'project'])
                ->whereIn('id', $entryIds)
                ->where('is_invoiced', false)
                ->whereNotNull('ended_at')
                ->get();

            if ($entries->isEmpty()) {
                throw new InvalidArgumentException('No eligible unbilled time entries found.');
            }

            $invoiceNumber = $this->generateNextInvoiceNumber();

            $invoice = Invoice::create([
                'client_id' => $client->id,
                'project_id' => $projectId,
                'invoice_number' => $invoiceNumber,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'currency' => $client->currency ?? 'MYR',
                'subtotal' => 0.00,
                'tax_rate' => $taxRate,
                'tax_amount' => 0.00,
                'total' => 0.00,
                'status' => InvoiceStatus::Draft,
                'notes' => $notes,
            ]);

            $lineItems = $this->buildLineItems($entries, $aggregationMode, $invoice);

            $subtotal = 0.00;
            $sortOrder = 0;
            foreach ($lineItems as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['sort_order'] = $sortOrder++;
                $invoiceItem = InvoiceItem::create($itemData);
                $subtotal += (float) $invoiceItem->amount;
            }

            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total = $subtotal + $taxAmount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            // Lock time entries
            TimeEntry::whereIn('id', $entries->pluck('id'))->update([
                'invoice_id' => $invoice->id,
                'is_invoiced' => true,
            ]);

            return $invoice->load(['items', 'client', 'project']);
        });
    }

    /**
     * Unlock time entries when an invoice is voided or deleted.
     */
    public function unlockInvoiceEntries(Invoice $invoice): void
    {
        TimeEntry::where('invoice_id', $invoice->id)->update([
            'invoice_id' => null,
            'is_invoiced' => false,
        ]);
    }

    /**
     * Generate sequential invoice number: INV-YYYY-NNN continuing from highest sequence
     */
    public function generateNextInvoiceNumber(?string $customPrefix = null): string
    {
        $year = now()->format('Y');
        $prefix = $customPrefix ?? "INV-{$year}-";

        // Find all invoice numbers matching prefix
        $invoiceNumbers = Invoice::where('invoice_number', 'LIKE', "{$prefix}%")
            ->pluck('invoice_number');

        $maxSequence = 0;
        foreach ($invoiceNumbers as $number) {
            if (preg_match('/(\d+)$/', $number, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSequence) {
                    $maxSequence = $seq;
                }
            }
        }

        // If no invoices exist for current year prefix, check all INV-YYYY-NNN invoices
        if ($maxSequence === 0) {
            $allInvoices = Invoice::pluck('invoice_number');
            foreach ($allInvoices as $number) {
                if (preg_match('/INV-\d{4}-(\d+)/', $number, $matches)) {
                    $seq = (int) $matches[1];
                    if ($seq > $maxSequence) {
                        $maxSequence = $seq;
                    }
                }
            }
        }

        $nextSequence = $maxSequence + 1;

        return sprintf('%s%03d', $prefix, $nextSequence);
    }

    /**
     * Build invoice line items based on chosen aggregation mode & flexible rate model
     */
    protected function buildLineItems($entries, string $mode, Invoice $invoice): array
    {
        $items = [];

        if ($mode === 'detailed') {
            foreach ($entries as $entry) {
                $hours = round($entry->duration_seconds / 3600, 2);
                $unitRate = (float) $entry->hourly_rate_snapshot;
                $amount = round($hours * $unitRate, 2);

                $dateStr = $entry->started_at->format('Y-m-d');
                $gitSuffix = $entry->git_ref ? " ({$entry->git_ref})" : '';
                $taskPrefix = $entry->task ? "[{$entry->task->title}] " : '';
                $description = "[{$dateStr}] {$taskPrefix}{$entry->description}{$gitSuffix}";

                $items[] = [
                    'description' => $description,
                    'quantity_hours' => max(0.01, $hours),
                    'unit_rate' => $unitRate,
                    'amount' => $amount,
                ];
            }
        } elseif ($mode === 'grouped_by_task') {
            $grouped = $entries->groupBy(function ($entry) {
                $groupKey = $entry->task_id ? 'task_' . $entry->task_id : 'desc_' . md5($entry->description);
                return $groupKey . '_' . $entry->hourly_rate_snapshot;
            });

            foreach ($grouped as $group) {
                $first = $group->first();
                $totalSeconds = $group->sum('duration_seconds');
                $hours = round($totalSeconds / 3600, 2);

                // If task has a fixed deliverable price (2 - Per Task)
                if ($first->task && !is_null($first->task->fixed_amount) && (float) $first->task->fixed_amount > 0) {
                    $unitRate = (float) $first->task->fixed_amount;
                    $amount = $unitRate;
                    $quantityHours = 1.00;
                    $description = "Task Deliverable: {$first->task->title} (" . $group->count() . " logs, {$hours}h tracked)";
                } else {
                    $unitRate = (float) $first->hourly_rate_snapshot;
                    $amount = round($hours * $unitRate, 2);
                    $quantityHours = max(0.01, $hours);
                    $description = $first->task ? "Task: {$first->task->title} (" . $group->count() . " log entries)" : "{$first->description} (" . $group->count() . " log entries)";
                }

                $items[] = [
                    'description' => $description,
                    'quantity_hours' => $quantityHours,
                    'unit_rate' => $unitRate,
                    'amount' => $amount,
                ];
            }
        } else {
            // single_line summary
            $startDate = $entries->min('started_at')->format('M j, Y');
            $endDate = $entries->max('started_at')->format('M j, Y');

            $firstProject = $entries->first()->project ?? null;

            // 4 - Monthly Retainer
            if ($firstProject && $firstProject->billing_type === BillingType::Monthly && $firstProject->monthly_retainer_amount) {
                $amount = (float) $firstProject->monthly_retainer_amount;
                $items[] = [
                    'description' => "Monthly Retainer Services: {$firstProject->name} ({$startDate} to {$endDate})",
                    'quantity_hours' => 1.00,
                    'unit_rate' => $amount,
                    'amount' => $amount,
                ];
            } else {
                $groupedByRate = $entries->groupBy('hourly_rate_snapshot');

                foreach ($groupedByRate as $rate => $group) {
                    $totalSeconds = $group->sum('duration_seconds');
                    $hours = round($totalSeconds / 3600, 2);
                    $unitRate = (float) $rate;
                    $amount = round($hours * $unitRate, 2);

                    $description = "Software Engineering & Consulting Services ({$startDate} to {$endDate})";

                    $items[] = [
                        'description' => $description,
                        'quantity_hours' => max(0.01, $hours),
                        'unit_rate' => $unitRate,
                        'amount' => $amount,
                    ];
                }
            }
        }

        return $items;
    }
}
