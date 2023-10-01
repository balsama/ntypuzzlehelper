<?php

namespace Balsama\Nytpuzzlehelper\Solvers;

use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Group;

/**
 * A Bomb is a cell that would preclude all other cells in the group from being marked.
 */
class IsBomb
{
    const KILLED = 3;
    public string $bombCellId;
    public function __construct(
        public Group $group,
        int $cellRow,
        string $cellColumn,
    ) {
        $this->bombCellId = $cellColumn . $cellRow;
    }

    public function solve(): bool
    {
        foreach ($this->group->cells as $cell) {
            /* @var \Balsama\Nytpuzzlehelper\Cell $cell */
            if ($cell->getId() === $this->bombCellId) {
                $cell->setValue(self::KILLED);
            }
        }
        $counts = array_count_values($this->group->getSolvedCellsValues());
        $killed = $counts[self::KILLED];
        if ($this->group->getGroupSize() - $killed === 1) {
            return true;
        }
        return false;
    }

    public function twoCellsTouch(Cell $cellA, Cell $cellB): bool
    {}

}
