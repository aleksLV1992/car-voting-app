<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ModelsListData extends Data
{
    /**
     * @param array<string> $models
     */
    public function __construct(
        public array $models,
    ) {}
}
