<?php

namespace App\Observers;

use App\Models\Contest;
use App\Models\User;

class ContestObserver
{
    public function creating(Contest $contest): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $contest->user_id = $user->id;
        }
    }
}
