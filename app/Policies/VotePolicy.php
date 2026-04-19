<?php

namespace App\Policies;

use App\Models\Vote;

class VotePolicy
{
    public function create(): bool
    {
        return true;
    }
}
