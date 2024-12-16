<?php

namespace Balsama\Nytpuzzlehelper;

class Shape
{
    /**
     * @param int $xLength
     * @param int $yLength
     * @param array $ocuupiedSquares
     *   an array of xy coordinates from the upper left (upper left = [1,1]) of squares that are occuped by the shape
     *   within the box.
     */
    public function __construct(
        public int $xLength,
        public int $yLength,
        public array $ocuupiedSquares,
    ) {
    }

    public function getArea(): int
    {
        return count($this->ocuupiedSquares);
    }

    /**
     * Rotates 90 degrees clockwise.
     */
    public function getRotatedVersion(): self
    {
        $this->swapVars($this->xLength, $this->yLength);
        $rotated = new Shape(
            $this->yLength,
            $this->xLength,
            $this->getRotatedShapeMap,
        );
        return $this;
    }

    public function getRotatedShapeMap(array $shapeMap) :array
    {
        // Get last row

        // Put that row as the first column

        // Check to see if there's a row before it

            // if so, do it again
    }

    private function swapVars(&$a, &$b)
    {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }
}
