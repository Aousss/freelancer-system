# FreelanceOS — System Architecture & AI Agent Build Specification

## 1. Project Overview & Core Philosophy
**FreelanceOS** is a self-hosted, developer-centric Freelance Management System (FMS) designed for solo software engineers and technical consultants. It unifies **Client & Project Scoping**, **Git-Linked Task Management**, **Zero-Friction Time Tracking (Web + CLI)**, and **Automated PDF Invoicing** into a single source of truth.

### Primary Goals for the Coding Agent
1. **Zero-Friction Time Logging:** Starting, stopping, or resuming a timer must take no more than 1 click (or 1 keyboard shortcut via `Cmd/Ctrl + K`).
2. **Accounting Integrity:** Store all durations in integer seconds (`duration_seconds`), snapshot hourly rates onto every time entry (`hourly_rate_snapshot`), and strictly lock time entries (`is_invoiced = true`) once attached to an invoice.
3. **High-Contrast Bento Dark UI:** Implement a Linear/Raycast-inspired dark mode interface (`#090A0F` canvas, `#12141C` Bento cards, `1px` translucent borders, and `JetBrains Mono` tabular numerals for all timers and financial figures).

---

## 2. Recommended Tech Stack
- **Backend Framework:** Laravel 11.x (PHP 8.3+)
- **Database:** MySQL 8.0+ (or SQLite for rapid local setup)
- **Frontend Stack:** Blade + Livewire 3.x + Alpine.js + Tailwind CSS 3.4+
- **API Authentication (for CLI Companion):** Laravel Sanctum
- **PDF Generation:** `barryvdh/laravel-dompdf` or `spatie/laravel-pdf`
- **Icons:** Lucide Icons or Heroicons

---

## 3. Directory & File Structure Blueprint

```text
freelance-os/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── CheckIdleTimersCommand.php       # Flags timers running > 4 hours
│   ├── Enums/
│   │   ├── BillingType.php                      # hourly, fixed, retainer
│   │   ├── InvoiceStatus.php                    # draft, sent, paid, overdue, void
│   │   ├── ProjectStatus.php                    # active, paused, completed, archived
│   │   └── TaskStatus.php                       # backlog, todo, in_progress, review, done
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── CliTimerController.php       # Sanctum endpoints for terminal logging
│   │   │   └── InvoicePdfController.php         # Streams/downloads rendered A4 PDF
│   ├── Livewire/
│   │   ├── CommandPalette.php                   # Global Cmd+K fuzzy action modal
│   │   ├── Dashboard/
│   │   │   └── BentoOverview.php                # KPI cards, project burn bars, recent logs
│   │   ├── GlobalTimerBar.php                   # Sticky header stopwatch component
│   │   ├── Invoices/
│   │   │   ├── InvoiceIndex.php
│   │   │   └── SplitInvoiceBuilder.php          # Left config pane + Right live A4 preview
│   │   ├── Projects/
│   │   │   ├── ProjectIndex.php
│   │   │   └── ProjectWorkspace.php             # Burn-rate header + Kanban task board
│   │   └── Timesheet/
│   │       └── TimesheetManager.php             # Inline editable table + manual time parser
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Project.php
│   │   ├── Task.php
│   │   ├── TimeEntry.php
│   │   └── User.php
│   └── Services/
│       ├── InvoiceAggregatorService.php         # Groups unbilled entries & locks records
│       ├── RateResolverService.php              # Resolves Task -> Project -> Client rate
│       ├── TimeParserService.php                # Parses "1h 30m", "90m", "1.5" -> seconds
│       └── TimerStateService.php                # Atomic start/stop single-timer enforcement
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_10_16_000001_create_clients_table.php
│   │   ├── 2026_10_16_000002_create_projects_table.php
│   │   ├── 2026_10_16_000003_create_tasks_table.php
│   │   ├── 2026_10_16_000004_create_invoices_table.php
│   │   ├── 2026_10_16_000005_create_time_entries_table.php
│   │   └── 2026_10_16_000006_create_invoice_items_table.php
│   └── seeders/
│       └── DemoFreelanceSeeder.php              # Populates realistic clients, tasks, & logs
├── resources/
│   └── views/
│       ├── components/
│       │   ├── layouts/
│       │   │   └── app.blade.php                # Dark shell with Sidebar + GlobalTimerBar
│       │   ├── project-burn-meter.blade.php
│       │   ├── stat-card.blade.php
│       │   └── status-pill.blade.php
│       ├── livewire/
│       │   ├── command-palette.blade.php
│       │   ├── global-timer-bar.blade.php
│       │   ├── dashboard/bento-overview.blade.php
│       │   ├── invoices/split-invoice-builder.blade.php
│       │   ├── projects/project-workspace.blade.php
│       │   └── timesheet/timesheet-manager.blade.php
│       └── pdf/
│           └── invoice-template.blade.php       # Clean print-ready A4 invoice layout
└── routes/
    ├── api.php                                  # CLI timer routes
    └── web.php                                  # Livewire page routes
```

