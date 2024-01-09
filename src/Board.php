<?php

namespace Balsama\Nytpuzzlehelper;

use Laminas\Text\Table\Decorator\Blank;
use MathieuViossat\Util\ArrayToTextTable;

/**
 * A basic board with groups/shape outlines.
 */
class Board
{
    private array $boardDescription;
    private array $boardPrefills;
    public array $cells;
    public array $groups;

    public function __construct(
        array $boardDescription,
        array $boardPrefills,
        array $boardShaded = [],
        public array $meta = ['puzzle_type' => 'unk', 'date' => 'unk']
    ) {
        $this->boardDescription = $boardDescription;
        $this->boardPrefills = $boardPrefills;
        $this->boardShaded = $boardShaded;
        $this->cellify();
        $this->recordGroups();
        $this->prefill($boardPrefills);
    }

    public function getPrettySolution()
    {
        $state = $this->getCurrentState();
        $renderer = new ArrayToTextTable($state);
        $renderer->setDecorator(new Blank());
        return $renderer->getTable();
    }

    public function getBoardDescription(): array
    {
        return $this->boardDescription;
    }

    public function getBoardShaded(): ?array
    {
        return $this->boardShaded;
    }

    public function getBoardPrefills(): array
    {
        return $this->boardPrefills;
    }

    protected function findAllUnsolvedCells(): array
    {
        $unsolved = [];
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if ($cell->valueIsMutable) {
                $unsolved[] = $cell;
            }
        }
        return $unsolved;
    }

    public function checkIfDone()
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

    public function getMutableCell(int $row, string $column): Cell
    {
        if (!$this->cells[md5($row . $column)]->valueIsMutable) {
            throw new \Exception("Cell $row$column is not mutable.");
        }
        return $this->cells[md5($row . $column)];
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

    public function getCellGroup($groupId): ?Group
    {
        $groupCells = [];
        foreach ($this->cells as $cell) {
            if ($cell->getGroup() === $groupId) {
                $groupCells[$cell->cellId] = $cell;
            }
        }
        if (!$groupCells) {
            return null;
        }
        return new Group($groupCells, $groupId);
    }

    /**
     * Gets an array of Cell objects for the provided column letter.
     * @param string $column
     * @return Cell[]
     */
    protected function getCellColumn(string $column): array
    {
        return array_filter($this->cells, function ($cell) use ($column) {
            $cellColumn = $cell->getColumn();
            if ($cellColumn === $column) {
                return true;
            }
            return false;
        });
    }

    /**
     * Gets an array of Cell objects for the provided row integer.
     *
     * @param int $row
     * @return Cell[]
     */
    public function getCellRow(int $row): array
    {
        return array_filter($this->cells, function ($cell) use ($row) {
            $cellrow = $cell->getRow();
            if ($cellrow === $row) {
                return true;
            }
            return false;
        });
    }

    /**
     * Creates Cell objects for each cell based on the $description array passed to the constructor.
     */
    private function cellify(): array
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

        return $this->cells;
    }

    /**
     * Fills the board with known values
     * @param $prefills
     *   An array of arrays representing rows of cells. Cells with a known value should contain the known value integer.
     */
    private function prefill($prefills): array
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
        return $this->getCurrentState();
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

    public function getBoardLastColumnAlpha(): string
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if ($cell->getRow() === 1) {
                $columns[] = $cell->getColumn();
            }
        }
        krsort($columns);
        return reset($columns);
    }

    /**
     * Clears out the value of any cell that is mutable.
     */
    protected function wipeUncertainValues(): int
    {
        $wipedCount = 0;
        foreach ($this->cells as $cell) {
            if ($cell->valueIsMutable) {
                $cell->setValue(null);
                $wipedCount++;
            }
        }
        return $wipedCount;
    }

    private function recordGroups(): void
    {
        $groupId = 1;
        while ($cellGroup = $this->getCellGroup($groupId)) {
            $this->groups[$groupId] = $cellGroup;
            $groupId++;
        }
    }
}
