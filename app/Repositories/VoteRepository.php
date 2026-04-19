<?php

namespace App\Repositories;

use App\Models\Vote;

class VoteRepository implements VoteRepositoryInterface
{
    public function hasVoted(int $carId, string $voterId): bool
    {
        return Vote::query()
            ->where('car_id', $carId)
            ->where('voter_id', $voterId)
            ->exists();
    }

    public function create(int $carId, string $voterId): Vote
    {
        return Vote::query()->create([
            'car_id' => $carId,
            'voter_id' => $voterId,
        ]);
    }

    public function getCountByCar(int $carId): int
    {
        return Vote::query()->where('car_id', $carId)->count();
    }
}
