<?php

namespace Nytpuzzlehelper;

use Balsama\Nytpuzzlehelper\Shape;

class ShapeTest extends \PHPUnit\Framework\TestCase
{
    private Shape $shape;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shape = new Shape(
            3,
            2,
            [
                [1, 1],
                [2, 1],
                [3, 1],
                [2, 2],
            ],
        );
    }

    public function testGetArea()
    {
        $shape = new Shape(
            3,
            2,
            [
                [1, 1],
                [2, 1],
                [3, 1],
                [2, 2],
            ],
        );

        $this->assertEquals(4, $shape->getArea());
    }

    /**
    public function testRotate()
    {
        $originalWidth = $this->shape->xLength;
        $rotated = $this->shape->rotate();
        $rotatedWidth = $rotated->xLength;
        $updatedOriginalWidth = $this->shape->xLength;

        $this->assertEquals(3, $originalWidth);
        $this->assertEquals(2, $rotatedWidth);

        $this->assertEquals(2, $updatedOriginalWidth);
        $foo = 21;
    }
     */

    public function testGetRotatedShapeMap()
    {
        $shape = $this->shape;
        $shape->getRotatedShapeMap([1, 2, 3]);
    }
}