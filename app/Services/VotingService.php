<?php

namespace App\Services;

use App\Data\CarData;
use App\Data\CarPairData;
use App\Repositories\CarRepositoryInterface;
use App\Repositories\VoteRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class VotingService
{
    public function __construct(
        private readonly CarRepositoryInterface $carRepository,
        private readonly VoteRepositoryInterface $voteRepository,
    ) {}

    /**
     * Получить случайную пару автомобилей
     * Фотографии не повторяются в цикле (используется кэш для отслеживания показанных)
     *
     * @param string|null $model   Бренд автомобиля (make)
     * @param string|null $voterId ID голосующего (IP)
     * @return CarPairData Пара автомобилей или null если недостаточно
     */
    public function getRandomPair(?string $model = null, ?string $voterId = null): CarPairData
    {
        $cacheKey = $this->getCacheKey($model, $voterId);
        $shownIds = Cache::get($cacheKey, []);

        $cars = $this->carRepository->getRandomPairWithExclusions($model, $shownIds);

        if ($cars->count() < 2 && !empty($shownIds)) {
            Cache::forget($cacheKey);
            $cars = $this->carRepository->getRandomPairWithExclusions($model, []);
        }

        if ($cars->count() < 2) {
            return new CarPairData(left: null, right: null);
        }

        $this->updateCache($cacheKey, $shownIds, $cars);

        return new CarPairData(
            left: CarData::from($cars[0]),
            right: CarData::from($cars[1])
        );
    }

    /**
     * Проголосовать за автомобиль
     */
    public function vote(int $carId, string $voterId): bool
    {
        if (!$this->carRepository->exists($carId)) {
            return false;
        }

        if ($this->voteRepository->hasVoted($carId, $voterId)) {
            return false;
        }

        $this->voteRepository->create($carId, $voterId);
        $this->carRepository->incrementVotesCount($carId);

        return true;
    }

    /**
     * Проверить, голосовал ли пользователь за автомобиль
     */
    public function hasVoted(int $carId, string $voterId): bool
    {
        return $this->voteRepository->hasVoted($carId, $voterId);
    }

    /**
     * Проверить, достаточно ли автомобилей для голосования
     */
    public function hasEnoughCars(?string $model = null): bool
    {
        return $this->carRepository->getCountByMake($model) >= 2;
    }

    /**
     * Получить количество голосов за автомобиль
     */
    public function getVotesCount(int $carId): int
    {
        $car = $this->carRepository->getById($carId);
        return $car ? $car->votes_count : 0;
    }

    /**
     * Получить ключ кэша для отслеживания показанных автомобилей
     */
    private function getCacheKey(?string $model, ?string $voterId): string
    {
        return $voterId
            ? 'voting.shown.' . $voterId . ($model ? '.' . $model : '')
            : 'voting.shown.global' . ($model ? '.' . $model : '');
    }

    /**
     * Обновить кэш показанных автомобилей
     */
    private function updateCache(string $cacheKey, array $shownIds, $cars): void
    {
        $shownIds = array_merge($shownIds, [$cars[0]->id, $cars[1]->id]);
        Cache::put($cacheKey, $shownIds, now()->addDay());
    }
}
