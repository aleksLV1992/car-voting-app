<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ModelData;
use App\Repositories\CarRepositoryInterface;
use Illuminate\Support\Collection;

class ModelService
{
    public function __construct(
        private readonly CarRepositoryInterface $carRepository,
    ) {}

    /**
     * Получить все модели для бренда
     */
    public function getModelsByMake(?string $make = null): Collection
    {
        $models = $this->carRepository->getAllModels();

        if ($make) {
            $models = array_filter($models, fn($item) => $item['make'] === $make);
        }

        return collect($models)->map(fn($item) => new ModelData(
            make: $item['make'],
            model: $item['model'],
            count: $item['count'] ?? 0
        ));
    }

    /**
     * Получить все уникальные комбинации make/model
     */
    public function getAllModels(): Collection
    {
        $models = $this->carRepository->getAllModels();

        return collect($models)->map(fn($item) => new ModelData(
            make: $item['make'],
            model: $item['model'],
            count: $item['count'] ?? 0
        ));
    }
}
