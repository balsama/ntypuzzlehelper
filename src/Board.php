<?php

namespace Balsama\Nytpuzzlehelper;

class Board
{

    private array $boardDescription;
    public array $cells;
    private array $groupSizes;

    public function __construct(array $boardDescription, array $boardPrefills)
    {
        $this->boardDescription = $boardDescription;
        $this->cellify();
        $this->groupify();
        $this->groupSizes = $this->getGroupSizes();
        $this->prefill($boardPrefills);
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
    public function getCellPossibleValues(int $row, string $column)
    {
        $cellId = md5($row . $column);
        $cell = $this->cells[$cellId];
        if (!$this->cells[$cellId]->valueIsMutable) {
            return 0;
        }

        $cellGroup = $this->getCellGroup($cell->getGroup());

        return $cellGroup->getRemainingNumbersToBePlaced();
    }

    public function attemptToSolve(int $row, string $column)
    {
        $possibleValues = $this->getCellPossibleValues($row, $column);
        if (!$possibleValues) {
            return new Result(null, 'no possibilities');
        }
        if (count($possibleValues) === 1) {
            $cell = $this->getMutableCell($row, $column);
            $cell->setValue(reset($possibleValues), false);

            return new Result(reset($possibleValues), 'confident');
        }
        else {
            throw new \Exception('Time to finish this method');
        }
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

}