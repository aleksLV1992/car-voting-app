<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CarData extends Data
{
    public function __construct(
        public int $id,
        public string $image,
        public string $make,
        public string $model,
        public int $year,
        public ?int $odometer = null,
        public ?string $engine = null,
        public ?string $transmission = null,
        public ?string $color = null,
        public ?string $location = null,
        public ?string $price = null,
        public int $votes_count = 0,
    ) {}
}
