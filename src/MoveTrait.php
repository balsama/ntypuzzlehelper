<?php


use Balsama\Nytpuzzlehelper\Cell;

trait MoveTrait {

    private static function moveX($start, int $distance): string
    {
        $ord = ord($start) + $distance;
        $character = chr($ord);
        return $character;
    }

    public function getNeighborIdByCoordinate(Cell $cell, int $x = 0, int $y = 0): string
    {
        $column = $this->moveX($cell->getColumn(), $x);
        $row = ($cell->getRow() + $y);
    }

    /**
     * @param Cell $cell
     * @param $direction
     *   Must be one of 'x' or 'y'
     * @param int $distance
     *   Positive (right / down) or negative (left / up) integer.
     * @return ?string
     */
    public static function getNeighbor(Cell $cell, $direction, int $distance): ?string
    {
        if (!in_array($direction, ['x', 'y'])) {
            throw new \Exception('Expected one of "x" or "y" for $direction.');
        }
        if ($direction === 'x') {
            $row = $cell->getRow();
            $column = self::moveX($cell->getColumn(), $distance);
            if ($column === '`') {
                throw new Exception("I don't know what ` means here. Let's find out.");
                return null;
            }
        }
        if ($direction === 'y') {
            $row = ($cell->getRow() + $distance);
            if ($row < 1) {
                return null;
            }
            // Removed logic around detecting if a requested cell is out of the Board becuase a Board isn't always
            // available.
            $column = $cell->getColumn();
        }

        return md5($row . $column);
    }
}