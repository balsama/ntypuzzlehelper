<?php

namespace Balsama\Nytpuzzlehelper;

class Group
{
    public function __construct(public array $cells)
    {
    }

    public function getGroupSize(): int
    {
        return count($this->cells);
    }

    public function getSolvedCellsCount(): int
    {
        $cellsSolvedCount = 0;
        foreach ($this->cells as $cell) {
            if ($cell->value) {
                $cellsSolvedCount++;
            }
        }
        return $cellsSolvedCount;
    }

    public function getGroupWidth(): int
    {
        $cellColumnMin = null;
        $cellColumnMax = null;
        /* @var Cell $cell */
        foreach ($this->cells as $cell) {
            $cellColumn = $cell->getColumn();

            if (!$cellColumnMin) {
                $cellColumnMin = $cellColumn;
            }
            if (!$cellColumnMax) {
                $cellColumnMax = $cellColumn;
            }

            if ($cellColumn < $cellColumnMin) {
                $cellColumnMin = $cellColumn;
            }
            if ($cellColumn > $cellColumnMax) {
                $cellColumnMax = $cellColumn;
            }
        }

        return $this->getXDistance($cellColumnMin, $cellColumnMax) + 1;
    }

    public function getGroupHeight(): int
    {
        $cellRowMin = null;
        $cellRowMax = null;
        /* @var Cell $cell */
        foreach ($this->cells as $cell) {
            $cellRow = $cell->getRow();

            if (!$cellRowMin) {
                $cellRowMin = $cellRow;
            }
            if (!$cellRowMax) {
                $cellRowMax = $cellRow;
            }

            if ($cellRow < $cellRowMin) {
                $cellRowMin = $cellRow;
            }
            if ($cellRow > $cellRowMax) {
                $cellRowMax = $cellRow;
            }
        }

        return ($cellRowMax - $cellRowMin) + 1;
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
     * Gets the distance between two alphabetical letters.
     *
     * @example
     *   getXDistance('f', 'b')
     *   // returns 4
     * @example
     *   getDistance('a', 'b')
     *   // returns 1
     */
    public function getXDistance(string $column1, string $column2): int
    {
        return abs(ord($column1) - ord($column2));
    }
}
