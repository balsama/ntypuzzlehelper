<?php

namespace Nytpuzzlehelper;

use Balsama\Nytpuzzlehelper\Coordinate;
use PHPUnit\Framework\TestCase;

class CoordinateTest extends TestCase
{
    public function testGetNeighborByRelativeCoordinate()
    {
        $coordinate = new Coordinate(3, 3);
        $relativeCoordinate = new Coordinate(1, 1);
        $neighbor = $coordinate->getNeighborByRelativeCoordinate($relativeCoordinate);

        $this->assertEquals('e', $neighbor->legacyColumn);
        $this->assertEquals(md5(4 . 'e'), $neighbor->legacyId);
    }

    public function testWeight()
    {
        $coordinate = new Coordinate(3, 3);
        $this->assertEquals(6, $coordinate->weight);
    }

    public function testGetAlphaColumn()
    {
        $coordinate = new Coordinate(1, 1);
        $this->assertEquals('a', $coordinate->legacyColumn);
        $coordinate = new Coordinate(3, 3);
        $this->assertEquals('c', $coordinate->legacyColumn);
        $coordinate = new Coordinate(4, 3);
        $this->assertEquals('d', $coordinate->legacyColumn);
    }

}