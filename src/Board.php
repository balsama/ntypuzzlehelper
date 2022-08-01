<?php

namespace Balsama\Nytpuzzlehelper;

use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;

class Board
{

    private array $boardDescription;
    public array $cells;
    private array $groupSizes;
    private ?array $savedState = null;

    public function __construct(array $boardDescription, array $boardPrefills)
    {
        $this->boardDescription = $boardDescription;
        $this->cellify();
        $this->groupify();
        $this->groupSizes = $this->getGroupSizes();
        $this->prefill($boardPrefills);
    }

    private function doSolve($confident = true)
    {
        $this->fillAllCellsPossibleProhibitedValues();
        $newlyAssigned = true;
        while ($newlyAssigned) {
            $newlyAssigned = $this->assignUnambiguousCells($confident);
            $state = $this->getCurrentState();
            $this->fillAllCellsPossibleProhibitedValues();
        }
        if ($done = $this->checkIfDone()) {
            return $done;
        }
        $newlyAssigned = true;
        while ($newlyAssigned) {
            $newlyAssigned = $this->assignCellsWithUniquePossibilities($confident);
            $this->fillAllCellsPossibleProhibitedValues();
        }
        if ($done = $this->checkIfDone()) {
            return $done;
        }

        return false;
    }

    public function solve(array $state, bool $strict = false)
    {
        $this->doSolve();
        $state = $this->getCurrentState();

        if ($state = $this->checkIfDone()) {
            return $state;
        }

        // @NOTE: The solver works flawlessly up until this point

        // 1. Assign ~a random~ the first unsolved cell a (possible) value
        $state = $this->getCurrentState();
        $nth = 1;
        $cell = $this->findNthUnsolvedCell($nth);
        $possibleValues = array_diff($cell->possibleValues, $cell->prohibitedValues);

        foreach ($possibleValues as $possibleValue) {
            $cell->setValue($possibleValue, true);
            $cell->previousAttempts[] = $possibleValue;
            // 2. Do all this shit again.
            $this->doSolve(false);
            $state = $this->getCurrentState();
            if ($state = $this->checkIfDone()) {
                return $state;
            }
            $foo = 21;
        }

        $foo = 21;
        // 3. If not success
        //    a. Assign the same unsolved cell a different (possible) value
        //    b. Do all this shit again

    }

