<?php

namespace App\Services;

class TimeParserService
{
    /**
     * Parses natural language duration strings into integer seconds.
     * Examples:
     * - "1h 30m", "1h30m" -> 5400
     * - "45m" -> 2700
     * - "02:15" (HH:MM) -> 8100
     * - "1.75", "1.75h" -> 6300
     * - "2h" -> 7200
     * - "01:30:15" -> 5415
     */
    public function parseToSeconds(?string $input): int
    {
        if (empty($input)) {
            return 0;
        }

        $input = trim(strtolower($input));

        // HH:MM:SS format
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $input, $matches)) {
            return ((int) $matches[1] * 3600) + ((int) $matches[2] * 60) + (int) $matches[3];
        }

        // HH:MM format (e.g. "02:15")
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $input, $matches)) {
            return ((int) $matches[1] * 3600) + ((int) $matches[2] * 60);
        }

        // Combination like "1h 30m", "1h30m", "2h", "45m", "1h 30m 15s"
        if (preg_match('/(?:(\d+(?:\.\d+)?)\s*h(?:ours?)?)?\s*(?:(\d+(?:\.\d+)?)\s*m(?:in(?:utes?)?)?)?\s*(?:(\d+)\s*s(?:ec(?:onds?)?)?)?/i', $input, $matches)) {
            $hasHour = !empty($matches[1]);
            $hasMin = !empty($matches[2]);
            $hasSec = !empty($matches[3]);

            if ($hasHour || $hasMin || $hasSec) {
                $hours = $hasHour ? (float) $matches[1] : 0;
                $minutes = $hasMin ? (float) $matches[2] : 0;
                $seconds = $hasSec ? (int) $matches[3] : 0;

                return (int) round(($hours * 3600) + ($minutes * 60) + $seconds);
            }
        }

        // Decimal hours e.g. "1.75", "1.5"
        if (is_numeric($input)) {
            return (int) round((float) $input * 3600);
        }

        return 0;
    }

    /**
     * Format seconds to human friendly string: "1h 30m" or "45m"
     */
    public function formatSecondsToHuman(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = floor($seconds / 3600);
        $minutes = round(($seconds % 3600) / 60);

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }

        if ($hours > 0) {
            return "{$hours}h";
        }

        return "{$minutes}m";
    }
}
