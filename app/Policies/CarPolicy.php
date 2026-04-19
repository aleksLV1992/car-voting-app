<?php

namespace App\Policies;

use App\Models\Car;

class CarPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function vote(): bool
    {
        return true;
    }
}
