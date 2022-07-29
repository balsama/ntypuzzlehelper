<?php

class BoardTest extends \PHPUnit\Framework\TestCase
{
    private \Balsama\Nytpuzzlehelper\Board $board;

    protected function setUp(): void
    {
        $boardDescription = [
            [1, 1, 2],
            [1, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [2, 3, 0],
            [0, 0, 0],
            [0, 0, 0],
        ];
        $boardSolution = [
            [2, 3, 1],
            [1, 2, 3],
            [3, 1, 2],
        ];
        $this->board = new \Balsama\Nytpuzzlehelper\Board($boardDescription, $boardPrefills);
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
        $cell = $this->board->getMutableCell(1, 'a');
        $this->assertInstanceOf('\Balsama\Nytpuzzlehelper\Cell', $cell);

        $value = 2;
        $cell->setValue($value);
        $this->assertEquals($value, $cell->getValue());
        $this->assertEquals($value, $this->board->cells[md5(1 . 'a')]->value);
    }

    public function testAttemptToSolve()
    {

        $result = $this->board->attemptToSolve(1, 'b');

        $this->assertEquals(2, $result->assigned);
        $this->assertEquals('confident', $result->confidence);
        $foo = 21;
    }

}