---

## 4. Database Schema Specification (DDL & Relationships)

The agent must create migrations adhering strictly to the following schema and foreign key order (`invoices` must be created before `time_entries` so `time_entries.invoice_id` can reference it).

### 4.1 `clients`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | FK (`users.id`), Cascade | Owner of the client record |
| `name` | `VARCHAR(255)` | Not Null | Company or client name |
| `contact_name` | `VARCHAR(255)` | Nullable | Primary point of contact |
| `email` | `VARCHAR(255)` | Nullable | Billing email address |
| `currency` | `CHAR(3)` | Default `'MYR'` | ISO currency code (`MYR`, `USD`, `SGD`) |
| `default_rate` | `DECIMAL(8,2)` | Default `0.00` | Default hourly rate for this client |
| `address` | `TEXT` | Nullable | Billing address for PDF invoices |
| `status` | `VARCHAR(30)` | Default `'active'` | `active`, `archived` |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

### 4.2 `projects`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `client_id` | `BIGINT UNSIGNED` | FK (`clients.id`), Cascade | Parent Client |
| `name` | `VARCHAR(255)` | Not Null | Project title |
| `slug` | `VARCHAR(255)` | Unique, Index | Used for CLI lookup (e.g., `pinnacle-cms`) |
| `billing_type` | `VARCHAR(30)` | Default `'hourly'` | `hourly`, `fixed`, `retainer` |
| `hourly_rate` | `DECIMAL(8,2)` | Nullable | Overrides `clients.default_rate` if set |
| `fixed_budget_amount`| `DECIMAL(10,2)`| Nullable | Used to calculate Effective Hourly Rate on fixed projects |
| `budget_hours` | `DECIMAL(6,2)` | Nullable | Total estimated/capped hours |
| `color` | `VARCHAR(20)` | Default `'#8b5cf6'`| Hex badge color for UI pills |
| `status` | `VARCHAR(30)` | Default `'active'` | `active`, `paused`, `completed`, `archived` |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

### 4.3 `tasks`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `project_id` | `BIGINT UNSIGNED` | FK (`projects.id`), Cascade | Parent Project |
| `title` | `VARCHAR(255)` | Not Null | Deliverable or ticket summary |
| `description` | `TEXT` | Nullable | Markdown notes or acceptance criteria |
| `status` | `VARCHAR(30)` | Default `'todo'` | `backlog`, `todo`, `in_progress`, `review`, `done` |
| `priority` | `VARCHAR(20)` | Default `'medium'` | `low`, `medium`, `high` |
| `estimated_hours` | `DECIMAL(5,2)` | Nullable | Estimated effort |
| `hourly_rate` | `DECIMAL(8,2)` | Nullable | Optional task-specific rate override |
| `git_ref` | `VARCHAR(255)` | Nullable | Branch name, PR number, or commit hash |
| `sort_order` | `INT` | Default `0` | Kanban column ordering |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

