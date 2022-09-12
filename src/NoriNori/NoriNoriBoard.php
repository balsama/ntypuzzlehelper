<?php

namespace Balsama\Nytpuzzlehelper\NoriNori;

use Balsama\Nytpuzzlehelper\Cell;

class NoriNoriBoard extends \Balsama\Nytpuzzlehelper\Board
{
    public function __construct(array $boardDescription, array $boardPrefills)
    {
        parent::__construct($boardDescription, $boardPrefills);
    }

    public function solve()
    {
        $this->fillTierOne();
        $state = $this->getCurrentState();
        $foo = 21;
    }

    /**
     * @definition
     *   Tier One: Groups with exactly two cells will always be a domino.
     */
    private function fillTierOne()
    {
        foreach ($this->groups as $group) {
            if ($group->getGroupSize() === 2) {
                /* @var \Balsama\Nytpuzzlehelper\Cell $cell */
                foreach ($group->cells as $cell) {
                    $cell->setValue(1, false);
                    // @todo I can't set the whole off limits because I haven't created the whole domino yet.
                    throw new \Exception('This needs to be worked out. The following will try to set an immutable cell on the second cell of the domino becaue the previous cell of the domino thought it was a regular neighbor.');
                    $this->setSurroundingOfflimits($cell);
                }
            }
        }
    }

    /**
     * @definition
     *   Tier Two: Groups with exactly three cells in an L-shape have three possible solutions only.
     */
    private function fillTierTwo()
    {
    }

    private function setSurroundingOffLimits(Cell $cell)
    {
        $surroundingCells = $this->getSurroundingCells($cell);
        foreach ($surroundingCells as $surroundingCell) {
            if ($surroundingCell) {
                $this->setOffLimits($surroundingCell);
            }
        }
    }

    private function setOffLimits(Cell $cell)
    {
        if (!$cell->valueIsMutable) {
            if ($cell->getValue() === 1) {
                // There should be a maximimum of one of these. We should count it somehow.
            }
            return;
        }
        $cell->setValue(0, false);
    }

    private function getSurroundingCells(Cell $cell)
    {
        foreach ($this->getSurroundingCellsIds($cell) as $cellsId) {
            $surroundingCells[$cellsId] = $this->cells[$cellsId];
        }
        return $surroundingCells;
    }

    private function getSurroundingCellsIds(Cell $cell)
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        return [
            'top' => md5(($row - 1) . $column),
            'right' => md5($row . (chr(ord($column) + 1))),
            'bottom' => md5(($row + 1) . $column),
            'left' => md5($row . (chr(ord($column) - 1))),
        ];
    }
}
