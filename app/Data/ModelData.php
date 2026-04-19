<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class ModelData extends Data
{
    public function __construct(
        public string $make,
        public string $model,
        public int $count,
    ) {}
}
