<?php

namespace Nytpuzzlehelper\AllOrOne;

use Balsama\Nytpuzzlehelper\AllOrOne\AllOrOneBoard;
use PHPUnit\Framework\TestCase;

class AllOrOneBoardTest extends TestCase
{

    private AllOrOneBoard $board;

    protected function setUp(): void
    {
        $this->resetBoardSmall();
        parent::setUp();
    }

    public function testGetTNeighbors()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 1, 0],
            [0, 0, 0],
            [0, 2, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $tNeighbors = $board->getTNeighbors($board->getMutableCell(2, 'b'));
        $this->assertCount(4, $tNeighbors);
    }

    public function testFillBullyNeighborCants()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 1, 0],
            [0, 0, 0],
            [0, 2, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $cell = $board->getMutableCell(2, 'b');
        $this->assertEmpty($cell->prohibitedValues);

        $board->fillBullyNeighborCants($cell);
        $this->assertCount(2, $cell->prohibitedValues);
        $this->assertTrue(in_array(1, $cell->prohibitedValues));
        $this->assertTrue(in_array(2, $cell->prohibitedValues));
        $this->assertFalse(in_array(3, $cell->prohibitedValues));
    }

    public function testFillCantBeOthersCants()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [1, 0, 0],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $b2 = $board->getMutableCell(2, 'b');
        $c2 = $board->getMutableCell(2, 'c');
        $b2->addProhibitedValue(2);
        $c2->addProhibitedValue(2);

        $group = $board->getCellGroup($b2->getGroup());
        $board->solveCantBeOthers($group);

        $this->assertEquals(1, $b2->getValue());
        $this->assertEquals(1, $c2->getValue());
    }

    public function testFillContBeOnesCants()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [1, 0, 0],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $b2 = $board->getMutableCell(2, 'b');
        $c2 = $board->getMutableCell(2, 'c');
        $c2->addProhibitedValue(1);
        $this->assertEmpty($b2->prohibitedValues);

        $group = $board->getCellGroup($b2->getGroup());
        $board->fillContBeOnesCants($group);

        $this->assertNotEmpty($b2->prohibitedValues);
        $this->assertEquals([1], $b2->prohibitedValues);
    }

    public function testSolveLastRemaining()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [1, 0, 1],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $b2 = $board->getMutableCell(2, 'b');
        $this->assertEmpty($b2->getValue());

        $group = $board->getCellGroup($b2->getGroup());
        $board->solveLastRemaining($group);

        $this->assertNotEmpty($b2->getValue());
        $this->assertEquals(1, $b2->getValue());

        $boardPrefills = [
            [0, 0, 0],
            [1, 0, 2],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $b2 = $board->getMutableCell(2, 'b');
        $this->assertEmpty($b2->getValue());

        $group = $board->getCellGroup($b2->getGroup());
        $board->solveLastRemaining($group);

        $this->assertNotEmpty($b2->getValue());
        $this->assertEquals(3, $b2->getValue());
    }

    public function testGetInsideCornersPowerfulNeighbors()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 3, 3],
            [2, 2, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [0, 0, 0],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $b1 = $board->getMutableCell(1, 'b');
        $b2 = $board->getMutableCell(2, 'b');
        $b3 = $board->getMutableCell(3, 'b');
        $a3 = $board->getMutableCell(3, 'a');

        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b1);
        $this->assertCount(0, $powerfulNeighbors);

        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b2);
        $this->assertCount(0, $powerfulNeighbors);

        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b3);
        $this->assertCount(0, $powerfulNeighbors);

        $a3->setValue(1);
        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b2);
        $this->assertCount(0, $powerfulNeighbors);

        $b3->addProhibitedValue(1);
        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b2);
        $this->assertCount(2, $powerfulNeighbors);
    }

    public function testGetInsideCornersPowerfulNeighbors__oneOrMorNeighborsSolved()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 3, 3],
            [2, 2, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [0, 0, 0],
            [1, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $a2 = $board->getMutableCell(2, 'a');
        $b2 = $board->getMutableCell(2, 'b');
        $a3 = $board->cells[md5(3 . 'a')];

        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b2);
        $this->assertEmpty($powerfulNeighbors);

        $a2->setValue(1);
        $a3->unsetValue();
        $powerfulNeighbors = $board->getInsideCornersPowerfulNeighbors($b2);
        $this->assertEmpty($powerfulNeighbors);
    }

    public function testSolveInsideCornerCell()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 3, 3],
            [2, 2, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [0, 0, 0],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $a2 = $board->getMutableCell(2, 'a');
        $b2 = $board->getMutableCell(2, 'b');
        $b3 = $board->getMutableCell(3, 'b');
        $a3 = $board->getMutableCell(3, 'a');

        $board->solveInsideCornerCell($b2);
        $this->assertNull($b2->getValue());

        $a2->addProhibitedValue(1);
        $b3->addProhibitedValue(1);
        $board->solveInsideCornerCell($b2);
        $this->assertNull($b2->getValue());

        $a3->setValue(1, false);
        $board->solveInsideCornerCell($b2);
        $this->assertNotNull($b2->getValue());
    }

    public function testGroupIsPowerfulNeighbor()
    {
        $boardDescription = [
            [1, 1, 1],
            [2, 3, 3],
            [2, 2, 3],
        ];
        $boardPrefills = [
            [0, 0, 0],
            [0, 0, 0],
            [0, 0, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $group = $board->getCellGroup(2);
        $this->assertFalse($board->groupIsPowerfulNeighbor($group));

        $a3 = $board->getMutableCell(3, 'a');
        $a3->setValue(1, false);
        $this->assertFalse($board->groupIsPowerfulNeighbor($group));

        $a2 = $board->getMutableCell(2, 'a');
        $a2->addProhibitedValue(1);
        $this->assertTrue($board->groupIsPowerfulNeighbor($group));

        $a2->prohibitedValues = [2];
        $this->assertFalse($board->groupIsPowerfulNeighbor($group));
    }

    public function testSolve()
    {
        $solution = $this->board->solve();
        $this->assertIsArray($solution);
    }

    private function resetBoardSmall()
    {
        $boardDescription = [
            [ 1, 1, 1, 2, 3, 3],
            [ 4, 4, 5, 2, 3, 6],
            [ 4, 5, 5, 2, 6, 6],
            [ 7, 8, 8, 9,10,10],
            [ 7, 8,11, 9, 9,10],
            [ 7,11,11,12,12,12],
        ];
        $boardPrefills = [
            [ 3, 0, 0, 0, 0, 0],
            [ 0, 2, 0, 0, 0, 3],
            [ 0, 0, 0, 2, 0, 0],
            [ 3, 0, 0, 0, 0, 2],
            [ 0, 0, 3, 0, 0, 0],
            [ 1, 0, 0, 1, 0, 0],
        ];
        $this->board = new AllOrOneBoard($boardDescription, $boardPrefills);
    }
}
