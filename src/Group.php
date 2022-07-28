<?php

namespace Balsama\Nytpuzzlehelper;

class Group
{
    public function __construct(public array $cells)
    {}

    public function getGroupCount()
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
        $remainingNumberCount = $this->getSolvedCellsCount() - count($this->getSolvedCellsValues());
        // Return the unaccounted for numbers remaining in the group
        $foo = 21;
    }
}