### 4.4 `invoices`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `client_id` | `BIGINT UNSIGNED` | FK (`clients.id`), Restrict | Billed Client |
| `project_id` | `BIGINT UNSIGNED` | FK (`projects.id`), NullOnDelete | Optional single project reference |
| `invoice_number` | `VARCHAR(50)` | Unique, Not Null | Format: `INV-YYYY-NNN` (e.g., `INV-2026-001`) |
| `issue_date` | `DATE` | Not Null | Date issued |
| `due_date` | `DATE` | Not Null | Payment due date |
| `currency` | `CHAR(3)` | Default `'MYR'` | Snapshot of client currency |
| `subtotal` | `DECIMAL(10,2)` | Default `0.00` | Sum of line items |
| `tax_rate` | `DECIMAL(5,2)` | Default `0.00` | Percentage (e.g., `8.00` for 8% SST) |
| `tax_amount` | `DECIMAL(10,2)` | Default `0.00` | Calculated tax amount |
| `total` | `DECIMAL(10,2)` | Default `0.00` | `subtotal + tax_amount` |
| `status` | `VARCHAR(30)` | Default `'draft'` | `draft`, `sent`, `paid`, `overdue`, `void` |
| `notes` | `TEXT` | Nullable | Bank details, payment terms |
| `paid_at` | `TIMESTAMP` | Nullable | Timestamp when marked paid |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

### 4.5 `time_entries`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | FK (`users.id`), Cascade | User who logged the time |
| `project_id` | `BIGINT UNSIGNED` | FK (`projects.id`), Cascade | Associated Project |
| `task_id` | `BIGINT UNSIGNED` | FK (`tasks.id`), NullOnDelete | Optional associated Task |
| `invoice_id` | `BIGINT UNSIGNED` | FK (`invoices.id`), NullOnDelete| Populated when locked into an invoice |
| `description` | `VARCHAR(500)` | Not Null | Work log / commit summary |
| `git_ref` | `VARCHAR(100)` | Nullable | Commit hash or branch tag |
| `started_at` | `TIMESTAMP` | Not Null, Index | Start timestamp (UTC) |
| `ended_at` | `TIMESTAMP` | Nullable, Index | `NULL` indicates timer is actively running |
| `duration_seconds` | `INT UNSIGNED` | Default `0` | Exact elapsed seconds (`0` while running) |
| `is_billable` | `BOOLEAN` | Default `true` | Whether entry is billable to client |
| `hourly_rate_snapshot`| `DECIMAL(8,2)` | Not Null | Resolved hourly rate at creation/stop time |
| `billable_amount` | `DECIMAL(10,2)` | Default `0.00` | `ROUND((duration_seconds / 3600) * hourly_rate_snapshot, 2)` |
| `is_invoiced` | `BOOLEAN` | Default `false`, Index| `true` locks row from editing/deletion |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

### 4.6 `invoice_items`
| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | PK, Auto Increment | Primary Key |
| `invoice_id` | `BIGINT UNSIGNED` | FK (`invoices.id`), Cascade | Parent Invoice |
| `description` | `VARCHAR(500)` | Not Null | Line item text |
| `quantity_hours` | `DECIMAL(6,2)` | Not Null | Decimal hours (e.g., `2.50`) |
| `unit_rate` | `DECIMAL(8,2)` | Not Null | Hourly rate or fixed fee |
| `amount` | `DECIMAL(10,2)` | Not Null | `ROUND(quantity_hours * unit_rate, 2)` |
| `sort_order` | `INT` | Default `0` | Display order on PDF |
| `timestamps` | `TIMESTAMP` | Nullable | `created_at`, `updated_at` |

---

## 5. Core Business Logic & Service Specifications

