<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class VoteResultData extends Data
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $votes_count,
    ) {}
}
