<?php

namespace Balsama\Nytpuzzlehelper\RippleEffect;

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Exception\UnableToSolveException;

/**
 * @rules
 *   Fill the cells of each heavily outlined area with the digits from 1 to n, where n is the number of cells in the
 *   area. If two identical numbers appear in the same row of column, at least that many cells must separate them.
 * @startdate
 *   2022-June?
 * @endate
 *   ??
 */
class RippleEffectBoard extends Board
{
    public function solve()
    {
        $this->setDiscoverableValues();
        if ($state = $this->checkIfDone()) {
            return $state;
        }

        $unsolved = $this->findAllUnsolvedCells();
        foreach ($unsolved as $unsolvedCell) {
            /* @var Cell $unsolvedCell */
            $this->recalculateAllCellsValidValues();
            $currentKnownAllowedValues = $unsolvedCell->getCurrentKnownAllowedValues();

            foreach ($currentKnownAllowedValues as $possibleValue) {
                $unsolvedCell->setValue($possibleValue, true);
                $unsolvedCell->previousAttempts[] = $possibleValue;
                $this->setDiscoverableValues(false);
                if ($state = $this->checkIfDone()) {
                    return $state;
                }
                $this->wipeUncertainValues();
            }
        }

        $state = $this->getCurrentState();
        throw new UnableToSolveException("Unable to solve board.", 0, null, $unsolvedCell, $state);
    }

    private function setDiscoverableValues($confident = true)
    {
        $this->recalculateAllCellsValidValues();
        $newlyAssigned = true;
        while ($newlyAssigned) {
            $newlyAssigned = $this->assignUnambiguousCells($confident);
            $this->recalculateAllCellsValidValues();
        }
        if ($done = $this->checkIfDone()) {
            return $done;
        }
        $newlyAssigned = true;
        while ($newlyAssigned) {
            $newlyAssigned = $this->assignCellsWithUniquePossibilities($confident);
            $this->recalculateAllCellsValidValues();
        }
        if ($done = $this->checkIfDone()) {
            return $done;
        }

        return false;
    }

    /**
     * Values that a given cell canNOT have based on the known value of and distance to other cells in the row.
     */
    public function findDisallowedXValues(Cell $cell): array
    {
        $disallowedXValues = [];
        $columnLetter = $cell->getColumn();
        $rowNumber = $cell->getRow();
        $cellRow = $this->getCellRow($rowNumber);
        $xLength = chr(97 + (count($cellRow) - 1));

        for ($tryColumn = 'a'; $tryColumn <= $xLength; $tryColumn++) {
            if ($tryColumn == $columnLetter) {
                continue;
            }
            $theCellValueToTest = $this->cells[md5($rowNumber . $tryColumn)]->getValue();
            $distance = $this->getXDistance($columnLetter, $tryColumn);
            if ($distance <= $theCellValueToTest) {
                $disallowedXValues[] = $theCellValueToTest;
            }
        }

        return array_unique($disallowedXValues);
    }

    /**
     * Values that a given cell canNOT have based on the known value of and distance to other cells in the column.
     */
    public function findDisallowedYValues(Cell $cell): array
    {
        $disallowedYValues = [];
        $columnLetter = $cell->getColumn();
        $rowNumber = $cell->getRow();
        $cellColumn = $this->getCellColumn($columnLetter);
        $yHeight = count($cellColumn);

        for ($tryRow = 1; $tryRow < $yHeight; $tryRow++) {
            if ($tryRow == $rowNumber) {
                continue;
            }
            $theCellValueToTest = $this->cells[md5($tryRow . $columnLetter)]->getValue();
            $distance = abs($tryRow - $rowNumber);
            if ($distance <= $theCellValueToTest) {
                $disallowedYValues[] = $theCellValueToTest;
            }
        }

        return array_unique($disallowedYValues);
    }

    /**
     * Gets values that aren't at least the same distance away as the value.
     *
     * @see Rules
     */
    public function getCellProhibitedValues(Cell $cell): array
    {
        $disallowedXValues = $this->findDisallowedXValues($cell);
        $disallowedYValues = $this->findDisallowedYValues($cell);

        return array_merge($disallowedXValues, $disallowedYValues);
    }

    /**
     * Finds cells in a group that have unique possibilities. I.e., cells that _can_ have a value where no other cells
     *   report that that value is allowed withing that group.
     */
    private function assignCellsWithUniquePossibilities(bool $confident = true): int
    {
        $newlyAssignedCount = 0;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if (!$cell->valueIsMutable) {
                continue;
            }

            $thisCellsValidValues = array_diff($cell->possibleValues, $cell->prohibitedValues);

            $group = $this->getCellGroup($cell->getGroup());
            $newlyAssignedCount = 0;
            $otherCellsValidValues = [];
            foreach ($group->cells as $otherCell) {
                /* @var Cell $otherCell */
                if ($otherCell->cellId === $cell->cellId) {
                    continue;
                }
                $otherCellsValidValues[] = array_diff($cell->possibleValues, $cell->prohibitedValues);
            }
            $whittledDownPossibilities = array_diff($thisCellsValidValues, ...$otherCellsValidValues);
            if ($whittledDownPossibilities) {
                $newlyAssignedCount++;
                throw new \Exception('Time to write this.');
            }
        }
        return $newlyAssignedCount;
    }

    /**
     * Gets the values not already assigned in a group.
     */
    public function findGroupUnassignedValues(Cell $cell): array
    {
        $cellGroup = $this->getCellGroup($cell->getGroup());
        return $cellGroup->getRemainingNumbersToBePlaced();
    }

    /**
     * Assigns cells that have only one possible solution.
     *
     * @param bool $confident
     *   Whether or not we have had to guess any values up to this point. Used to erase values later on that may have
     *     been based on guesses.
     * @return int
     * @throws \Exception
     */
    private function assignUnambiguousCells(bool $confident = true): int
    {
        $newlyAssignedCount = 0;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $this->recalculateAllCellsValidValues();
            if (!$cell->valueIsMutable) {
                continue;
            }
            $valueIntersect = array_diff($cell->possibleValues, $cell->prohibitedValues);
            if (count($valueIntersect) === 1) {
                if ($confident) {
                    $mutable = false;
                } else {
                    $mutable = true;
                }
                $cell->setValue(reset($valueIntersect), $mutable);
                $newlyAssignedCount++;
            }
        }
        return $newlyAssignedCount;
    }

    /**
     * Calculates and records the possible (allowed) values and prohibited values of cells.
     */
    private function recalculateAllCellsValidValues()
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $this->setCellPossibleValues($cell);
            $this->setCellProhibitedValues($cell);
        }
    }

    private function setCellPossibleValues(Cell $cell)
    {
        $cell->possibleValues = $this->findGroupUnassignedValues($cell);
    }
    private function setCellProhibitedValues($cell)
    {
        $cell->prohibitedValues = $this->getCellProhibitedValues($cell);
    }

}
