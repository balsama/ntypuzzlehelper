<?php

namespace Nytpuzzlehelper;

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Group;
use Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{

    private Board $board;
    private Group $group;
    private array $boardDescriptionSmall = [
        [1, 1, 1],
        [1, 1, 1],
        [2, 2, 2],
    ];

    private array $boardPrefillsSmall = [
        [0, 0, 0],
        [0, 0, 0],
        [0, 0, 0],
    ];
    private array $boardSolutionSmall = [
        [1, 0, 1],
        [0, 0, 0],
        [1, 0, 1],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->resetBoardSmall();
    }

    public function testGetGroupWidth()
    {
        $group1 = $this->board->getCellGroup(1);
        $width = $group1->getGroupWidth();
        $this->assertEquals(3, $width);

        $group2 = $this->board->getCellGroup(2);
        $width = $group2->getGroupWidth();
        $this->assertEquals(3, $width);
    }

    public function testGroupHeight()
    {
        $group1 = $this->board->getCellGroup(1);
        $height = $group1->getGroupHeight();
        $this->assertEquals(2, $height);

        $group2 = $this->board->getCellGroup(2);
        $height = $group2->getGroupHeight();
        $this->assertEquals(1, $height);
    }

    private function resetBoardSmall()
    {
        $this->board = new RippleEffectBoard($this->boardDescriptionSmall, $this->boardPrefillsSmall);
    }

}