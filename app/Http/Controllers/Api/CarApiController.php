<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarApiController extends Controller
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {}

    /**
     * Получить список автомобилей с фильтрами и пагинацией
     *
     * @param Request $request HTTP запрос
     * @return JsonResponse JSON ответ со списком автомобилей
     */
    public function index(Request $request): JsonResponse
    {
        $cars = $this->statisticsService->getCars(
            model: $request->query('model'),
            yearFrom: $request->integer('yearFrom'),
            yearTo: $request->integer('yearTo'),
            page: $request->integer('page', 1),
            perPage: $request->integer('perPage', 12)
        );

        return response()->json([
            'data' => $cars->items(),
            'pagination' => [
                'current_page' => $cars->currentPage(),
                'last_page' => $cars->lastPage(),
                'per_page' => $cars->perPage(),
                'total' => $cars->total(),
            ],
        ]);
    }

    /**
     * Получить список всех моделей (брендов)
     *
     * @return JsonResponse JSON ответ со списком моделей
     */
    public function models(): JsonResponse
    {
        return response()->json([
            'data' => $this->statisticsService->getAllModels(),
        ]);
    }

    /**
     * Получить статистику с фильтрами и пагинацией
     *
     * @param Request $request HTTP запрос
     * @return JsonResponse JSON ответ со статистикой
     */
    public function statistics(Request $request): JsonResponse
    {
        $result = $this->statisticsService->getStatistics(
            model: $request->get('model'),
            yearFrom: $request->integer('yearFrom'),
            yearTo: $request->integer('yearTo'),
            page: $request->integer('page', 1),
            perPage: $request->integer('perPage', 10)
        );

        return response()->json($result);
    }
}