### 5.1 `RateResolverService`
Resolves the applicable hourly rate in strict order of specificity whenever a `TimeEntry` is created or updated:
1. If `$task?->hourly_rate !== null`, return `$task->hourly_rate`.
2. Else if `$project->hourly_rate !== null`, return `$project->hourly_rate`.
3. Else return `$project->client->default_rate`.

### 5.2 `TimerStateService` (Single Active Timer Enforcement)
A user can only have **one** active timer (`ended_at IS NULL`) at any given time.
- **`startTimer(array $data): TimeEntry`**
  1. Run inside a DB transaction (`DB::transaction`).
  2. Query `TimeEntry::where('user_id', $userId)->whereNull('ended_at')->lockForUpdate()->get()`.
  3. For each running entry found, invoke `stopTimer($runningEntry)`.
  4. Resolve rate via `RateResolverService`.
  5. Create and return the new `TimeEntry` with `started_at = now()`, `ended_at = null`, `duration_seconds = 0`.
- **`stopTimer(TimeEntry $entry): TimeEntry`**
  1. Set `$endedAt = now()`.
  2. Calculate `$seconds = max(1, $entry->started_at->diffInSeconds($endedAt))`.
  3. Calculate `$billableAmount = $entry->is_billable ? round(($seconds / 3600) * $entry->hourly_rate_snapshot, 2) : 0.00`.
  4. Update `$entry` with `ended_at`, `duration_seconds`, and `billable_amount`.
- **`resumeEntry(TimeEntry $pastEntry): TimeEntry`**
  1. Calls `startTimer()` copying `project_id`, `task_id`, `description`, `git_ref`, and `is_billable` from `$pastEntry`.

### 5.3 `TimeParserService` (Natural Language Duration Input)
Allows users to type human-friendly durations into manual timesheet fields and converts them into integer seconds (`int`):
- `"1h 30m"` or `"1h30m"` $\rightarrow$ `5400`
- `"45m"` $\rightarrow$ `2700`
- `"02:15"` (`HH:MM`) $\rightarrow$ `8100`
- `"1.75"` or `"1.75h"` (Decimal hours) $\rightarrow$ `6300`

### 5.4 `InvoiceAggregatorService`
Handles converting unbilled `TimeEntry` records into an `Invoice` with `InvoiceItem` rows:
- **Input Parameters:** `client_id`, `project_id` (optional), `start_date`, `end_date`, `selected_time_entry_ids[]`, `aggregation_mode` (`detailed` | `grouped_by_task` | `single_line`), `tax_rate`.
- **Execution Steps (Transactional):**
  1. Fetch all `TimeEntry` records matching `selected_time_entry_ids` where `is_invoiced = false` and `ended_at IS NOT NULL`.
  2. Generate next sequential `invoice_number` (`INV-YYYY-NNN`).
  3. Create the `Invoice` header record.
  4. Transform entries into `InvoiceItem` records based on `aggregation_mode`:
     - **`detailed`:** 1 `TimeEntry` = 1 `InvoiceItem`. Description format: `"[YYYY-MM-DD] {description} ({git_ref})"`.
     - **`grouped_by_task`:** Group entries by `task_id` (or `description` if `task_id` is null) and `hourly_rate_snapshot`. Sum `duration_seconds`, convert to `ROUND(sum_seconds / 3600, 2)` for `quantity_hours`.
     - **`single_line`:** Group all entries by `hourly_rate_snapshot` into a single summary line: `"Software Engineering & Consulting Services ({start_date} to {end_date})"`.
  5. Compute `subtotal`, `tax_amount`, and `total` on the `Invoice`.
  6. Update all included `TimeEntry` rows: set `invoice_id = $invoice->id` and `is_invoiced = true`.
- **Unlocking on Void/Delete:**
  - If an `Invoice` in `draft` or `void` status is deleted or voided, run `TimeEntry::where('invoice_id', $invoice->id)->update(['invoice_id' => null, 'is_invoiced' => false])`.

