<?php

namespace Balsama\Nytpuzzlehelper\AllOrOne;

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Cell;
use http\Exception\InvalidArgumentException;
use Balsama\Nytpuzzlehelper\Group;

class AllOrOneBoard extends Board
{
    public array $groups;

    public function __construct(array $boardDescription, array $boardPrefills)
    {
        parent::__construct($boardDescription, $boardPrefills);
        $this->setGroups();
    }

    public function solve()
    {
        $this->fillInCants();
        foreach ($this->groups as $group) {
            $this->solveCantBeOthers($group);
        }
        $this->solveCellsWithTwoCants();
        print $this->getPrettySolution();
    }

    private function fillInCants()
    {
        foreach ($this->cells as $cell) {
            $this->fillBullyNeighborCants($cell);
        }
    }

    private function solveCellsWithTwoCants()
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if (count($cell->prohibitedValues) === 2) {
                $value = array_diff([1, 2, 3], $cell->prohibitedValues);
                if (count($value) !== 1) {
                    throw new \Exception('There should only be one possible value at this point');
                }
                $cell->setValue(reset($value), false);
            }
        }
    }

    public function fillBullyNeighborCants(Cell $cell)
    {
        $tNeighbors = array_filter($this->getTNeighbors($cell));
        foreach ($tNeighbors as $neighbor) {
            /* @var Cell $neighbor */
            if ($cell->getGroup() !== $neighbor->getGroup()) {
                if (!$neighbor->valueIsMutable) {
                    $cell->addProhibitedValue($neighbor->getValue());
                }
            }
        }
    }

    /**
     * Fills values of all cells in group if the group has exactly one known value and one of the remaining values is
     * excluded from both remaining cells.
     *
     * @param Group $group
     * @return void
     * @throws \Exception
     */
    public function solveCantBeOthers(Group $group)
    {
        if ($group->getSolvedCellsCount() === 1) {
            $allUnsolvedProhibited = [];
            foreach ($group->cells as $cell) {
                /* @var Cell $cell */
                if ($cell->valueIsMutable) {
                    $allUnsolvedProhibited = array_merge($allUnsolvedProhibited, $cell->prohibitedValues);
                } elseif (!$cell->valueIsMutable) {
                    $knownValue = $cell->getValue();
                }
            }
            $counts = array_count_values($allUnsolvedProhibited);
            foreach ($counts as $value => $count) {
                if ($count === 2) {
                    if ($count !== $knownValue) {
                        // DING DING DING
                        foreach ($group->cells as $cell) {
                            /* @var Cell $cell */
                            if ($cell->valueIsMutable) {
                                $cell->setValue($knownValue, false);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getTNeighbors(Cell $cell): array
    {
        $tNeighbors = [];
        $tNeighbors['r'] = $this->getNeighbor($cell, 'x', 1);
        $tNeighbors['d'] = $this->getNeighbor($cell, 'y', 1);
        $tNeighbors['l'] = $this->getNeighbor($cell, 'x', -1);
        $tNeighbors['u'] = $this->getNeighbor($cell, 'y', -1);
        return $tNeighbors;
    }

    private function getNeighbor(Cell $cell, $direction, int $distance): ?Cell
    {
        if (!in_array($direction, ['x', 'y'])) {
            throw new InvalidArgumentException('Expected one of "x" or "y" for $direction.');
        }
        if ($direction === 'x') {
            $row = $cell->getRow();
            $column = $this->moveX($cell->getColumn(), $distance);
            if ($column === '`') {
                return null;
            }
        }
        if ($direction === 'y') {
            $row = ($cell->getRow() + $distance);
            if ($row < 1) {
                return null;
            }
            $column = $cell->getColumn();
        }

        return $this->cells[md5($row . $column)];
    }

    private function moveX($start, int $distance): string
    {
        $ord = ord($start) + $distance;
        $character = chr($ord);
        return $character;
    }

    private function setGroups()
    {
        $this->groups = [];
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            $group = $this->getCellGroup($cell->getGroup());
            if (!in_array($group, $this->groups)) {
                $this->groups[$group->id] = $group;
            }
        }
    }
}
