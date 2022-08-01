<?php

class BoardTest extends \PHPUnit\Framework\TestCase
{
    private \Balsama\Nytpuzzlehelper\Board $board;

    private array $boardDescription = [
        [1, 1, 2],
        [1, 2, 2],
        [3, 3, 3],
    ];

    private array $boardPrefills = [
        [2, 3, 0],
        [0, 0, 0],
        [0, 0, 0],
    ];
    private array $boardSolution = [
        [2, 3, 1],
        [1, 2, 3],
        [3, 1, 2],
    ];

    protected function setUp(): void
    {
        $this->resetBoard();
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
        $this->resetBoard();

        $cell = $this->board->getMutableCell(3, 'a');
        $this->assertInstanceOf('\Balsama\Nytpuzzlehelper\Cell', $cell);

        $value = 2;
        $cell->setValue($value);
        $this->assertEquals($value, $cell->getValue());
        $this->assertEquals($value, $this->board->cells[md5(1 . 'a')]->value);
    }

    public function testAttemptToSolveCell()
    {
        $this->resetBoard();

        $result = $this->board->attemptToSolveCell(1, 'b');
        $this->assertEquals('already assigned', $result->confidence);

        $result = $this->board->attemptToSolveCell(2, 'a');
        $this->assertEquals('confident', $result->confidence);
        $this->assertEquals(1, $result->assigned);
    }

    public function testAttemptToSolveBoard()
    {
        $this->resetBoard();
        $state = $this->board->getCurrentState();
        $this->board->solve($state);

        $state = $this->board->getCurrentState();
        $this->assertEquals($this->boardSolution, $state);
    }

    public function testComplicatedBoard()
    {
        $boardDescription = [
            [1, 1, 2, 3, 3, 3, 3],
            [4, 4, 2, 5, 6, 7, 3],
            [2, 2, 2, 5, 6, 7, 3],
            [8, 8, 9, 5, 10, 11, 10],
            [12, 12, 9, 5, 10, 10, 10],
            [9, 9, 9, 13, 10, 14, 13],
            [15, 15, 16, 13, 13, 13, 13],
        ];
        $boardPrefills = [
            [0, 0, 1, 5, 3, 0, 0],
            [0, 0, 3, 0, 2, 0, 0],
            [0, 4, 0, 0, 0, 2, 0],
            [0, 0, 0, 0, 0, 0, 0],
            [0, 0, 5, 0, 3, 0, 0],
            [0, 0, 2, 1, 5, 0, 0],
            [1, 0, 0, 3, 0, 0, 4],
        ];

        $bigBoard = new \Balsama\Nytpuzzlehelper\Board($boardDescription, $boardPrefills);
        $state = $bigBoard->getCurrentState();
        $bigBoard->solve($state);
        $state = $bigBoard->getCurrentState();
        $foo = 21;
    }


    private function resetBoard()
    {
        $this->board = new \Balsama\Nytpuzzlehelper\Board($this->boardDescription, $this->boardPrefills);
    }

}