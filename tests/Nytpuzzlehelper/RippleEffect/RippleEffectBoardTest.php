<?php

namespace Nytpuzzlehelper\RippleEffect;

use Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class RippleEffectBoardTest extends TestCase
{
    private RippleEffectBoard $board;

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
    private array $boardSolutionSmall = [
        [2, 3, 1],
        [1, 2, 3],
        [3, 1, 2],
    ];

    private array $boardDescriptionLarge = [
        [1, 1, 2, 3, 3, 3, 3],
        [4, 4, 2, 5, 6, 7, 3],
        [2, 2, 2, 5, 6, 7, 3],
        [8, 8, 9, 5, 10, 11, 10],
        [12, 12, 9, 5, 10, 10, 10],
        [9, 9, 9, 13, 10, 14, 13],
        [15, 15, 16, 13, 13, 13, 13],
    ];

    private array $boardPrefillsLarge = [
        [0, 0, 1, 5, 3, 0, 0],
        [0, 0, 3, 0, 2, 0, 0],
        [0, 4, 0, 0, 0, 2, 0],
        [0, 0, 0, 0, 0, 0, 0],
        [0, 0, 5, 0, 3, 0, 0],
        [0, 0, 2, 1, 5, 0, 0],
        [1, 0, 0, 3, 0, 0, 4],
    ];

    private array $boardSolutionLarge = [
        [0 => 1, 1 => 2, 2 => 1, 3 => 5, 4 => 3, 5 => 6, 6 => 2],
        [0 => 2, 1 => 1, 2 => 3, 3 => 1, 4 => 2, 5 => 1, 6 => 4],
        [0 => 5, 1 => 4, 2 => 2, 3 => 3, 4 => 1, 5 => 2, 6 => 1],
        [0 => 1, 1 => 2, 2 => 1, 3 => 4, 4 => 6, 5 => 1, 6 => 2],
        [0 => 2, 1 => 1, 2 => 5, 3 => 2, 4 => 3, 5 => 4, 6 => 1],
        [0 => 4, 1 => 3, 2 => 2, 3 => 1, 4 => 5, 5 => 1, 6 => 6],
        [0 => 1, 1 => 2, 2 => 1, 3 => 3, 4 => 2, 5 => 5, 6 => 4],
    ];

    private array $boardPrefillsLargeHalfSolved = [
        [0 => 1, 1 => 2, 2 => 1, 3 => 5, 4 => 3, 5 => 6, 6 => 2],
        [0 => 2, 1 => 1, 2 => 3, 3 => 0, 4 => 2, 5 => 1, 6 => 4],
        [0 => 5, 1 => 4, 2 => 2, 3 => 3, 4 => 1, 5 => 2, 6 => 1],
        [0 => 1, 1 => 2, 2 => 0, 3 => 0, 4 => 6, 5 => 1, 6 => 2],
        [0 => 2, 1 => 1, 2 => 5, 3 => 2, 4 => 3, 5 => 4, 6 => 1],
        [0 => 0, 1 => 3, 2 => 2, 3 => 1, 4 => 5, 5 => 1, 6 => 6],
        [0 => 1, 1 => 2, 2 => 1, 3 => 3, 4 => 2, 5 => 5, 6 => 4],
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

    public function testAttemptToSolveBoard()
    {
        $this->resetBoardSmall();
        $this->board->solve();

        $state = $this->board->getCurrentState();
        $this->assertEquals($this->boardSolutionSmall, $state);
    }

    public function testComplicatedBoard()
    {
        $this->resetBoardLarge();

        $this->board->getCurrentState();
        $this->board->solve();
        $state = $this->board->getCurrentState();
        $this->assertEquals($this->boardSolutionLarge, $state);
    }

    public function testGetXDistance()
    {
        $this->assertEquals(1, $this->board->getXDistance('b', 'a'));
        $this->assertEquals(3, $this->board->getXDistance('b', 'e'));
        $this->assertEquals(0, $this->board->getXDistance('d', 'd'));
    }

    public function testFindDisallowedYValues()
    {
        $this->resetBoardSmall();
        $disallowedYValues = $this->board->findDisallowedYValues($this->board->cells[md5('3a')]);
        $this->assertCount(1, $disallowedYValues);
        $this->assertEquals(2, reset($disallowedYValues));

        $this->resetBoardLarge();
        $disallowedYValues = $this->board->findDisallowedYValues($this->board->cells[md5('2d')]);
        $this->assertCount(1, $disallowedYValues);
        $this->assertEquals([5], $disallowedYValues);

        $disallowedYValues = $this->board->findDisallowedYValues($this->board->cells[md5('3e')]);
        $this->assertCount(3, $disallowedYValues);
        $this->assertEquals([3, 2, 5], array_values($disallowedYValues));

        $disallowedYValues = $this->board->findDisallowedYValues($this->board->cells[md5('7e')]);
        $this->assertCount(2, $disallowedYValues);
        $this->assertEquals([3, 5], $disallowedYValues);

        $board = [[1, 1, 1], [1, 1, 1], [1, 1, 1]];
        $prefills = [[2, 0, 0], [0, 0, 0], [0, 0, 0]];
        $board = new RippleEffectBoard($board, $prefills);

        $disallowedYValues = $board->findDisallowedYValues($board->cells[md5('2a')]);
        $this->assertCount(1, $disallowedYValues);
        $this->assertEquals([2], $disallowedYValues);

        $disallowedYValues = $board->findDisallowedYValues($board->cells[md5('3a')]);
        $this->assertCount(1, $disallowedYValues);
        $this->assertEquals([2], $disallowedYValues);
    }

    public function testFindDisallowedXValues()
    {
        $this->resetBoardSmall();
        $disallowedXValues = $this->board->findDisallowedXValues($this->board->cells[md5('1c')]);
        $this->assertCount(2, $disallowedXValues);
        $this->assertEquals([2, 3], $disallowedXValues);

        $this->resetBoardLarge();
        $disallowedXValues = $this->board->findDisallowedXValues($this->board->cells[md5('3e')]);
        $this->assertCount(2, $disallowedXValues);
        $this->assertEquals([4, 2], $disallowedXValues);

        $disallowedXValues = $this->board->findDisallowedXValues($this->board->cells[md5('1a')]);
        $this->assertCount(1, $disallowedXValues);
        $this->assertEquals([5], $disallowedXValues);

        $disallowedXValues = $this->board->findDisallowedXValues($this->board->cells[md5('4b')]);
        $this->assertCount(0, $disallowedXValues);
        $this->assertEquals([], $disallowedXValues);

        $board = [[1, 1, 1], [1, 1, 1], [1, 1, 1]];
        $prefills = [[0, 0, 0], [0, 0, 2], [0, 0, 0]];
        $board = new RippleEffectBoard($board, $prefills);

        $disallowedXValues = $board->findDisallowedXValues($this->board->cells[md5('2a')]);
        $this->assertCount(1, $disallowedXValues);
        $this->assertEquals([2], $disallowedXValues);

        $board = [[1, 1, 1], [1, 1, 1], [1, 1, 1]];
        $prefills = [[0, 0, 0], [0, 0, 1], [0, 0, 0]];
        $board = new RippleEffectBoard($board, $prefills);

        $disallowedXValues = $board->findDisallowedXValues($this->board->cells[md5('2a')]);
        $this->assertCount(0, $disallowedXValues);
        $this->assertEquals([], $disallowedXValues);
    }

    public function testHalfSolved()
    {
        $this->board = new RippleEffectBoard($this->boardDescriptionLarge, $this->boardPrefillsLargeHalfSolved);
        $cell = $this->board->cells[md5('2d')];
        $disallowedX = $this->board->findDisallowedXValues($cell);
        $this->assertCount(3, $disallowedX);
        $this->assertEquals([3, 2, 4], $disallowedX);
    }

    public function testCellHasUniquePossibleValueWithinGroup()
    {
        $definition = [
            [1, 1, 1],
            [2, 2, 2],
        ];
        $prefills = [
            [0, 0, 0],
            [3, 3, 0],
        ];

        $board = new RippleEffectBoard($definition, $prefills);
        $result = $board->cellHasUniquePossibleValueWithinGroup($board->getMutableCell(1, 'c'));
        $this->assertTrue($result === 3);

        $definition = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $prefills = [
            [0, 0, 0],
            [2, 2, 0],
            [3, 3, 0],
        ];
        $board = new RippleEffectBoard($definition, $prefills);
        $result = $board->cellHasUniquePossibleValueWithinGroup($board->getMutableCell(1, 'c'));
        $this->assertFalse($result);
    }

    private function resetBoardSmall()
    {
        $this->board = new RippleEffectBoard($this->boardDescriptionSmall, $this->boardPrefillsSmall);
    }
    private function resetBoardLarge()
    {
        $this->board = new RippleEffectBoard($this->boardDescriptionLarge, $this->boardPrefillsLarge);
    }
}
