<?php

namespace Nytpuzzlehelper;

use Balsama\Nytpuzzlehelper\Board;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    private Board $board;

    private array $boardDescriptionSmall = [
        [1, 1, 2],
        [1, 2, 2],
        [3, 3, 3],
    ];

    private array $boardPrefillsSmall = [
        [2, 3, 0],
        [0, 0, 0],
        [0, 0, 0],
    ];

    protected function setUp(): void
    {
        $this->resetBoardSmall();
        parent::setUp();
    }

    public function testBoard()
    {
        $this->assertIsArray($this->board->cells);
        $this->assertCount(9, $this->board->cells);

        $cellIdentifiers = array_keys($this->board->cells);
        $this->assertEquals(md5('1a'), $cellIdentifiers[0]);
        $this->assertEquals(md5('3c'), $cellIdentifiers[8]);
    }

    public function testMutateCell()
    {
        $this->resetBoardSmall();

        $cell = $this->board->getMutableCell(3, 'a');
        $this->assertInstanceOf('\Balsama\Nytpuzzlehelper\Cell', $cell);

        $value = 2;
        $cell->setValue($value);
        $this->assertEquals($value, $cell->getValue());
        $this->assertEquals($value, $this->board->cells[md5(1 . 'a')]->getValue());
    }

    public function testGetXDistance()
    {
        $this->assertEquals(1, $this->board->getXDistance('b', 'a'));
        $this->assertEquals(3, $this->board->getXDistance('b', 'e'));
        $this->assertEquals(0, $this->board->getXDistance('d', 'd'));
    }

    private function resetBoardSmall()
    {
        $this->board = new Board($this->boardDescriptionSmall, $this->boardPrefillsSmall);
    }
}
