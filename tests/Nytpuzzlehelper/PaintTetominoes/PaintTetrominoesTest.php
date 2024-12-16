<?php

namespace Nytpuzzlehelper\PaintTetominoes;

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Group;
use Balsama\Nytpuzzlehelper\PaintTetrominoes\PaintTetrominoes;

class PaintTetrominoesTest extends \PHPUnit\Framework\TestCase
{

    public function testFindShapeInBoard()
    {
        $board = new PaintTetrominoes(
            [
                [1, 1, 1],
                [2, 1, 2],
                [2, 2, 2],
            ],
            [],
            [],
            [],
        );

        $shape = new Board(
            [
                [1, 1, 1],
                [1, 1, 1],
            ],
            [
                [1, 1, 1],
                [0, 1, 0],
            ],
            [
                [1, 1, 1],
                [0, 1, 0],
            ],
        );

        $board->findShapeInBoard($shape);
    }
}