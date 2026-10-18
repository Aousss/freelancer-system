<?php

namespace Database\Seeders;

use App\Enums\BillingType;
use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoFreelanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or retrieve Developer User
        $user = User::firstOrCreate(
            ['email' => 'developer@freelanceos.test'],
            [
                'name' => 'Alex Rivers',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create Sanctum API Token for CLI
        $user->tokens()->delete();
        $token = $user->createToken('cli-companion-token');

        // 2. Create 2 Realistic Clients
        $clientAcme = Client::firstOrCreate(
            ['email' => 'sarah@acmecorp.com'],
            [
                'user_id' => $user->id,
                'name' => 'Acme Global Ventures',
                'contact_name' => 'Sarah Jenkins',
                'currency' => 'USD',
                'default_rate' => 135.00,
                'address' => "Acme Global Tower, Suite 400\n100 Tech Way, San Francisco, CA 94107",
                'status' => 'active',
            ]
        );

        $clientPinnacle = Client::firstOrCreate(
            ['email' => 'hafiz@pinnacletech.my'],
            [
                'user_id' => $user->id,
                'name' => 'Pinnacle Systems Malaysia',
                'contact_name' => 'Hafiz Rahman',
                'currency' => 'MYR',
                'default_rate' => 260.00,
                'address' => "Level 28, Menara Ilham\nNo 8, Jalan Binjai, 50450 Kuala Lumpur",
                'status' => 'active',
            ]
        );

        // 3. Create 4 Projects matching the 4 Flexible Rate Models:
        // 1 - Per Hour
        $projectPerHour = Project::firstOrCreate(
            ['slug' => 'pinnacle-cms'],
            [
                'client_id' => $clientPinnacle->id,
                'name' => 'Pinnacle Headless CMS Engine',
                'billing_type' => BillingType::PerHour,
                'hourly_rate' => 280.00,
                'budget_hours' => 60.00,
                'color' => '#8b5cf6', // Violet
                'status' => ProjectStatus::Active,
            ]
        );

        // 2 - Per Task
        $projectPerTask = Project::firstOrCreate(
            ['slug' => 'acme-portal'],
            [
                'client_id' => $clientAcme->id,
                'name' => 'Customer Self-Service Portal',
                'billing_type' => BillingType::PerTask,
                'hourly_rate' => 150.00,
                'budget_hours' => 80.00,
                'color' => '#3b82f6', // Blue
                'status' => ProjectStatus::Active,
            ]
        );

        // 3 - Per Project (Fixed Lump-Sum)
        $projectPerProject = Project::firstOrCreate(
            ['slug' => 'pinnacle-mobile'],
            [
                'client_id' => $clientPinnacle->id,
                'name' => 'Pinnacle iOS & Android Mobile Suite',
                'billing_type' => BillingType::PerProject,
                'fixed_budget_amount' => 18000.00,
                'budget_hours' => 120.00,
                'color' => '#10b981', // Emerald
                'status' => ProjectStatus::Active,
            ]
        );

        // 4 - Monthly Retainer
        $projectMonthly = Project::firstOrCreate(
            ['slug' => 'cloud-migration'],
            [
                'client_id' => $clientPinnacle->id,
                'name' => 'AWS Cloud Infrastructure Retainer',
                'billing_type' => BillingType::Monthly,
                'monthly_retainer_amount' => 6500.00,
                'budget_hours' => 40.00,
                'color' => '#f59e0b', // Amber
                'status' => ProjectStatus::Active,
            ]
        );

        // 4. Create Tasks across the 4 flexible projects
        // Tasks for 1-Per Hour (pinnacle-cms)
        $task1 = Task::create([
            'project_id' => $projectPerHour->id,
            'title' => 'Optimize MySQL composite indexes & EXPLAIN queries',
            'description' => 'Profile query slow logs on reporting endpoints and create compound indexes.',
            'status' => TaskStatus::InProgress,
            'priority' => 'high',
            'estimated_hours' => 6.00,
            'hourly_rate' => null,
            'fixed_amount' => null,
            'git_ref' => 'feat/db-index',
            'sort_order' => 1,
        ]);

        $task2 = Task::create([
            'project_id' => $projectPerHour->id,
            'title' => 'Implement Sanctum Rate Limiting & Auth',
            'description' => 'Configure Redis throttle headers and personal access token scopes.',
            'status' => TaskStatus::Done,
            'priority' => 'medium',
            'estimated_hours' => 4.50,
            'hourly_rate' => null,
            'fixed_amount' => null,
            'git_ref' => 'feat/sanctum-auth',
            'sort_order' => 2,
        ]);

        // Tasks for 2-Per Task (acme-portal) with individual Fixed Deliverable Fees
        $task5 = Task::create([
            'project_id' => $projectPerTask->id,
            'title' => 'Design High-Contrast Bento Dashboard UI',
            'description' => 'Build dark mode layout with Raycast/Linear aesthetics and micro-animations.',
            'status' => TaskStatus::Done,
            'priority' => 'high',
            'estimated_hours' => 16.00,
            'hourly_rate' => null,
            'fixed_amount' => 2400.00, // Fixed Task Price USD 2,400
            'git_ref' => 'feat/bento-ui',
            'sort_order' => 1,
        ]);

        $task6 = Task::create([
            'project_id' => $projectPerTask->id,
            'title' => 'Integrate Stripe Customer Portal & Webhook handlers',
            'description' => 'Handle customer subscription updates and invoice payment succeeded events.',
            'status' => TaskStatus::Review,
            'priority' => 'high',
            'estimated_hours' => 10.00,
            'hourly_rate' => null,
            'fixed_amount' => 1500.00, // Fixed Task Price USD 1,500
            'git_ref' => 'feat/stripe-billing',
            'sort_order' => 2,
        ]);

        $task7 = Task::create([
            'project_id' => $projectPerTask->id,
            'title' => 'Tenant DB connection resolver middleware',
            'description' => 'Dynamic schema switching per subdomain header.',
            'status' => TaskStatus::InProgress,
            'priority' => 'medium',
            'estimated_hours' => 14.00,
            'hourly_rate' => null,
            'fixed_amount' => 2000.00, // Fixed Task Price USD 2,000
            'git_ref' => 'feat/tenancy',
            'sort_order' => 3,
        ]);

        // Tasks for 3-Per Project (pinnacle-mobile)
        $task3 = Task::create([
            'project_id' => $projectPerProject->id,
            'title' => 'Setup Flutter Clean Architecture & State Layer',
            'description' => 'BLoC state management, dependency injection with get_it.',
            'status' => TaskStatus::Todo,
            'priority' => 'high',
            'estimated_hours' => 20.00,
            'hourly_rate' => null,
            'fixed_amount' => null,
            'git_ref' => 'mobile/scaffold',
            'sort_order' => 1,
        ]);

        // Tasks for 4-Monthly (cloud-migration)
        $task8 = Task::create([
            'project_id' => $projectMonthly->id,
            'title' => 'Terraform ECS Fargate task definitions & ALB',
            'description' => 'Zero-downtime rolling deployment pipeline.',
            'status' => TaskStatus::Todo,
            'priority' => 'high',
            'estimated_hours' => 18.00,
            'hourly_rate' => null,
            'fixed_amount' => null,
            'git_ref' => 'infra/ecs-cluster',
            'sort_order' => 1,
        ]);

        // 5. Create 1 Past Invoiced record for Acme Corp
        $invoiceAcme = Invoice::create([
            'client_id' => $clientAcme->id,
            'project_id' => $projectPerTask->id,
            'invoice_number' => 'INV-2026-001',
            'issue_date' => now()->subDays(12)->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'currency' => 'USD',
            'subtotal' => 2400.00,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'total' => 2400.00,
            'status' => InvoiceStatus::Sent,
            'notes' => 'Wire transfer to Chase Manhattan. Account: 9876-5432-1000.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoiceAcme->id,
            'description' => 'Task Deliverable: Design High-Contrast Bento Dashboard UI (feat/bento-ui)',
            'quantity_hours' => 16.00,
            'unit_rate' => 150.00,
            'amount' => 2400.00,
            'sort_order' => 0,
        ]);

        // Invoiced time entries
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerTask->id,
            'task_id' => $task5->id,
            'invoice_id' => $invoiceAcme->id,
            'description' => 'Design High-Contrast Bento Dashboard UI - Phase 1',
            'git_ref' => 'feat/bento-ui',
            'started_at' => now()->subDays(14)->setTime(9, 0),
            'ended_at' => now()->subDays(14)->setTime(17, 0),
            'duration_seconds' => 28800, // 8h
            'is_billable' => true,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 1200.00,
            'is_invoiced' => true,
        ]);

        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerTask->id,
            'task_id' => $task5->id,
            'invoice_id' => $invoiceAcme->id,
            'description' => 'Design High-Contrast Bento Dashboard UI - Polish & Micro-animations',
            'git_ref' => 'feat/bento-ui',
            'started_at' => now()->subDays(13)->setTime(9, 30),
            'ended_at' => now()->subDays(13)->setTime(17, 30),
            'duration_seconds' => 28800, // 8h
            'is_billable' => true,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 1200.00,
            'is_invoiced' => true,
        ]);

        // 6. Historical Unbilled Time Entries
        // Pinnacle CMS entries (1 - Per Hour)
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerHour->id,
            'task_id' => $task2->id,
            'invoice_id' => null,
            'description' => 'Setup Sanctum middleware and API route authentication',
            'git_ref' => 'feat/sanctum-auth',
            'started_at' => now()->subDays(3)->setTime(10, 0),
            'ended_at' => now()->subDays(3)->setTime(14, 30),
            'duration_seconds' => 16200, // 4.5h
            'is_billable' => true,
            'hourly_rate_snapshot' => 280.00,
            'billable_amount' => 1260.00,
            'is_invoiced' => false,
        ]);

        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerHour->id,
            'task_id' => $task1->id,
            'invoice_id' => null,
            'description' => 'Analyze EXPLAIN query plans for slow articles query',
            'git_ref' => 'feat/db-index',
            'started_at' => now()->subDays(2)->setTime(11, 0),
            'ended_at' => now()->subDays(2)->setTime(13, 0),
            'duration_seconds' => 7200, // 2h
            'is_billable' => true,
            'hourly_rate_snapshot' => 280.00,
            'billable_amount' => 560.00,
            'is_invoiced' => false,
        ]);

        // 2 - Per Task entries
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerTask->id,
            'task_id' => $task6->id,
            'invoice_id' => null,
            'description' => 'Draft Stripe webhook endpoint with signature verification',
            'git_ref' => 'feat/stripe-billing',
            'started_at' => now()->subDays(1)->setTime(9, 0),
            'ended_at' => now()->subDays(1)->setTime(14, 15),
            'duration_seconds' => 18900, // 5.25h
            'is_billable' => true,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 787.50,
            'is_invoiced' => false,
        ]);

        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerTask->id,
            'task_id' => $task7->id,
            'invoice_id' => null,
            'description' => 'Scaffold multi-tenancy schema switching tests',
            'git_ref' => 'feat/tenancy',
            'started_at' => now()->subDays(1)->setTime(15, 0),
            'ended_at' => now()->subDays(1)->setTime(18, 30),
            'duration_seconds' => 12600, // 3.5h
            'is_billable' => true,
            'hourly_rate_snapshot' => 150.00,
            'billable_amount' => 525.00,
            'is_invoiced' => false,
        ]);

        // 4 - Monthly Retainer entries
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectMonthly->id,
            'task_id' => $task8->id,
            'invoice_id' => null,
            'description' => 'Configure AWS provider and VPC peering subnets',
            'git_ref' => 'infra/ecs-cluster',
            'started_at' => now()->setTime(8, 30),
            'ended_at' => now()->setTime(11, 45),
            'duration_seconds' => 11700, // 3.25h
            'is_billable' => true,
            'hourly_rate_snapshot' => 300.00,
            'billable_amount' => 975.00,
            'is_invoiced' => false,
        ]);

        // 1 - Per Hour entry for today
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerHour->id,
            'task_id' => $task1->id,
            'invoice_id' => null,
            'description' => 'Add composite index on articles(published_at, category_id)',
            'git_ref' => 'feat/db-index',
            'started_at' => now()->setTime(12, 30),
            'ended_at' => now()->setTime(14, 0),
            'duration_seconds' => 5400, // 1.5h
            'is_billable' => true,
            'hourly_rate_snapshot' => 280.00,
            'billable_amount' => 420.00,
            'is_invoiced' => false,
        ]);

        // 1 Actively running timer entry right now!
        TimeEntry::create([
            'user_id' => $user->id,
            'project_id' => $projectPerHour->id,
            'task_id' => $task1->id,
            'invoice_id' => null,
            'description' => 'Optimize MySQL composite index benchmarks',
            'git_ref' => 'feat/db-index',
            'started_at' => now()->subMinutes(42),
            'ended_at' => null, // Active timer!
            'duration_seconds' => 0,
            'is_billable' => true,
            'hourly_rate_snapshot' => 280.00,
            'billable_amount' => 0.00,
            'is_invoiced' => false,
        ]);
    }
}
