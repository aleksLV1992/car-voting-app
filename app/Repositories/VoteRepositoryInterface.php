<?php

namespace App\Repositories;

use App\Models\Vote;

interface VoteRepositoryInterface
{
    /**
     * Check if user already voted for a car
     */
    public function hasVoted(int $carId, string $voterId): bool;

    /**
     * Create a vote
     */
    public function create(int $carId, string $voterId): Vote;

    /**
     * Get vote count for a car
     */
    public function getCountByCar(int $carId): int;
}
