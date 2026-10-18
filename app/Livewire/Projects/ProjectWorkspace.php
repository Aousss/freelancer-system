<?php

namespace App\Livewire\Projects;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProjectWorkspace extends Component
{
    public Project $project;

    // Task creation modal
    public bool $showTaskModal = false;
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskStatus = 'todo';
    public string $taskPriority = 'medium';
    public ?float $taskEstimatedHours = 4.0;
    public ?float $taskHourlyRate = null;
    public ?float $taskFixedAmount = null;
    public string $taskGitRef = '';
    public string $errorMessage = '';
    public string $successMessage = '';

    public function mount(string $slug): void
    {
        $this->project = Project::with(['client', 'tasks.timeEntries'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    #[On('timesheet-updated')]
    public function refreshWorkspace(): void
    {
        $this->project->refresh();
    }

    public function startTaskTimer(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $this->dispatch('start-task-timer',
            projectId: $this->project->id,
            taskId: $task->id,
            description: $task->title,
            gitRef: $task->git_ref
        );
        $this->successMessage = "Started timer for task '{$task->title}'";
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Task Timer Started',
            'description' => "Tracking time on '{$task->title}'",
        ]);
    }

    public function updateTaskStatus(int $taskId, string $newStatus): void
    {
        $task = Task::where('project_id', $this->project->id)->findOrFail($taskId);
        $task->update([
            'status' => TaskStatus::from($newStatus),
        ]);
        $this->project->refresh();
        $label = TaskStatus::from($newStatus)->label();
        $this->successMessage = "Moved task to {$label}";
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'Task Status Updated',
            'description' => "'{$task->title}' moved to {$label}",
        ]);
    }

    public function openTaskModal(string $defaultStatus = 'todo'): void
    {
        $this->showTaskModal = true;
        $this->taskStatus = $defaultStatus;
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskPriority = 'medium';
        $this->taskEstimatedHours = 4.0;
        $this->taskHourlyRate = null;
        $this->taskFixedAmount = $this->project->billing_type->value === 'per_task' ? 500.00 : null;
        $this->taskGitRef = '';
        $this->errorMessage = '';
    }

    public function saveTask(): void
    {
        $this->errorMessage = '';

        if (empty($this->taskTitle)) {
            $this->errorMessage = 'Task title cannot be empty.';
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Cannot Create Task',
                'description' => 'Task title cannot be empty.',
            ]);
            return;
        }

        $maxSort = Task::where('project_id', $this->project->id)
            ->where('status', $this->taskStatus)
            ->max('sort_order') ?? 0;

        Task::create([
            'project_id' => $this->project->id,
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'status' => TaskStatus::from($this->taskStatus),
            'priority' => $this->taskPriority,
            'estimated_hours' => $this->taskEstimatedHours,
            'hourly_rate' => $this->taskHourlyRate,
            'fixed_amount' => $this->taskFixedAmount,
            'git_ref' => $this->taskGitRef ?: null,
            'sort_order' => $maxSort + 1,
        ]);

        $createdTitle = $this->taskTitle;
        $createdStatus = TaskStatus::from($this->taskStatus)->label();
        $this->showTaskModal = false;
        $this->project->refresh();
        $this->successMessage = 'Task created.';
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Task Created',
            'description' => "'{$createdTitle}' added to {$createdStatus}.",
        ]);
    }

    public function render()
    {
        $tasks = $this->project->tasks()->with('timeEntries')->orderBy('sort_order')->get();

        $columns = [
            'backlog' => [
                'title' => 'Backlog',
                'status' => TaskStatus::Backlog,
                'tasks' => $tasks->where('status', TaskStatus::Backlog),
            ],
            'todo' => [
                'title' => 'To Do',
                'status' => TaskStatus::Todo,
                'tasks' => $tasks->where('status', TaskStatus::Todo),
            ],
            'in_progress' => [
                'title' => 'In Progress',
                'status' => TaskStatus::InProgress,
                'tasks' => $tasks->where('status', TaskStatus::InProgress),
            ],
            'review' => [
                'title' => 'In Review',
                'status' => TaskStatus::Review,
                'tasks' => $tasks->where('status', TaskStatus::Review),
            ],
            'done' => [
                'title' => 'Done',
                'status' => TaskStatus::Done,
                'tasks' => $tasks->where('status', TaskStatus::Done),
            ],
        ];

        $unbilledAmount = (float) TimeEntry::where('project_id', $this->project->id)
            ->where('is_billable', true)
            ->where('is_invoiced', false)
            ->whereNotNull('ended_at')
            ->sum('billable_amount');

        return view('livewire.projects.project-workspace', [
            'columns' => $columns,
            'unbilledAmount' => $unbilledAmount,
        ]);
    }
}
