<?php

namespace Balsama\Nytpuzzlehelper;

class Board
{

    private array $boardDescription;
    public array $cells;
    private array $groupSizes;

    public function __construct(array $boardDescription)
    {
        $this->boardDescription = $boardDescription;
        $this->cellify();
        $this->groupify();
        $this->groupSizes = $this->getGroupSizes();
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

    public function getCellPossibleValues(int $row, string $column)
    {
        $cellId = md5($row . $column);
        $cell = $this->cells[$cellId];
        if (!$this->cells[$cellId]->valueIsMutable) {
            return 'Already definitively set';
        }

        $groupSize = $this->groupSizes[$cell->getGroup()];
        $cellGroup = $this->getCellGroup($cell->getGroup());

        $ntbr = $cellGroup->getRemainingNumbersToBePlaced();

        $foo = 21;
        // Region?
        // Region size?
        // Region values taken already?
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

}