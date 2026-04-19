<?php

namespace App\Repositories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CarRepositoryInterface
{
    /**
     * Get all cars with optional filters
     */
    public function getAll(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null, int $page = 1, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get car by ID
     */
    public function getById(int $id): ?Car;

    /**
     * Get all unique models (make + model combinations)
     */
    public function getAllModels(): array;

    /**
     * Get all unique makes (brands)
     */
    public function getAllMakes(): array;

    /**
     * Get random pair of cars for voting
     */
    public function getRandomPair(?string $model = null): ?array;

    /**
     * Get random pair with exclusions (for avoiding repeats)
     */
    public function getRandomPairWithExclusions(?string $model = null, array $excludeIds = []): Collection;

    /**
     * Get count of cars by make
     */
    public function getCountByMake(?string $model = null): int;

    /**
     * Get statistics with filters
     */
    public function getStatistics(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null, int $page = 1, int $perPage = 10): array;

    /**
     * Get total votes count for filtered cars
     */
    public function getTotalVotesCount(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null): int;

    /**
     * Increment votes count for a car
     */
    public function incrementVotesCount(int $carId): void;

    /**
     * Check if car exists
     */
    public function exists(int $id): bool;
}