### 5.5 Model Protection Rules (`TimeEntry::booted()`)
- Prevent updating or deleting any `TimeEntry` where `$entry->getOriginal('is_invoiced') === true`, throwing a `DomainException('Locked invoiced time entries cannot be modified.')` unless the operation is explicitly detaching a voided invoice.

---

## 6. UI/UX & Component Architecture Specification

### 6.1 Color Tokens & Typography
Configure `tailwind.config.js` with the following aesthetics:
- **Fonts:**
  - Sans: `'Inter', sans-serif`
  - Mono: `'JetBrains Mono', monospace` (Always pair with `tabular-nums` for timers, durations, percentages, and currency).
- **Surface Hierarchy:**
  - App Background Canvas: `bg-[#090A0F]`
  - Sidebar & Header Surface: `bg-[#0D0F17]`
  - Bento Card Surface: `bg-[#12141C]`
  - Elevated / Inner Card Surface: `bg-[#171A24]`
  - Standard Border: `border-white/[0.07]`
- **Semantic Status Pills:**
  - **Emerald (Active / Billable / On Track):** `bg-emerald-500/10 text-emerald-400 border border-emerald-500/20`
  - **Violet (Primary Accent / Project Tag):** `bg-violet-500/10 text-violet-300 border border-violet-500/20`
  - **Amber (Unbilled / Near Budget Cap 75%-95%):** `bg-amber-500/10 text-amber-400 border border-amber-500/20`
  - **Rose (Stop Timer / Over Budget >95% / Overdue):** `bg-rose-500/10 text-rose-400 border border-rose-500/20`

### 6.2 Key Interactive Components

#### A. `<livewire:global-timer-bar />`
- Mounted persistently at the top of `layouts/app.blade.php`.
- **Idle State:** Shows task description input, project selector dropdown, `$` billable toggle, `00:00:00` counter, and an Emerald `▶ Start` button.
- **Running State:**
  - Input container receives `border-emerald-500/30 shadow-[0_0_25px_-5px_rgba(16,185,129,0.15)]` and a pulsing emerald dot (`animate-ping`).
  - Alpine.js `setInterval` calculates `Math.floor((Date.now() - startedAtTimestamp) / 1000)` every second so the timer never drifts even if the browser tab sleeps.
  - Dynamically updates `document.title` to `"▶ 01:24:15 — {Task Description} | FreelanceOS"`.
  - Action button switches to Rose `■ Stop`.

#### B. `<livewire:dashboard.bento-overview />`
- **Row 1 (4 KPI Cards):**
  1. *Hours Logged (This Week)* vs weekly target (default `35.0h`).
  2. *Unbilled Revenue Pot* (Sum of `billable_amount` where `is_billable = true` and `is_invoiced = false`).
  3. *Effective Hourly Rate* (Total billed + unbilled revenue divided by total hours logged across both hourly and fixed projects).
  4. *Sprint Velocity* (Tasks completed this week + count in review).
- **Row 2 (Project Burn-Rate & Quick Actions):**
  - Displays active projects with horizontal progress bars (`logged_hours / budget_hours`).
  - Progress bar color shifts dynamically: `< 75%` Violet, `75% - 95%` Amber, `> 95%` Rose.
