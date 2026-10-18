<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;

class RateResolverService
{
    /**
     * Resolve hourly rate following strict specificity:
     * 1. Task hourly_rate override (if present)
     * 2. Project hourly_rate override (if present)
     * 3. Client default_rate
     */
    public function resolve(Project $project, ?Task $task = null): float
    {
        if ($task && !is_null($task->hourly_rate)) {
            return (float) $task->hourly_rate;
        }

        if (!is_null($project->hourly_rate)) {
            return (float) $project->hourly_rate;
        }

        if ($project->relationLoaded('client') && $project->client) {
            return (float) ($project->client->default_rate ?? 0.00);
        }

        $client = $project->client()->first();
        return (float) ($client?->default_rate ?? 0.00);
    }
}
