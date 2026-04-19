<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VotingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VotingController extends Controller
{
    public function __construct(
        private readonly VotingService $votingService
    ) {}

    /**
     * Получить случайную пару автомобилей для голосования
     *
     * @param Request $request HTTP запрос
     * @return JsonResponse JSON ответ с парой автомобилей
     */
    public function getPair(Request $request): JsonResponse
    {
        $pair = $this->votingService->getRandomPair(
            model: $request->query('model'),
            voterId: $request->ip()
        );

        if (!$pair->left || !$pair->right) {
            $insufficientCars = $this->checkInsufficientCars($request->query('model'));

            return response()->json([
                'success' => false,
                'message' => 'Недостаточно автомобилей для голосования',
                'insufficientCars' => $insufficientCars,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pair,
        ]);
    }

    /**
     * Проголосовать за автомобиль
     *
     * @param Request $request HTTP запрос
     * @param int     $carId   ID автомобиля
     * @return JsonResponse JSON ответ с результатом
     */
    public function vote(Request $request, int $carId): JsonResponse
    {
        $success = $this->votingService->vote($carId, $request->ip());

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже голосовали за этот автомобиль!',
            ], 400);
        }

        $votesCount = $this->votingService->getVotesCount($carId);

        return response()->json([
            'success' => true,
            'votes_count' => $votesCount,
        ]);
    }

    /**
     * Проверить недостаточно ли автомобилей для бренда
     *
     * @param string|null $model Бренд автомобиля
     * @return bool
     */
    private function checkInsufficientCars(?string $model): bool
    {
        return $this->votingService->hasEnoughCars($model);
    }
}
