<?php

namespace Balsama\Nytpuzzlehelper;

class Group
{
    public function __construct(public array $cells)
    {}

    public function getGroupSize()
    {
        return count($this->cells);
    }

    public function getSolvedCellsCount()
    {
        $cellsSolvedCount = 0;
        foreach ($this->cells as $cell)
        {
            if ($cell->value) {
                $cellsSolvedCount++;
            }
        }
        return $cellsSolvedCount;
    }

    public function getSolvedCellsValues()
    {
        $solvedCellsValues = [];
        foreach ($this->cells as $cell)
        {
            if ($cell->value) {
                $solvedCellsValues[] = $cell->value;
            }
        }
        return $solvedCellsValues;
    }

    public function getRemainingNumbersToBePlaced()
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