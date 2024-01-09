<?php

namespace Nytpuzzlehelper;

use Balsama\Nytpuzzlehelper\Group;
use Balsama\Nytpuzzlehelper\TwoNotTouch\TwoNotTouchBoard;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    public TwoNotTouchBoard $board;

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->board = new TwoNotTouchBoard($boardDescription);
    }

    public function testGetGroupWidth()
    {
        $group = $this->board->getCellGroup(1);
        $width = $group->getGroupWidth();
        $this->assertEquals(3, $width);
    }

    public function testGetGroupHeight()
    {
        $group = $this->board->getCellGroup(1);
        $height = $group->getGroupHeight();
        $this->assertEquals(3, $height);
    }

    public function testIsSquareT()
    {
        $group = $this->board->getCellGroup(1);
        $this->assertTrue($group->isSquareT());

        $group = $this->board->getCellGroup(2);
        $this->assertFalse(($group->isSquareT()));

        $group = $this->board->getCellGroup(3);
        $this->assertTrue(($group->isSquareT()));

        $group = $this->board->getCellGroup(4);
        $this->assertFalse(($group->isSquareT()));

        $group = $this->board->getCellGroup(5);
        $this->assertTrue(($group->isSquareT()));

        $group = $this->board->getCellGroup(6);
        $this->assertTrue(($group->isSquareT()));
    }

    public function testGetGroupCorner()
    {
        $group1 = $this->board->getCellGroup(1);
        $g1nwCorner = $group1->getGroupCorner(false, false);

        $group2 = $this->board->getCellGroup(2);
        $g2nwCorner = $group2->getGroupCorner(false, false);
        $foo = 21;
    }

    public function testGetcorner()
    {
        $group = $this->board->getCellGroup(2);
        $upperLeft = $group->getCorner(true);
        $this->assertEquals(2, $upperLeft->getRow());
        $this->assertEquals('a', $upperLeft->getColumn());
        $lowerRight = $group->getCorner(false);
        $this->assertEquals('d', $lowerRight->getColumn());
        $this->assertEquals(8, $lowerRight->getRow());

        $group = $this->board->getCellGroup(4);
        $upperLeft = $group->getCorner(true);
        $this->assertEquals(1, $upperLeft->getRow());
        $this->assertEquals('f', $upperLeft->getColumn());
        $lowerRight = $group->getCorner(false);
        $this->assertEquals('j', $lowerRight->getColumn());
        $this->assertEquals(9, $lowerRight->getRow());
    }
}