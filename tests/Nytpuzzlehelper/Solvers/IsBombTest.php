<?php

namespace Nytpuzzlehelper\Solvers;

use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Group;
use Balsama\Nytpuzzlehelper\Solvers\IsBomb;

class IsBombTest extends \PHPUnit\Framework\TestCase
{
    public function testTwoCellsTouch()
    {
        $cellA = new Cell(1, 'a', 1);
        $cellB = new Cell(2, 'a', 1);
        $group = new Group(
            [$cellA, $cellB],
            1
        );
        $isbomb = new IsBomb($group, 1, 'a');
        $this->assertTrue($isbomb->solve());
        $foo = 21;
    }

    public function testMatchstick()
    {
        $matchstick = $this->getMatchstick();
        $isBombA = new IsBomb($matchstick, 1, 'a');
        $this->assertFalse($isBombA->solve());

        $matchstick = $this->getMatchstick();
        $isBombA = new IsBomb($matchstick, 1, 'b');
        $this->assertFalse($isBombA->solve());

        $matchstick = $this->getMatchstick();
        $isBombA = new IsBomb($matchstick, 1, 'a');
        $this->assertFalse($isBombA->solve());
    }

    public function testL()
    {
        $l = $this->getBigL();
        $isBomb = new IsBomb($l, 1, 'a');
        $this->assertFalse($isBomb->solve());
    }

    public function testLWith()
    {
        $l = $this->getBigL();
        $isBomb = new IsBomb($l, 1, 'a');
        $this->assertFalse($isBomb->solve());
    }

    private function getMatchstick()
    {
        $cellA = new Cell(1, 'a', 1);
        $cellB = new Cell(1, 'b', 1);
        $cellC = new Cell(1, 'c', 1);
        $group = new Group(
            [$cellA, $cellB, $cellC],
            1
        );
        return $group;
    }

    private function getBigL()
    {
        $cellA = new Cell(1, 'a', 1);
        $cellB = new Cell(1, 'b', 1);
        $cellC = new Cell(1, 'c', 1);

        $cellD = new Cell(1, 'c', 1);
        $cellE = new Cell(2, 'c', 1);
        $cellF = new Cell(3, 'c', 1);

        return new Group(
            [$cellA, $cellB, $cellC, $cellD, $cellE, $cellF],
            1
        );
    }
}
