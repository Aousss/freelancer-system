<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\RateResolverService;
use App\Services\TimeParserService;
use App\Services\TimerStateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $isOpen = false;

    public string $query = '';

    public string $feedbackMessage = '';

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->feedbackMessage = '';
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function executeCommand(
        TimerStateService $timerService,
        TimeParserService $timeParser,
        RateResolverService $rateResolver
    ): void {
        $raw = trim($this->query);
        if (empty($raw)) {
            return;
        }

        $userId = Auth::id() ?? 1;
        $parts = preg_split('/\s+/', $raw, 3);
        $command = strtolower($parts[0] ?? '');

        // 1. stop
        if ($command === 'stop') {
            $active = $timerService->getActiveTimer($userId);
            if ($active) {
                $timerService->stopTimer($active);
                $this->dispatch('timer-refresh');
                $this->dispatch('timesheet-updated');
                $this->dispatch('toast', [
                    'type' => 'success',
                    'message' => 'Timer Stopped',
                    'description' => 'Active timer stopped via Command Palette.',
                ]);
            } else {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => 'No Active Timer',
                    'description' => 'There was no timer currently running.',
                ]);
            }
            $this->close();

            return;
        }

        // 1b. pause
        if ($command === 'pause') {
            $active = $timerService->getActiveTimer($userId);
            if ($active && ! $active->is_paused) {
                $timerService->pauseTimer($active);
                $this->dispatch('timer-refresh');
                $this->dispatch('timesheet-updated');
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => 'Timer Paused',
                    'description' => 'Active timer paused.',
                ]);
            } else {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => 'Cannot Pause',
                    'description' => 'No running timer to pause.',
                ]);
            }
            $this->close();

            return;
        }

        // 1c. resume
        if ($command === 'resume') {
            $active = $timerService->getActiveTimer($userId);
            if ($active && $active->is_paused) {
                $timerService->resumeTimer($active);
                $this->dispatch('timer-refresh');
                $this->dispatch('timesheet-updated');
                $this->dispatch('toast', [
                    'type' => 'info',
                    'message' => 'Timer Resumed',
                    'description' => 'Timer counting resumed.',
                ]);
            } else {
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => 'Cannot Resume',
                    'description' => 'No paused timer to resume.',
                ]);
            }
            $this->close();

            return;
        }

        // 2. goto {dashboard|timesheet|projects|invoices|settings}
        if ($command === 'goto' && ! empty($parts[1])) {
            $destination = strtolower($parts[1]);
            $this->close();

            match ($destination) {
                'timesheet', 'time' => $this->redirect('/timesheet', navigate: true),
                'projects', 'proj' => $this->redirect('/projects', navigate: true),
                'invoices', 'inv' => $this->redirect('/invoices', navigate: true),
                'profile', 'me' => $this->redirect('/profile', navigate: true),
                'settings', 'pref', 'config', 'currency' => $this->redirect('/settings', navigate: true),
                default => $this->redirect('/', navigate: true),
            };

            return;
        }

        // 3. start {project_slug} {description}
        if ($command === 'start' && ! empty($parts[1])) {
            $slug = $parts[1];
            $desc = $parts[2] ?? 'Work logged via Command Palette';

            $project = Project::where('slug', $slug)
                ->orWhere('name', 'LIKE', "%{$slug}%")
                ->first();

            if ($project) {
                $timerService->startTimer([
                    'project_id' => $project->id,
                    'description' => $desc,
                    'is_billable' => true,
                ], $userId);

                $this->dispatch('timer-refresh');
                $this->dispatch('timesheet-updated');
                $this->dispatch('toast', [
                    'type' => 'success',
                    'message' => 'Timer Started',
                    'description' => "Tracking on {$project->name}",
                ]);
                $this->close();

                return;
            } else {
                $this->feedbackMessage = "Project '{$slug}' not found.";
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Project Not Found',
                    'description' => "Could not locate project matching '{$slug}'.",
                ]);

                return;
            }
        }

        // 4. log {duration} {project_slug} {description}
        if ($command === 'log' && ! empty($parts[1])) {
            $durationStr = $parts[1];
            $remainder = $parts[2] ?? '';
            $subParts = preg_split('/\s+/', $remainder, 2);
            $slug = $subParts[0] ?? '';
            $desc = $subParts[1] ?? 'Manual log via Command Palette';

            $seconds = $timeParser->parseToSeconds($durationStr);
            if ($seconds <= 0) {
                $this->feedbackMessage = "Invalid duration '{$durationStr}'. Use e.g. 1h30m, 45m, 1.5.";
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Invalid Duration',
                    'description' => "Could not parse '{$durationStr}'.",
                ]);

                return;
            }

            $project = Project::where('slug', $slug)
                ->orWhere('name', 'LIKE', "%{$slug}%")
                ->first();

            if (! $project) {
                $project = Project::first();
            }

            if ($project) {
                $rate = $rateResolver->resolve($project);
                $billableAmount = round(($seconds / 3600) * $rate, 2);

                TimeEntry::create([
                    'user_id' => $userId,
                    'project_id' => $project->id,
                    'description' => $desc,
                    'started_at' => now()->subSeconds($seconds),
                    'ended_at' => now(),
                    'duration_seconds' => $seconds,
                    'is_billable' => true,
                    'hourly_rate_snapshot' => $rate,
                    'billable_amount' => $billableAmount,
                    'is_invoiced' => false,
                ]);

                $this->dispatch('timesheet-updated');
                $this->dispatch('toast', [
                    'type' => 'success',
                    'message' => 'Time Logged',
                    'description' => "Logged {$durationStr} to {$project->name}.",
                ]);
                $this->close();

                return;
            }
        }

        // Default: If no command prefix, treat as start with default project
        $defaultProj = Project::first();
        if ($defaultProj) {
            $timerService->startTimer([
                'project_id' => $defaultProj->id,
                'description' => $raw,
                'is_billable' => true,
            ], $userId);

            $this->dispatch('timer-refresh');
            $this->dispatch('timesheet-updated');
            $this->close();
        }
    }

    public function render()
    {
        $projects = Project::where('status', 'active')->limit(6)->get();

        return view('livewire.command-palette', [
            'projects' => $projects,
        ]);
    }
}
