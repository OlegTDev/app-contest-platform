<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contest;
use Carbon\CarbonInterface;

/**
 * Centralized time window checks for contests and entries.
 * Eliminates duplicated time-check logic across models and services.
 */
final readonly class TimeWindowService
{
    /**
     * Check if a datetime window is currently active.
     */
    public function isWithinWindow(?CarbonInterface $from, ?CarbonInterface $until): bool
    {
        if ($from === null || $until === null) {
            return true;
        }

        $now = now();

        if ($now->lt($from)) {
            return false;
        }

        if ($now->gt($until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a contest is within its active time window (start_at / end_at).
     */
    public function isContestActive(Contest $contest): bool
    {
        return $this->isWithinWindow($contest->start_at, $contest->end_at);
    }
}
