<?php

namespace Balsama\Nytpuzzlehelper;

class Board
{

    private array $boardDescription;
    public array $cells;

    public function __construct(array $boardDescription)
    {
        $this->boardDescription = $boardDescription;
        $this->cellify();
    }

    public function getRegionSizes(): array
    {
        $combined = [];
        foreach ($this->boardDescription as $row) {
            $combined = array_merge($row, $combined);
        }
        return array_count_values($combined);
    }

    public function getMutableCell(int $row, int $column): Cell
    {
        return $this->cells[md5($row . $column)];
    }

    private function cellify()
    {
        $currentRow = 1;
        foreach ($this->boardDescription as $row) {
            $currentColumn = 1;
            foreach ($row as $cell) {
                $this->cells[md5($currentRow . $currentColumn)] = new Cell($currentRow, $currentColumn, $cell, null);
                $currentColumn++;
            }
            $currentRow++;
        }
    }

    private function nthLetter($nth)
    {

    }

}