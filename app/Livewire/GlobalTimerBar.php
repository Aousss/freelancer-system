<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\TimerStateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalTimerBar extends Component
{
    public ?int $activeTimerId = null;
    public bool $isRunning = false;
    public bool $isPaused = false;
    public int $baseDurationSeconds = 0;
    public ?int $startedAtTimestamp = null;
    public string $description = '';
    public ?int $projectId = null;
    public ?int $taskId = null;
    public string $gitRef = '';
    public bool $isBillable = true;

    public function mount(TimerStateService $timerService): void
    {
        $this->refreshTimerState($timerService);
    }

    public function refreshTimerState(TimerStateService $timerService): void
    {
        $userId = Auth::id() ?? 1;
        $active = $timerService->getActiveTimer($userId);

        if ($active) {
            $this->activeTimerId = $active->id;
            $this->isRunning = true;
            $this->isPaused = (bool) $active->is_paused;
            $this->baseDurationSeconds = (int) $active->duration_seconds;
            $this->startedAtTimestamp = $active->started_at->getTimestamp() * 1000;
            $this->description = $active->description;
            $this->projectId = $active->project_id;
            $this->taskId = $active->task_id;
            $this->gitRef = $active->git_ref ?? '';
            $this->isBillable = (bool) $active->is_billable;
        } else {
            $this->activeTimerId = null;
            $this->isRunning = false;
            $this->isPaused = false;
            $this->baseDurationSeconds = 0;
            $this->startedAtTimestamp = null;
            if (!$this->projectId) {
                $this->projectId = Project::where('status', 'active')->value('id');
            }
        }
    }

    public function start(TimerStateService $timerService): void
    {
        if (empty($this->projectId)) {
            $this->projectId = Project::where('status', 'active')->value('id');
        }

        if (empty($this->description)) {
            $this->description = 'General development';
        }

        $userId = Auth::id() ?? 1;

        $entry = $timerService->startTimer([
            'project_id' => $this->projectId,
            'task_id' => $this->taskId,
            'description' => $this->description,
            'git_ref' => $this->gitRef ?: null,
            'is_billable' => $this->isBillable,
        ], $userId);

        $this->refreshTimerState($timerService);

        $projectName = Project::find($this->projectId)?->name ?? 'Project';
        $this->dispatch('timer-started', id: $entry->id);
        $this->dispatch('timesheet-updated');
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Timer Started',
            'description' => "Tracking on {$projectName}: {$this->description}",
        ]);
    }

    public function pause(TimerStateService $timerService): void
    {
        if ($this->activeTimerId) {
            $entry = TimeEntry::find($this->activeTimerId);
            if ($entry) {
                $timerService->pauseTimer($entry);
            }
        }

        $this->refreshTimerState($timerService);
        $this->dispatch('timer-paused');
        $this->dispatch('timesheet-updated');
        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => 'Timer Paused',
            'description' => 'Session paused. Click Resume when ready.',
        ]);
    }

    public function resume(TimerStateService $timerService): void
    {
        if ($this->activeTimerId) {
            $entry = TimeEntry::find($this->activeTimerId);
            if ($entry) {
                $timerService->resumeTimer($entry);
            }
        }

        $this->refreshTimerState($timerService);
        $this->dispatch('timer-resumed');
        $this->dispatch('timesheet-updated');
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'Timer Resumed',
            'description' => 'Session resumed and tracking actively.',
        ]);
    }

    public function stop(TimerStateService $timerService): void
    {
        if ($this->activeTimerId) {
            $entry = TimeEntry::find($this->activeTimerId);
            if ($entry) {
                $timerService->stopTimer($entry);
            }
        }

        $this->refreshTimerState($timerService);
        $this->description = '';
        $this->taskId = null;
        $this->gitRef = '';

        $this->dispatch('timer-stopped');
        $this->dispatch('timesheet-updated');
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Timer Stopped & Logged',
            'description' => 'Time entry logged to timesheet.',
        ]);
    }

    #[On('start-task-timer')]
    public function handleStartTaskTimer(int $projectId, ?int $taskId, string $description, ?string $gitRef, TimerStateService $timerService): void
    {
        $this->projectId = $projectId;
        $this->taskId = $taskId;
        $this->description = $description;
        $this->gitRef = $gitRef ?? '';
        $this->isBillable = true;

        $this->start($timerService);
    }

    #[On('resume-time-entry')]
    public function handleResumeTimeEntry(int $entryId, TimerStateService $timerService): void
    {
        $past = TimeEntry::find($entryId);
        if ($past) {
            $this->projectId = $past->project_id;
            $this->taskId = $past->task_id;
            $this->description = $past->description;
            $this->gitRef = $past->git_ref ?? '';
            $this->isBillable = (bool) $past->is_billable;

            $this->start($timerService);
        }
    }

    #[On('timer-refresh')]
    public function handleTimerRefresh(TimerStateService $timerService): void
    {
        $this->refreshTimerState($timerService);
    }

    public function render()
    {
        $projects = Project::where('status', 'active')->orderBy('name')->get();

        return view('livewire.global-timer-bar', [
            'projects' => $projects,
        ]);
    }
}