    private function findNthUnsolvedCell($nth)
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if ($cell->valueIsMutable) {
                $unsolvedCell[] = $cell;
            }
        }

        return $unsolvedCell[$nth - 1];
    }

    private function checkIfDone()
    {
        $state = $this->getCurrentState();
        foreach ($state as $row) {
            foreach ($row as $cell) {
                if ($cell === null) {
                    return false;
                }
            }
        }
        return $state;
    }

    /**
     * Finds cells in a group that have unique possibilities. I.e., cells that _can_ have a value where no other cells
     *   report that that value is allowed withing that group.
     * @return void
     */
    private function assignCellsWithUniquePossibilities(bool $confident = true)
    {
        $newlyAssignedCount = 0;
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if (!$cell->valueIsMutable) {
                continue;
            }

            $thisCellsValidValues = array_diff($cell->possibleValues, $cell->prohibitedValues);

            $group = $this->getCellGroup($cell->getGroup());
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
                $foo = 21;
                $newlyAssignedCount++;
                throw new \Exception('Time to write this.');
            }
        }
        return $newlyAssignedCount;
    }
    private function assignUnambiguousCells(bool $confident = true)
    {
        $newlyAssignedCount = 0;
        foreach ($this->cells as $cell) {
            $this->fillAllCellsPossibleProhibitedValues();
            $testId = md5('4b');
            if ($cell->cellId == $testId) {
                $foo = 21;
            }
            /* @var Cell $cell */
            if (!$cell->valueIsMutable) {
                continue;
            }
            $valueIntersect = array_diff($cell->possibleValues, $cell->prohibitedValues);
            if (count($valueIntersect) === 1) {
                if ($confident) {
                    $mutable = false;
                }
                else {
                    $mutable = true;
                }
                $cell->setValue(reset($valueIntersect), $mutable);
                $newlyAssignedCount++;
            }
        }
        return $newlyAssignedCount;
    }
    private function fillAllCellsPossibleProhibitedValues()
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $this->fillPossibleValues($cell);
            $this->fillProhibitedValues($cell);
            if ($cell->cellId == md5('2d')) {
                $fo = 21;
            }
        }
    }
    private function fillPossibleValues(Cell $cell)
    {
        $cell->possibleValues = $this->getCellPossibleValues($cell);
    }
    private function fillProhibitedValues($cell)
    {
        $cell->prohibitedValues = $this->getCellDisallowedValues($cell);
    }

    public function getGroupSizes(): array
    {
        $combined = [];
        foreach ($this->boardDescription as $row) {
            $combined = array_merge($row, $combined);
        }
        return array_count_values($combined);
    }

    public function getMutableCell(int $row, string $column): Cell
    {
        if (!$this->cells[md5($row . $column)]->valueIsMutable) {
            throw new \Exception("Cell $row$column is not mutable.");
        }
        return $this->cells[md5($row . $column)];
    }

    /**
     * @param int $row
     * @param string $column
     * @return array|int
     *   0 if already definitively set.
     */
    public function getCellPossibleValues(cell $cell)
    {
        $cellGroup = $this->getCellGroup($cell->getGroup());

        return $cellGroup->getRemainingNumbersToBePlaced();
    }

    public function findDisallowedXValues(Cell $cell): array
    {
        $state = $this->getCurrentState();
        $disallowedXValues = [];
        $columnLetter = $cell->getColumn();
        $rowNumber = $cell->getRow();
        $cellRow = $this->getCellRow($rowNumber);
        $xLength = chr(97 + (count($cellRow) - 1));

        $distance = 0;
        for ($tryColumn = 'a'; $tryColumn <= $xLength; $tryColumn++) {
            if ($tryColumn == $columnLetter) {
                $distance++;
                continue;
            }
            $theCellValueToTest = $this->cells[md5($rowNumber.$tryColumn)]->getValue();
            $distance = $this->getXDistance($columnLetter, $tryColumn);
            if ($distance <= $theCellValueToTest) {
                $disallowedXValues[] = $theCellValueToTest;
            }
        }

        return array_unique($disallowedXValues);
    }

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
            $theCellValueToTest = $this->cells[md5($tryRow.$columnLetter)]->getValue();
            $distance = abs($tryRow - $rowNumber);
            if ($distance <= $theCellValueToTest) {
                $disallowedYValues[] = $theCellValueToTest;
            }
        }

        return array_unique($disallowedYValues);
    }

    public function getCellDisallowedValues(Cell $cell)
    {
        $disallowedXValues = $this->findDisallowedXValues($cell);
        $disallowedYValues = $this->findDisallowedYValues($cell);

        return array_merge($disallowedXValues, $disallowedYValues);
    }

    public function attemptToSolveCell(int $row, string $column, $strict = false)
    {
        $cellId = md5($row . $column);
        $allPossibleValues = $this->getCellPossibleValues($this->cells[$cellId]);
        $disallowedValues = $this->getCellDisallowedValues($this->cells[$cellId]);

        $cell = $this->getMutableCell($row, $column);
        $validValues = array_diff($allPossibleValues, $disallowedValues);
        if ($strict) {
            $validValues = array_diff($validValues, $cell->previousAttempts);
        }

        if (!$validValues) {
            $foo = 21;
            $this->savedState = $this->getCurrentState();
            $this->wipeUncertainValues();
            $this->solve($this->getCurrentState());
            throw new \Exception('Time to write this');
            // Need to go back and change ...something. But What 🤔
        }

        else {
            $selectedValueToTry = array_rand($validValues);
            $result = $cell->setValue($selectedValueToTry);
            $cell->previousAttempts[] = $selectedValueToTry;
            $this->fillAllCellsPossibleProhibitedValues();

            return $result;
        }
    }

    public function getCurrentState(): array
    {
        $state = [];
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $state[($cell->getRow() - 1)][] = $cell->getValue();
        }
        return $state;
    }

    private function getCellGroup($groupId)
    {
        foreach ($this->cells as $cell) {
            if ($cell->getGroup() === $groupId) {
                $groupCells[$cell->cellId] = $cell;
            }
        }
        return new Group($groupCells);
    }

    private function getCellColumn(string $column)
    {
        $columnCells  = array_filter($this->cells, function($cell) use ($column) {
            $cellColumn = $cell->getColumn();
            if ($cellColumn === $column) {
                return true;
            }
            return false;
        });

        return $columnCells;
    }

    public function getCellRow(int $row)
    {
        $rowCells = array_filter($this->cells, function($cell) use ($row) {
            $cellrow = $cell->getRow();
            if ($cellrow === $row) {
                return true;
            }
            return false;
        });

        return $rowCells;
    }

    private function cellify()
    {
        $currentRow = 1;
        foreach ($this->boardDescription as $row) {
            $currentColumn = 'a';
            foreach ($row as $cell) {
                $this->cells[md5($currentRow . $currentColumn)] = new Cell($currentRow, $currentColumn, $cell, null);
                $currentColumn++;
            }
            $currentRow++;
        }
    }

    private function groupify()
    {
        return;
    }

    private function wipeUncertainValues()
    {
        foreach ($this->cells as $cell) {
            if ($cell->valueIsMutable) {
                $cell->value = null;
            }
        }
        return;
    }

    private function decrementLetter($letter) {
        return chr(ord($letter) - 1);
    }

    private function prefill($prefills)
    {
        $rowNumber = 1;
        foreach ($prefills as $row) {
            $cellNumber = 'a';
            foreach ($row as $cell) {
                if ($cell !== 0) {
                    $this->getMutableCell($rowNumber, $cellNumber)->setValue($cell, false);
                }
                $cellNumber++;
            }
            $rowNumber++;
        }
        return;
    }

    public function getXDistance(string $column1, string $column2): int
    {
        return abs( ord($column1) - ord($column2));
    }

}