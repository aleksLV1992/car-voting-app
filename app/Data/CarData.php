<?php

namespace App\Data;

use App\Models\Car;
use Spatie\LaravelData\Data;

class CarData extends Data
{
    public function __construct(
        public ?int $id = null,
        public ?string $image = null,
        public ?string $make = null,
        public ?string $model = null,
        public ?int $year = null,
        public ?int $odometer = null,
        public ?string $engine = null,
        public ?string $transmission = null,
        public ?string $color = null,
        public ?string $location = null,
        public ?string $price = null,
        public int $votes_count = 0,
    ) {}

    /**
     * Создать DTO из модели Car
     */
    public static function fromModel(Car $car): self
    {
        return new self(
            id: $car->id,
            image: $car->image,
            make: $car->make,
            model: $car->model,
            year: $car->year,
            odometer: $car->odometer,
            engine: $car->engine,
            transmission: $car->transmission,
            color: $car->color,
            location: $car->location,
            price: $car->price,
            votes_count: $car->votes_count ?? 0,
        );
    }
}
