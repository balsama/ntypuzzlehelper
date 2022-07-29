<?php

namespace Balsama\Nytpuzzlehelper;

class Result
{
    public function __construct(
        public ?int $assigned,
        public string $confidence,
    )
    {}
}