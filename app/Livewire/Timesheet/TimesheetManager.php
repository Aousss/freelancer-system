<?php

namespace App\Livewire\Timesheet;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\RateResolverService;
use App\Services\TimeParserService;
use App\Services\TimerStateService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TimesheetManager extends Component
{
    use WithPagination;

    public ?int $filterProjectId = null;
    public string $filterBilling = 'all'; // all, unbilled, invoiced
    public string $search = '';

    // Modal state for manual time logging
    public bool $showManualModal = false;
    public ?int $manualProjectId = null;
    public string $manualDescription = '';
    public string $manualDuration = '1h 30m';
    public string $manualDate = '';
    public string $manualGitRef = '';
    public bool $manualIsBillable = true;
    public string $errorMessage = '';
    public string $successMessage = '';

    public function mount(): void
    {
        $this->manualDate = now()->toDateString();
        $this->manualProjectId = Project::where('status', 'active')->value('id');
    }

    #[On('timesheet-updated')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function resumeEntry(int $entryId, TimerStateService $timerService): void
    {
        $this->dispatch('resume-time-entry', entryId: $entryId);
        $this->successMessage = 'Resumed timer for task.';
    }

    public function openManualModal(): void
    {
        $this->showManualModal = true;
        $this->errorMessage = '';
        $this->manualDate = now()->toDateString();
    }

    public function closeManualModal(): void
    {
        $this->showManualModal = false;
        $this->errorMessage = '';
    }

    public function saveManualEntry(
        TimeParserService $timeParser,
        RateResolverService $rateResolver
    ): void {
        $this->errorMessage = '';

        if (empty($this->manualProjectId)) {
            $this->errorMessage = 'Please select a project.';
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Missing Project', 'description' => $this->errorMessage]);
            return;
        }

        if (empty($this->manualDescription)) {
            $this->errorMessage = 'Please provide a description.';
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Missing Description', 'description' => $this->errorMessage]);
            return;
        }

        $seconds = $timeParser->parseToSeconds($this->manualDuration);
        if ($seconds <= 0) {
            $this->errorMessage = "Could not parse duration '{$this->manualDuration}'. Use format e.g. 1h 30m, 45m, 1.5h.";
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Invalid Duration', 'description' => $this->errorMessage]);
            return;
        }

        $project = Project::with('client')->findOrFail($this->manualProjectId);
        $rate = $rateResolver->resolve($project);
        $billableAmount = $this->manualIsBillable ? round(($seconds / 3600) * $rate, 2) : 0.00;

        $startTime = now()->parse($this->manualDate . ' 09:00:00');
        $endTime = $startTime->copy()->addSeconds($seconds);

        TimeEntry::create([
            'user_id' => Auth::id() ?? 1,
            'project_id' => $project->id,
            'task_id' => null,
            'description' => $this->manualDescription,
            'git_ref' => $this->manualGitRef ?: null,
            'started_at' => $startTime,
            'ended_at' => $endTime,
            'duration_seconds' => $seconds,
            'is_billable' => $this->manualIsBillable,
            'hourly_rate_snapshot' => $rate,
            'billable_amount' => $billableAmount,
            'is_invoiced' => false,
        ]);

        $loggedDuration = $this->manualDuration;
        $this->manualDescription = '';
        $this->manualGitRef = '';
        $this->showManualModal = false;
        $this->successMessage = 'Manual time entry recorded successfully.';
        $this->dispatch('timesheet-updated');
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Time Log Saved',
            'description' => "Logged {$loggedDuration} on {$project->name}.",
        ]);
    }

    public function deleteEntry(int $entryId): void
    {
        try {
            $entry = TimeEntry::findOrFail($entryId);
            $entry->delete();
            $this->successMessage = 'Time entry removed.';
            $this->dispatch('toast', [
                'type' => 'info',
                'message' => 'Time Entry Deleted',
                'description' => 'Entry removed from timesheet.',
            ]);
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Cannot Delete Entry',
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $userId = Auth::id() ?? 1;

        $query = TimeEntry::with(['project.client', 'task', 'invoice'])
            ->where('user_id', $userId)
            ->orderBy('started_at', 'desc');

        if ($this->filterProjectId) {
            $query->where('project_id', $this->filterProjectId);
        }

        if ($this->filterBilling === 'unbilled') {
            $query->where('is_billable', true)->where('is_invoiced', false);
        } elseif ($this->filterBilling === 'invoiced') {
            $query->where('is_invoiced', true);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'LIKE', "%{$this->search}%")
                  ->orWhere('git_ref', 'LIKE', "%{$this->search}%");
            });
        }

        $entries = $query->paginate(15);
        $projects = Project::where('status', 'active')->orderBy('name')->get();

        return view('livewire.timesheet.timesheet-manager', [
            'entries' => $entries,
            'projects' => $projects,
        ]);
    }
}
