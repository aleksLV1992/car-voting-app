<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CarPairData extends Data
{
    public function __construct(
        public ?CarData $left,
        public ?CarData $right,
    ) {}
}
