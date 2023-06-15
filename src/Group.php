<?php

namespace Balsama\Nytpuzzlehelper;

class Group
{
    public function __construct(public array $cells, public int $id)
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
}
