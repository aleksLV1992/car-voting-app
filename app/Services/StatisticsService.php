<?php

namespace App\Services;

use App\Data\CarData;
use App\Repositories\CarRepositoryInterface;

class StatisticsService
{
    public function __construct(
        private readonly CarRepositoryInterface $carRepository,
    ) {}

    /**
     * Получить статистику с фильтрами и пагинацией
     *
     * @param string|null $model    Бренд автомобиля
     * @param int|null    $yearFrom Год от
     * @param int|null    $yearTo   Год до
     * @param int         $page     Страница
     * @param int         $perPage  Записей на страницу
     * @return array{data: array, total_votes: int, total: int, last_page: int, current_page: int, per_page: int}
     */
    public function getStatistics(
        ?string $model = null,
        ?int $yearFrom = null,
        ?int $yearTo = null,
        int $page = 1,
        int $perPage = 10
    ): array {
        $result = $this->carRepository->getStatistics($model, $yearFrom, $yearTo, $page, $perPage);

        return [
            'data' => collect($result['data'])->map(fn ($car) => CarData::from($car))->values()->toArray(),
            'total_votes' => $this->carRepository->getTotalVotesCount($model, $yearFrom, $yearTo),
            'total' => $result['total'],
            'last_page' => $result['last_page'],
            'current_page' => $result['current_page'],
            'per_page' => $result['per_page'],
        ];
    }

    /**
     * Получить список всех моделей (брендов)
     *
     * @return array<string>
     */
    public function getAllModels(): array
    {
        return $this->carRepository->getAllMakes();
    }

    /**
     * Получить список автомобилей с фильтрами
     *
     * @param string|null $model    Бренд автомобиля
     * @param int|null    $yearFrom Год от
     * @param int|null    $yearTo   Год до
     * @param int         $page     Страница
     * @param int         $perPage  Записей на страницу
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCars(
        ?string $model = null,
        ?int $yearFrom = null,
        ?int $yearTo = null,
        int $page = 1,
        int $perPage = 12
    ) {
        return $this->carRepository->getAll($model, $yearFrom, $yearTo, $page, $perPage);
    }
}
