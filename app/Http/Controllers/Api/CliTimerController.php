<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\TimerStateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CliTimerController extends Controller
{
    public function __construct(
        protected TimerStateService $timerService
    ) {}

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTimer = $this->timerService->getActiveTimer($user->id);

        if (!$activeTimer) {
            return response()->json([
                'running' => false,
                'paused' => false,
                'entry' => null,
            ]);
        }

        return response()->json([
            'running' => true,
            'paused' => (bool) $activeTimer->is_paused,
            'entry' => [
                'id' => $activeTimer->id,
                'description' => $activeTimer->description,
                'project' => $activeTimer->project->slug ?? $activeTimer->project->name,
                'git_ref' => $activeTimer->git_ref,
                'started_at' => $activeTimer->started_at->toIso8601String(),
                'elapsed_seconds' => $activeTimer->elapsed_seconds,
                'formatted' => $activeTimer->formatted_duration,
                'is_paused' => (bool) $activeTimer->is_paused,
            ],
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_slug' => ['required', 'string', 'exists:projects,slug'],
            'description' => ['required', 'string', 'max:500'],
            'git_ref' => ['nullable', 'string', 'max:100'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $project = Project::where('slug', $validated['project_slug'])->firstOrFail();

        $entry = $this->timerService->startTimer([
            'project_id' => $project->id,
            'description' => $validated['description'],
            'git_ref' => $validated['git_ref'] ?? null,
            'is_billable' => $validated['is_billable'] ?? true,
        ], $request->user()->id);

        return response()->json([
            'message' => 'Timer started successfully.',
            'entry' => [
                'id' => $entry->id,
                'description' => $entry->description,
                'project' => $project->slug,
                'git_ref' => $entry->git_ref,
                'started_at' => $entry->started_at->toIso8601String(),
                'hourly_rate_snapshot' => (float) $entry->hourly_rate_snapshot,
            ],
        ], 201);
    }

    public function pause(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTimer = $this->timerService->getActiveTimer($user->id);

        if (!$activeTimer) {
            return response()->json([
                'running' => false,
                'paused' => false,
                'message' => 'No active timer running.',
            ], 404);
        }

        $pausedEntry = $this->timerService->pauseTimer($activeTimer);

        return response()->json([
            'running' => true,
            'paused' => true,
            'message' => 'Timer paused successfully.',
            'entry' => [
                'id' => $pausedEntry->id,
                'description' => $pausedEntry->description,
                'duration_seconds' => $pausedEntry->duration_seconds,
                'formatted' => $pausedEntry->formatted_duration,
                'is_paused' => true,
            ],
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTimer = $this->timerService->getActiveTimer($user->id);

        if (!$activeTimer) {
            return response()->json([
                'running' => false,
                'paused' => false,
                'message' => 'No active timer found.',
            ], 404);
        }

        $resumedEntry = $this->timerService->resumeTimer($activeTimer);

        return response()->json([
            'running' => true,
            'paused' => false,
            'message' => 'Timer resumed successfully.',
            'entry' => [
                'id' => $resumedEntry->id,
                'description' => $resumedEntry->description,
                'duration_seconds' => $resumedEntry->duration_seconds,
                'formatted' => $resumedEntry->formatted_duration,
                'is_paused' => false,
            ],
        ]);
    }

    public function stop(Request $request): JsonResponse
    {
        $user = $request->user();
        $activeTimer = $this->timerService->getActiveTimer($user->id);

        if (!$activeTimer) {
            return response()->json([
                'message' => 'No active timer running.',
            ], 404);
        }

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
            'git_ref' => ['nullable', 'string', 'max:100'],
        ]);

        if (!empty($validated['description'])) {
            $activeTimer->description = $validated['description'];
        }
        if (!empty($validated['git_ref'])) {
            $activeTimer->git_ref = $validated['git_ref'];
        }

        $stoppedEntry = $this->timerService->stopTimer($activeTimer);

        return response()->json([
            'message' => 'Timer stopped successfully.',
            'entry' => [
                'id' => $stoppedEntry->id,
                'description' => $stoppedEntry->description,
                'duration_seconds' => $stoppedEntry->duration_seconds,
                'formatted' => $stoppedEntry->formatted_duration,
                'billable_amount' => (float) $stoppedEntry->billable_amount,
                'ended_at' => $stoppedEntry->ended_at->toIso8601String(),
            ],
        ]);
    }
}
