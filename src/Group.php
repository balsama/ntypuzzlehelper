<?php

namespace Balsama\Nytpuzzlehelper;

use function PHPUnit\Framework\isFalse;

class Group
{
    public function __construct(
        public array $cells,
        public int $id
    ) {
    }

    public function getGroupSize(): int
    {
        return count($this->cells);
    }

    public function getSolvedCellsCount(): int
    {
        $cellsSolvedCount = 0;
        foreach ($this->cells as $cell) {
            if ($cell->getValue()) {
                $cellsSolvedCount++;
            }
        }
        return $cellsSolvedCount;
    }

    public function getSolvedCellsValues(): array
    {
        $solvedCellsValues = [];
        foreach ($this->cells as $cell) {
            if ($cell->getValue()) {
                $solvedCellsValues[] = $cell->getValue();
            }
        }
        return $solvedCellsValues;
    }

    public function getUnsolvedCells(): array
    {
        $unsolvedCells = [];
        foreach ($this->cells as $cell) {
            if (!$cell->getValue()) {
                $unsolvedCells[] = $cell;
            }
        }
        return $unsolvedCells;
    }

    public function getRemainingNumbersToBePlaced(): array
    {
        $solvedCellsValues = $this->getSolvedCellsValues();
        $groupSize = $this->getGroupSize();
        $remainingNumbersToBePlaced = [];
        $i = 1;
        while ($i <= $groupSize) {
            if (!in_array($i, $solvedCellsValues)) {
                $remainingNumbersToBePlaced[] = $i;
            }
            $i++;
        }
        // Return the unaccounted for numbers remaining in the group
        return $remainingNumbersToBePlaced;
    }

    /**
     * Is the group shaped like this or one of its rotations?
     * ◻︎◼︎◻︎◻︎◻︎◻︎
     * ◻︎◼︎◼︎◼︎◻︎◻︎
     * ◻︎◼︎◻︎◻︎◻︎◻︎
     *
     * @return bool
     */
    public function isSquareT(): bool
    {
        if ($this->getGroupSize() !== 5) {
            return false;
        }
        if ($this->getGroupHeight() != 3) {
            return false;
        }
        if ($this->getGroupWidth() != 3) {
            return false;
        }

        $upperLeft = $this->getCorner(true);
        if (!$upperLeft) {
            // This is an "⌟" L!
            return false;
        }
        $lowerRight = $this->getCorner(false);
        if (!$lowerRight) {
            // This is an "⌜" L!
            return false;
        }

        if ($this->getDistance($upperLeft->coordinate, $lowerRight->coordinate) === 3) {
            // Upper left is exactly 3 spaces away from lower right: No matter the orientation, This is a T.
            return true;
        }
        // This is an "⌞" or "⌝" L.
        return false;
    }

    /**
     * @param $upperLeft
     *   Whether to get the Upper Left corner (true) or lower right (false). You cannot get the upper right or lower
     *   left corner with this method.
     * @return ?Cell
     *   The upper left corner if $upperLeft is set to true.
     *   The lower right corner if $upperLeft is set to false.
     */
    public function getCorner($upperLeft = true): ?Cell
    {
        $sortedCells = $this->getWeightSortedCells();
        if ($upperLeft) {
            $corner = reset($sortedCells);
        } else {
            $corner = end($sortedCells);
        }

        $cornerWeight = $corner->weight;

        $cellsWithCornerWeight = [];
        foreach ($sortedCells as $cell) {
            if ($cell->weight == $cornerWeight) {
                $cellsWithCornerWeight[] = $cell;
            }
        }
        if (count($cellsWithCornerWeight) !== 1) {
            return null;
        }
        return $corner;
    }

    private function getWeightSortedCells(): array
    {
        $sortedCells = $this->cells;
        uasort($sortedCells, function ($a, $b) {
            return [$a->weight, $b->weight] <=> [$b->weight, $a->weight];
        });
        return $sortedCells;
    }

    public function getGroupWidth(): int
    {
        $lowestX = $this->getGroupLowestXValue();
        $highestX = $this->getGroupHighestXValue();
        $distance = (abs(ord($highestX) - ord($lowestX))) + 1;
        return $distance;
    }

    public function getGroupHeight(): int
    {
        $lowestY = $this->getGroupLowestYValue();
        $highestY = $this->getGroupHighestYValue();
        $height = ($highestY - $lowestY) + 1;
        return $height;
    }

    public function getGroupLowestYValue(): int
    {
        $lowestY = null;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $currentCellRow = $cell->getRow();
            if (($currentCellRow < $lowestY) || is_null($lowestY)) {
                $lowestY = $currentCellRow;
            }
        }
        return $lowestY;
    }

    public function getGroupHighestYValue(): int
    {
        $highestY = null;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $currentCellRow = $cell->getRow();
            if (($currentCellRow > $highestY) || is_null($highestY)) {
                $highestY = $currentCellRow;
            }
        }
        return $highestY;
    }

    public function getGroupLowestXValue(): string
    {
        $lowestX = null;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $currentCellColumn = $cell->getColumn();
            if (($currentCellColumn < $lowestX) || is_null($lowestX)) {
                $lowestX = $currentCellColumn;
            }
        }
        return $lowestX;
    }

    public function getGroupHighestXValue(): string
    {
        $highestX = null;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $currentCellColumn = $cell->getColumn();
            if (($currentCellColumn > $highestX) || is_null($highestX)) {
                $highestX = $currentCellColumn;
            }
        }
        return $highestX;
    }

    private function getDistance(Coordinate $c1, Coordinate $c2): int
    {
        $xDistance = abs($c1->column - $c2->column);
        $yDistance = abs($c1->row - $c2->row);
        return ($xDistance + $yDistance);
    }
}
