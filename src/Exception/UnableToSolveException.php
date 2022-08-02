<?php

namespace Balsama\Nytpuzzlehelper\Exception;

use Balsama\Nytpuzzlehelper\Cell;
use Throwable;

class UnableToSolveException extends \Exception
{
    public function __construct(
        public $message = "Unable to solve puzzle.",
        $code = 0,
        Throwable $previous = null,
        public ?Cell $cell = null,
        public ?array $state = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