- **Row 3 (Today's Timesheet Table):**
  - Lists today's logs with inline editing, a `↻` Resume button on each row, and `🔒 INV-XXXX` lock badges on invoiced rows.

#### C. `<livewire:invoices.split-invoice-builder />`
- **Left Pane (Controls):** Client dropdown, date range pickers, segmented radio button for Aggregation Mode (`Detailed`, `Grouped by Task`, `Single Summary`), and a reactive checklist of unbilled time entries.
- **Right Pane (Live A4 Preview):** Renders `resources/views/pdf/invoice-template.blade.php` inside an aspect-ratio A4 container that updates immediately as checkboxes or aggregation modes are toggled.

#### D. `<livewire:command-palette />` (`Cmd/Ctrl + K`)
- Alpine.js + Livewire modal triggered via `@keydown.window.prevent.cmd.k` / `@keydown.window.prevent.ctrl.k`.
- Supports quick commands:
  - `start {project_slug} {description}` $\rightarrow$ Starts timer immediately.
  - `stop` $\rightarrow$ Stops current running timer.
  - `log {duration} {project_slug} {description}` $\rightarrow$ Creates completed manual time entry.
  - `goto {dashboard|timesheet|projects|invoices}` $\rightarrow$ Navigates to route.

---

## 7. CLI / Terminal Companion API Contract

Expose Laravel Sanctum-protected endpoints in `routes/api.php` so the freelancer can control timers from their terminal or Git hooks:

### `GET /api/timer/status`
- **Response (`200 OK`):**
```json
{
  "running": true,
  "entry": {
    "id": 42,
    "description": "Optimize MySQL composite index",
    "project": "pinnacle-cms",
    "git_ref": "feat/db-index",
    "started_at": "2026-10-16T01:00:00Z",
    "elapsed_seconds": 5412,
    "formatted": "01:30:12"
  }
}
```

### `POST /api/timer/start`
- **Payload:**
```json
{
  "project_slug": "pinnacle-cms",
  "description": "Implement Sanctum rate limiting",
  "git_ref": "feat/sanctum-auth",
  "is_billable": true
}
```
- **Action:** Stops any existing timer and starts a new `TimeEntry`.

### `POST /api/timer/stop`
- **Payload (Optional):**
```json
{
  "description": "Optional override description (e.g., from latest git commit message)",
  "git_ref": "a1b2c3d"
}
```
- **Action:** Stops the currently running timer and returns calculated duration and billable total.

---

## 8. Step-by-Step Execution Plan for the AI Coding Agent

When instructing the AI agent to build this project, execute in the following milestone order:

1. **Step 1 — Scaffold & Database Layer:**
   - Initialize Laravel 11 + Livewire 3 + Tailwind CSS.
   - Create all 4 Enums (`BillingType`, `ProjectStatus`, `TaskStatus`, `InvoiceStatus`).
   - Create all 6 Migrations (`clients`, `projects`, `tasks`, `invoices`, `time_entries`, `invoice_items`) and Eloquent Models with relationships and scopes (`scopeUnbilled`, `scopeRunning`).
   - Run `DemoFreelanceSeeder` to populate 2 clients, 3 projects, 8 tasks, and 15 historical/active time entries.

2. **Step 2 — Core Domain Services & Unit Tests:**
   - Implement `RateResolverService`, `TimeParserService`, `TimerStateService`, and `InvoiceAggregatorService`.
   - Write Pest/PHPUnit tests verifying:
     - Starting a second timer automatically stops the first timer.
     - Rate resolution follows `Task -> Project -> Client` hierarchy.
     - Locked (`is_invoiced = true`) time entries cannot be edited or deleted.

3. **Step 3 — App Shell, Global Timer Bar & Bento Dashboard:**
   - Build `components/layouts/app.blade.php` using the `#090A0F` Bento dark theme.
   - Build `<livewire:global-timer-bar />` with live Alpine.js second ticking and browser `<title>` synchronization.
   - Build `<livewire:dashboard.bento-overview />` with the 4 KPI cards, project budget burn meters, and today's inline log table.

4. **Step 4 — Timesheet Manager & Project Kanban Workspace:**
   - Build `<livewire:timesheet.timesheet-manager />` supporting inline cell edits, shorthand duration parsing (`1h 30m`), and one-click `↻` task resume.
   - Build `<livewire:projects.project-workspace />` featuring the Kanban board where clicking `▶` on any task card starts the global timer linked to that task.

5. **Step 5 — Split-Screen Invoice Builder & CLI API:**
   - Build `<livewire:invoices.split-invoice-builder />` with real-time A4 preview and PDF download via `InvoicePdfController`.
   - Implement `CliTimerController` API endpoints and `Cmd/Ctrl + K` `<livewire:command-palette />`.