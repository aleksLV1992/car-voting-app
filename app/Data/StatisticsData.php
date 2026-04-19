<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class StatisticsData extends Data
{
    /**
     * @param array<CarData> $data
     */
    public function __construct(
        public array $data,
        public int $total_votes,
        public int $total,
        public int $last_page,
        public int $current_page,
        public int $per_page,
    ) {}
}
