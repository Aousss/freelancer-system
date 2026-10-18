<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimerStateService
{
    public function __construct(
        protected RateResolverService $rateResolver
    ) {}

    /**
     * Start a new timer atomically, automatically stopping any currently running timer for the user.
     */
    public function startTimer(array $data, ?int $userId = null): TimeEntry
    {
        $userId = $userId ?? Auth::id() ?? 1;

        return DB::transaction(function () use ($data, $userId) {
            // Find and stop any currently active timers for this user
            $activeEntries = TimeEntry::where('user_id', $userId)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->get();

            foreach ($activeEntries as $activeEntry) {
                $this->stopTimer($activeEntry);
            }

            $project = Project::with('client')->findOrFail($data['project_id']);
            $task = !empty($data['task_id']) ? Task::find($data['task_id']) : null;

            $rate = $this->rateResolver->resolve($project, $task);
            $isBillable = array_key_exists('is_billable', $data) ? (bool) $data['is_billable'] : true;

            return TimeEntry::create([
                'user_id' => $userId,
                'project_id' => $project->id,
                'task_id' => $task?->id,
                'description' => $data['description'] ?? 'Work in progress',
                'git_ref' => $data['git_ref'] ?? null,
                'started_at' => now(),
                'ended_at' => null,
                'duration_seconds' => 0,
                'is_billable' => $isBillable,
                'is_paused' => false,
                'hourly_rate_snapshot' => $rate,
                'billable_amount' => 0.00,
                'is_invoiced' => false,
            ]);
        });
    }

    /**
     * Pause an actively running timer, snapshotting elapsed seconds into duration_seconds.
     */
    public function pauseTimer(TimeEntry $entry): TimeEntry
    {
        if ($entry->ended_at !== null || $entry->is_paused) {
            return $entry;
        }

        $additional = max(0, $entry->started_at->diffInSeconds(now()));
        $totalSeconds = (int) $entry->duration_seconds + $additional;

        $entry->update([
            'duration_seconds' => $totalSeconds,
            'is_paused' => true,
        ]);

        return $entry->fresh();
    }

    /**
     * Resume a paused timer, resetting the current slice's started_at to now.
     */
    public function resumeTimer(TimeEntry $entry): TimeEntry
    {
        if ($entry->ended_at !== null || !$entry->is_paused) {
            return $entry;
        }

        $entry->update([
            'started_at' => now(),
            'is_paused' => false,
        ]);

        return $entry->fresh();
    }

    /**
     * Stop a running or paused timer and record its elapsed seconds and billable amount.
     */
    public function stopTimer(TimeEntry $entry): TimeEntry
    {
        if ($entry->ended_at !== null) {
            return $entry;
        }

        $endedAt = now();

        if ($entry->is_paused) {
            $seconds = max(1, (int) $entry->duration_seconds);
        } else {
            $additional = max(0, $entry->started_at->diffInSeconds($endedAt));
            $seconds = max(1, (int) $entry->duration_seconds + $additional);
        }

        $billableAmount = $entry->is_billable
            ? round(($seconds / 3600) * (float) $entry->hourly_rate_snapshot, 2)
            : 0.00;

        $entry->update([
            'ended_at' => $endedAt,
            'is_paused' => false,
            'duration_seconds' => $seconds,
            'billable_amount' => $billableAmount,
        ]);

        return $entry->fresh();
    }

    /**
     * Resume a past time entry by starting a new timer with identical metadata.
     */
    public function resumeEntry(TimeEntry $pastEntry): TimeEntry
    {
        return $this->startTimer([
            'project_id' => $pastEntry->project_id,
            'task_id' => $pastEntry->task_id,
            'description' => $pastEntry->description,
            'git_ref' => $pastEntry->git_ref,
            'is_billable' => $pastEntry->is_billable,
        ], $pastEntry->user_id);
    }

    /**
     * Get the user's currently active timer if any exists.
     */
    public function getActiveTimer(?int $userId = null): ?TimeEntry
    {
        $userId = $userId ?? Auth::id() ?? 1;

        return TimeEntry::with(['project.client', 'task'])
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }
}
