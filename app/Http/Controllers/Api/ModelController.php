<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ModelService;
use Illuminate\Http\JsonResponse;

class ModelController extends Controller
{
    public function __construct(
        private ModelService $modelService,
    ) {}

    /**
     * Получить список всех моделей
     */
    public function index(): JsonResponse
    {
        $models = $this->modelService->getAllModels();

        return response()->json([
            'success' => true,
            'data' => $models,
        ]);
    }

    /**
     * Получить модели для конкретного бренда
     */
    public function byMake(string $make): JsonResponse
    {
        $models = $this->modelService->getModelsByMake($make);

        return response()->json([
            'success' => true,
            'data' => $models,
        ]);
    }
}
