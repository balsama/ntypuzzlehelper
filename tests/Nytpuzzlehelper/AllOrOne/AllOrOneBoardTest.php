<?php

namespace Nytpuzzlehelper\AllOrOne;

use Balsama\Nytpuzzlehelper\AllOrOne\AllOrOneBoard;
use Balsama\Nytpuzzlehelper\Board;

class AllOrOneBoardTest extends \PHPUnit\Framework\TestCase
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

    public function testSolve()
    {
        $this->board->solve();
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