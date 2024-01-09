<?php

namespace Nytpuzzlehelper\TwoNotTouch;

use Balsama\Nytpuzzlehelper\TwoNotTouch\TwoNotTouchBoard;

class TwoNotTouchTest extends \PHPUnit\Framework\TestCase
{


    public function testTwoNotTouchConstruct()
    {
        $boardDescription = [
            [1, 1, 1, 2, 3, 4, 4, 4, 4, 5,],
            [2, 1, 2, 2, 3, 4, 4, 5, 5, 5,],
            [2, 1, 2, 3, 3, 3, 4, 4, 4, 5,],
            [2, 2, 2, 6, 6, 6, 4, 4, 4, 4,],
            [2, 2, 2, 2, 6, 4, 7, 4, 4, 4,],
            [2, 2, 2, 2, 6, 4, 7, 7, 7, 4,],
            [2, 2, 2, 2, 8, 4, 7, 4, 4, 4,],
            [9, 2, 2, 2, 8, 4, 4, 4, 10, 4,],
            [9, 9, 9, 8, 8, 8, 4, 4, 10, 4,],
            [9, 4, 4, 4, 4, 4, 4, 10, 10, 10,]
        ];
        $board = new TwoNotTouchBoard($boardDescription);
        $foo = 21;
    }
}