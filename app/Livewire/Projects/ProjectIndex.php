<?php

namespace App\Livewire\Projects;

use App\Enums\BillingType;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProjectIndex extends Component
{
    public bool $showCreateModal = false;
    public ?int $clientId = null;
    public string $name = '';
    public string $slug = '';
    public string $billingType = 'per_hour'; // per_hour, per_task, per_project, monthly
    public ?float $hourlyRate = 150.00;
    public ?float $fixedBudgetAmount = 5000.00;
    public ?float $monthlyRetainerAmount = 4000.00;
    public ?float $budgetHours = 40.00;
    public string $color = '#8b5cf6';
    public string $errorMessage = '';

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
        $this->clientId = Client::where('status', 'active')->value('id');
        $this->name = '';
        $this->slug = '';
        $this->billingType = 'per_hour';
        $this->hourlyRate = 150.00;
        $this->fixedBudgetAmount = 5000.00;
        $this->monthlyRetainerAmount = 4000.00;
        $this->budgetHours = 40.00;
        $this->color = '#8b5cf6';
        $this->errorMessage = '';
    }

    public function updatedName($value): void
    {
        $this->slug = Str::slug($value);
    }

    public function createProject(): void
    {
        $this->errorMessage = '';

        if (empty($this->clientId)) {
            $this->errorMessage = 'Please select a client.';
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Missing Client',
                'description' => 'Please select a client.',
            ]);
            return;
        }

        if (empty($this->name)) {
            $this->errorMessage = 'Project title is required.';
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Missing Title',
                'description' => 'Project title is required.',
            ]);
            return;
        }

        $slug = Str::slug($this->slug ?: $this->name);
        if (Project::where('slug', $slug)->exists()) {
            $this->errorMessage = "Slug '{$slug}' is already taken.";
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Slug Exists',
                'description' => $this->errorMessage,
            ]);
            return;
        }

        $billingTypeEnum = match ($this->billingType) {
            'per_hour', 'hourly' => BillingType::PerHour,
            'per_task', 'task' => BillingType::PerTask,
            'per_project', 'fixed' => BillingType::PerProject,
            'monthly', 'retainer' => BillingType::Monthly,
            default => BillingType::PerHour,
        };

        Project::create([
            'client_id' => $this->clientId,
            'name' => $this->name,
            'slug' => $slug,
            'billing_type' => $billingTypeEnum,
            'hourly_rate' => $this->billingType === 'per_hour' ? $this->hourlyRate : null,
            'fixed_budget_amount' => $this->billingType === 'per_project' ? $this->fixedBudgetAmount : null,
            'monthly_retainer_amount' => $this->billingType === 'monthly' ? $this->monthlyRetainerAmount : null,
            'budget_hours' => $this->budgetHours,
            'color' => $this->color,
            'status' => ProjectStatus::Active,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Project Created',
            'description' => "Project '{$this->name}' created successfully.",
        ]);
        $this->redirect("/projects/{$slug}", navigate: true);
    }

    public function render()
    {
        $projects = Project::with(['client', 'tasks', 'timeEntries'])
            ->orderBy('id', 'desc')
            ->get();

        $clients = Client::where('status', 'active')->orderBy('name')->get();

        return view('livewire.projects.project-index', [
            'projects' => $projects,
            'clients' => $clients,
        ]);
    }
}
