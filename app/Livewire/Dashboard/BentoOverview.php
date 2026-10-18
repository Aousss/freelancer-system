<?php

namespace App\Livewire\Dashboard;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Services\TimerStateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BentoOverview extends Component
{
    public float $weeklyTargetHours = 35.0;

    #[On('timesheet-updated')]
    public function refreshData(): void
    {
        // Triggers reactive re-render
    }

    public function resumeEntry(int $entryId, TimerStateService $timerService): void
    {
        $this->dispatch('resume-time-entry', entryId: $entryId);
    }

    public function startTaskTimer(int $projectId, int $taskId, string $title, ?string $gitRef): void
    {
        $this->dispatch('start-task-timer', projectId: $projectId, taskId: $taskId, description: $title, gitRef: $gitRef);
    }

    public function render()
    {
        $userId = Auth::id() ?? 1;
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);

        // 1. KPI: Hours Logged This Week
        $thisWeekSeconds = TimeEntry::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->sum('duration_seconds');
        $thisWeekHours = round($thisWeekSeconds / 3600, 1);
        $weekProgressPct = min(100, round(($thisWeekHours / $this->weeklyTargetHours) * 100));

        // 2. KPI: Unbilled Revenue Pot
        $unbilledRevenue = (float) TimeEntry::where('user_id', $userId)
            ->where('is_billable', true)
            ->where('is_invoiced', false)
            ->whereNotNull('ended_at')
            ->sum('billable_amount');

        // 3. KPI: Effective Hourly Rate (Total revenue earned / total hours logged)
        $totalBilledAndUnbilledRevenue = (float) TimeEntry::where('user_id', $userId)
            ->where('is_billable', true)
            ->whereNotNull('ended_at')
            ->sum('billable_amount');

        $allHours = max(0.1, round(TimeEntry::where('user_id', $userId)->whereNotNull('ended_at')->sum('duration_seconds') / 3600, 2));
        $effectiveHourlyRate = round($totalBilledAndUnbilledRevenue / $allHours, 2);

        // 4. KPI: Sprint Velocity (Completed this week + In Review)
        $completedThisWeek = Task::where('status', 'done')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();
        $inReviewCount = Task::where('status', 'review')->count();

        // Active Projects
        $activeProjects = Project::with(['client', 'timeEntries'])
            ->where('status', 'active')
            ->get();

        // Today's Time Entries
        $todayEntries = TimeEntry::with(['project.client', 'task', 'invoice'])
            ->where('user_id', $userId)
            ->whereDate('started_at', today())
            ->orderBy('started_at', 'desc')
            ->get();

        // Tasks in progress for quick start
        $inProgressTasks = Task::with('project')
            ->whereIn('status', ['in_progress', 'todo'])
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        return view('livewire.dashboard.bento-overview', [
            'thisWeekHours' => $thisWeekHours,
            'weekProgressPct' => $weekProgressPct,
            'unbilledRevenue' => $unbilledRevenue,
            'effectiveHourlyRate' => $effectiveHourlyRate,
            'completedThisWeek' => $completedThisWeek,
            'inReviewCount' => $inReviewCount,
            'activeProjects' => $activeProjects,
            'todayEntries' => $todayEntries,
            'inProgressTasks' => $inProgressTasks,
        ]);
    }
}
