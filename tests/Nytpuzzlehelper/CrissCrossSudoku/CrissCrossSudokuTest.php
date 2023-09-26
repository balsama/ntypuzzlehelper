<?php

namespace Nytpuzzlehelper\CrissCrossSudoku;

use Balsama\Nytpuzzlehelper\CrissCrossSudoku\CrissCrossSudokuBoard;

class CrissCrossSudokuTest extends \PHPUnit\Framework\TestCase
{

    private CrissCrossSudokuBoard $board;

    protected function setUp(): void
    {
        $prefills = [
            ['p', 'e', 'n', 'c', 'i', 'l'],
            [  0,   0, 'i',   0,   0,   0],
            [  0,   0, 'c',   0,   0,   0],
            [  0, 'p', 'e', 'n',   0,   0],
            [  0,   0,   0,   0,   0, 'n'],
            [  0, 'c', 'l', 'i', 'p',   0],
        ];
        $this->board = new CrissCrossSudokuBoard($prefills);
        parent::setUp();
    }

    public function testReplaceAlphaValueWithMap()
    {
        $map = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];
        $result = $this->board->replaceAlphaWithNumeric('a', $map);
        $this->assertEquals(1, $result);
    }

    public function testBoard()
    {
        $this->board->solve();
        $this->assertEquals(1, 1);
    }

}