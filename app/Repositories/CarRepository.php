<?php

namespace App\Repositories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CarRepository implements CarRepositoryInterface
{
    /**
     * Get all cars with optional filters
     */
    public function getAll(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null, int $page = 1, int $perPage = 12): LengthAwarePaginator
    {
        return Car::query()
            ->when($model, fn($q) => $q->where('make', $model))
            ->when($yearFrom, fn($q) => $q->where('year', '>=', $yearFrom))
            ->when($yearTo, fn($q) => $q->where('year', '<=', $yearTo))
            ->orderBy('votes_count', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get car by ID
     */
    public function getById(int $id): ?Car
    {
        return Car::find($id);
    }

    /**
     * Get all unique models (make + model combinations)
     */
    public function getAllModels(): array
    {
        return Car::query()
            ->selectRaw('make, model, COUNT(*) as count')
            ->distinct()
            ->whereNotNull('make')
            ->where('make', '!=', '')
            ->groupBy('make', 'model')
            ->orderBy('make')
            ->orderBy('model')
            ->get()
            ->map(fn($item) => [
                'make' => $item->make,
                'model' => $item->model,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    /**
     * Get all unique makes (brands)
     */
    public function getAllMakes(): array
    {
        return Car::query()
            ->distinct()
            ->whereNotNull('make')
            ->where('make', '!=', '')
            ->orderBy('make')
            ->pluck('make')
            ->toArray();
    }

    /**
     * Get random pair of cars for voting
     */
    public function getRandomPair(?string $model = null): ?array
    {
        $query = Car::query()
            ->when($model, fn($q) => $q->where('make', $model));

        $cars = $query->inRandomOrder()->limit(2)->get();

        return $cars->count() >= 2 ? $cars->toArray() : null;
    }

    /**
     * Get random pair with exclusions (for avoiding repeats)
     */
    public function getRandomPairWithExclusions(?string $model = null, array $excludeIds = []): Collection
    {
        return Car::query()
            ->when($model, fn($q) => $q->where('make', $model))
            ->when($excludeIds, fn($q) => $q->whereNotIn('id', $excludeIds))
            ->inRandomOrder()
            ->limit(2)
            ->get();
    }

    /**
     * Get count of cars by make
     */
    public function getCountByMake(?string $model = null): int
    {
        return Car::query()
            ->when($model, fn($q) => $q->where('make', $model))
            ->count();
    }

    /**
     * Get statistics with filters
     */
    public function getStatistics(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null, int $page = 1, int $perPage = 10): array
    {
        $paginator = Car::query()
            ->when($model, fn($q) => $q->where('make', $model))
            ->when($yearFrom, fn($q) => $q->where('year', '>=', $yearFrom))
            ->when($yearTo, fn($q) => $q->where('year', '<=', $yearTo))
            ->orderBy('votes_count', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ];
    }

    /**
     * Get total votes count for filtered cars
     */
    public function getTotalVotesCount(?string $model = null, ?int $yearFrom = null, ?int $yearTo = null): int
    {
        return Car::query()
            ->when($model, fn($q) => $q->where('make', $model))
            ->when($yearFrom, fn($q) => $q->where('year', '>=', $yearFrom))
            ->when($yearTo, fn($q) => $q->where('year', '<=', $yearTo))
            ->sum('votes_count');
    }

    /**
     * Increment votes count for a car
     */
    public function incrementVotesCount(int $carId): void
    {
        Car::query()
            ->where('id', $carId)
            ->increment('votes_count');
    }

    /**
     * Check if car exists
     */
    public function exists(int $id): bool
    {
        return Car::query()->where('id', $id)->exists();
    }
}
