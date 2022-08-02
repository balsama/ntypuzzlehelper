<?php

namespace Balsama\Nytpuzzlehelper\Exception;

use Balsama\Nytpuzzlehelper\Cell;
use Throwable;

class UnableToSolveException extends \Exception
{
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        public ?Cell $cell = null,
        public ?array $state = null)
    {
        parent::__construct($message, $code, $previous);
    }

    protected $message = 'Unable to solve puzzle.';

}