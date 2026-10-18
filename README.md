# FreelanceOS ⚡

> A distraction-free, solo Freelancer Operating System (FMS) designed for independent developers, designers, and creators. Track live billable hours, manage flexible rate projects, and generate professional PDF invoices seamlessly.

---

## 🌟 Overview

**FreelanceOS** is built to solve the fragmentation of using separate timer apps, spreadsheet trackers, and invoicing tools. It consolidates your entire freelance business workflow into a unified, Bento-style dashboard powered by **Laravel 11**, **Livewire 3**, **Tailwind CSS**, and **Alpine.js**.

---

## ✨ Features

### ⏱️ 1. Persistent Stopwatch & Time Tracker
- **Sticky Live Header**: A live second-by-second ticker that persists across page transitions without resetting.
- **Pause & Resume Controls**: Pause ongoing sessions for quick breaks and resume with accurate accumulated time.
- **Natural Language Parsing**: Log past work quickly using formats like `1h 30m`, `45m`, or `1.5`.

### 💰 2. Flexible Multi-Tier Billing Rates
Support for 4 different freelancer pricing models:
1. **Per Hour (`hourly`)**: Real-time minute-by-minute rate accumulation.
2. **Per Task (`task`)**: Fixed flat-fee pricing per milestone or deliverable.
3. **Per Project (`project`)**: Lump-sum project contract pricing with budget burn calculation.
4. **Monthly Retainer (`monthly`)**: Recurring flat rate for ongoing client retainers.
*Includes granular fallback resolution: Task Rate ➔ Project Rate ➔ Global Default.*

### 📋 3. Project & Task Kanban Workspace
- Visual columns: **To-Do**, **In Progress**, **In Review**, and **Done**.
- One-click **Start Timer** directly from any task card.
- Visual budget burn progress meters to prevent scope creep.

### 🧾 4. Dynamic PDF Invoices & Historical Backlog
- **Split & Aggregate Invoicing**: Convert unbilled timesheet entries and flat tasks into clean PDF invoices with a single click.
- **Historical Invoices Logging**: Import past, already-paid invoices with custom invoice numbers to keep financial continuity without breaking auto-increment sequences.
- **Status Lifecycle**: Track drafts, sent, paid, and voided invoices with automated tax calculations.

### ⌨️ 5. Fuzzy Command Palette (`⌘K` / `Ctrl+K`)
- Power-user keyboard CLI to control the system instantly:
  - `start <project> <description>` — Launch timer for a project.
  - `pause` / `resume` / `stop` — Control active sessions.
  - `log <duration> <project> <desc>` — Natural language manual logging.
  - `goto <page>` — Jump directly to Dashboard, Timesheets, Projects, Invoices, or Settings.

### 🔔 6. Sonner Toast Feedback (ReUI / Shadcn Dark Theme)
- Instant feedback with stacked, rich-color Sonner notifications for all successful actions, warnings, and network/server validation errors.

### ⚙️ 7. Full Customization & Settings
- Multi-currency support (`MYR`, `USD`, `EUR`, `GBP`, `SGD`, `AUD`, etc.).
- Custom invoice numbering formats, prefix, default hourly rates, tax percentages, and company profile.

---

## 🚀 How to Use (Quick Start)

### Prerequisites
- **PHP 8.2+**
- **Composer**
- **Node.js 18+ & NPM**
- **MySQL / MariaDB** (or SQLite / Laragon)

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/Aousss/freelancer-system.git
cd freelancer-system

# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
Open `.env` and configure your database settings (e.g. for Laragon / local MySQL):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelancer_system
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Migrations & Seed Demo Data
```bash
php artisan migrate --seed
```

### 4. Build Assets & Start Server
```bash
# Compile frontend assets
npm run build

# Start local development server
php artisan serve
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

---

## 🗺️ Roadmap & Incoming Updates

### 🔜 Incoming Updates (Next Release)
- [ ] **Client Portal**: Shareable magic links allowing clients to view real-time task progress and download approved invoices.
- [ ] **Automated Reminders**: Email notifications for overdue unpaid invoices with customizable schedule intervals.
- [ ] **Timesheet CSV / Excel Export**: One-click raw timesheet exports for tax reporting and client audits.

### 🔮 Future Features
- [ ] **Payment Gateway Integration**: Direct credit card and bank transfer checkout via Stripe & ToyyibPay.
- [ ] **Git CLI / IDE Daemon**: Background watcher that automatically attributes commit times directly to project tasks.
- [ ] **Expense & Receipt Management**: Track deductible software subscriptions and contractor payouts alongside revenue.
- [ ] **Mobile Progressive Web App (PWA)**: Offline-first stopwatch timer app installable on iOS and Android.

---

## 📄 License
This project is open-source software licensed under the **[MIT License](LICENSE)**.
