<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use Illuminate\Console\Command;

class CheckIdleTimersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timers:check-idle {--hours=4 : Threshold in hours to consider a timer idle}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identifies active timers running beyond the designated threshold';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoursThreshold = (float) $this->option('hours');
        $cutoff = now()->subHours($hoursThreshold);

        $idleTimers = TimeEntry::with(['user', 'project', 'task'])
            ->whereNull('ended_at')
            ->where('started_at', '<=', $cutoff)
            ->get();

        if ($idleTimers->isEmpty()) {
            $this->info("No active timers exceeding {$hoursThreshold} hours found.");
            return Command::SUCCESS;
        }

        $this->warn("Found {$idleTimers->count()} timer(s) running longer than {$hoursThreshold} hours:");

        $rows = [];
        foreach ($idleTimers as $timer) {
            $elapsedHours = round(now()->diffInSeconds($timer->started_at) / 3600, 1);
            $rows[] = [
                $timer->id,
                $timer->user->name ?? 'User #' . $timer->user_id,
                $timer->project->name ?? 'N/A',
                $timer->description,
                $timer->started_at->toDateTimeString(),
                "{$elapsedHours}h",
            ];
        }

        $this->table(['ID', 'User', 'Project', 'Description', 'Started At', 'Elapsed'], $rows);

        return Command::SUCCESS;
    }
}
