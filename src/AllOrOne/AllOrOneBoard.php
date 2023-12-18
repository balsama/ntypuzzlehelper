<?php

namespace Balsama\Nytpuzzlehelper\AllOrOne;

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Exception\UnableToSolveException;
use http\Exception\InvalidArgumentException;
use Balsama\Nytpuzzlehelper\Group;

/**
 * @rules
 *   Place a digit from 1-3 in each cell so tat each outilned region contains either all teh same digit or all different
 *   digits. If two cells are separated by a bold region boundary, they must contain different digits.
 * @startdate
 *   2023-May 28
 * @endate
 *   ??
 */
class AllOrOneBoard extends Board
{
    public function solve($unsolvedCountStart = null): array
    {
        $this->fillInCants();
        $this->solveCells();

        $unsolved = $this->findAllUnsolvedCells();
        if ($unsolved) {
            if ($unsolvedCountStart === count($unsolved)) {
                throw new UnableToSolveException(
                    'Unable to solve puzzle.',
                    0,
                    null,
                    null,
                    $this->getCurrentState()
                );
            }
            return $this->solve(count($unsolved));
        }

        return $this->getCurrentState();
    }

    private function fillInCants(): void
    {
        foreach ($this->cells as $cell) {
            $this->fillBullyNeighborCants($cell);
        }
        foreach ($this->groups as $group) {
            $this->fillContBeOnesCants($group);
        }
    }

    private function solveCells(): void
    {
        foreach ($this->groups as $group) {
            $this->solveCantBeOthers($group);
            $this->solveLastRemaining($group);
        }
        foreach ($this->cells as $cell) {
            $this->solveCellWithTwoCants($cell);
            $this->solveInsideCornerCell($cell);
        }
    }

    private function solveCellWithTwoCants(Cell $cell): void
    {
        if (count($cell->prohibitedValues) === 2) {
            $value = array_diff([1, 2, 3], $cell->prohibitedValues);
            if (count($value) !== 1) {
                throw new \Exception('There should only be one possible value at this point');
            }
            if (!$cell->valueIsMutable) {
                return;
            }
            $cell->setValue(reset($value), false);
        }
    }

    /**
     * Adds prohibited values to cells with adjacent cells in different regions that are solved.
     */
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
     * For groups that 1) Have exactly one cell solved and 2) Have one unsolved cell that prohibits the value of the
     * solved cell: we know that the cells must each contain a different digit. So we can prohibit the value fo the
     * solved cell from the unsolved cells that don't already prohibit it.
     */
    public function fillContBeOnesCants(Group $group)
    {
        if ($group->getSolvedCellsCount() !== 1) {
            return;
        }

        foreach ($group->cells as $cell) {
            /* @var Cell $cell */
            if (!$cell->valueIsMutable) {
                $knownValue = $cell->getValue();
                $solvedCellId = $cell->cellId;
                continue;
            }
        }

        $unsolvedCells = [];
        foreach ($group->cells as $cell) {
            /* @var Cell $cell */
            if ($cell->cellId !== $solvedCellId) {
                $unsolvedCells[] = $cell;
            }
        }

        foreach ($unsolvedCells as $unsolvedCell) {
            /* @var Cell $unsolvedCell */
            if (in_array($knownValue, $unsolvedCell->prohibitedValues)) {
                $unsolvedCells[0]->addProhibitedValue($knownValue);
                $unsolvedCells[1]->addProhibitedValue($knownValue);
            }
        }
    }

    /**
     * Fills values of all cells in group if the group has exactly one known value and one of the remaining values is
     * excluded from both remaining cells since we know that all cells must have the same value.
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
                    if ($value !== $knownValue) {
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

    /**
     * Inside corners (that is, cells that have two adjacent cells from the same group) can have a known value even when
     * neither of the adjacent cells are solved if: 1) The adjacent group's corner is solved 2) at least one of the
     * other two cells prohibits the value of the solved corner.
     */
    public function solveInsideCornerCell(Cell $cell)
    {
        if ($cell->getValue()) {
            return;
        }
        $insideCornersPowerfulNeighbors = $this->getInsideCornersPowerfulNeighbors($cell);
        if (!$insideCornersPowerfulNeighbors) {
            return;
        }

        $neighborsProhibitedValues = [];
        foreach ($insideCornersPowerfulNeighbors as $insideCornersPowerfulNeighbor) {
            /* @var Cell $insideCornersPowerfulNeighbor */
            $neighborsProhibitedValues = array_merge(
                $neighborsProhibitedValues,
                $insideCornersPowerfulNeighbor->prohibitedValues
            );
        }

        if (count(array_unique($neighborsProhibitedValues)) === 1) {
            $cell->setValue(reset($neighborsProhibitedValues), false);
        }
    }

    /**
     * Gets two adjacent is they are from the same group and meet the criteria for being powerful. That is: 1) The group
     * is L-shaped 2) The corner is solved 3) one of the unsolved cells prohibits the solved cell's value.
     */
    public function getInsideCornersPowerfulNeighbors(Cell $cell): array
    {
        $tNeighbors = $this->getTNeighbors($cell);
        foreach ($tNeighbors as $direction => $tNeighbor) {
            /* @var Cell $tNeighbor */
            $tNeighborGroups[$direction] = $tNeighbor->getGroup();
        }
        $counts = array_count_values($tNeighborGroups);
        foreach ($counts as $group => $count) {
            if ($count === 2) {
                if ($group !== $cell->getGroup()) {
                    $powerfulNeighborGroupId = $group;
                }
            }
        }

        if (!isset($powerfulNeighborGroupId)) {
            return [];
        }

        if (!$this->groupIsPowerfulNeighbor($this->getCellGroup($powerfulNeighborGroupId))) {
            return  [];
        }

        $powerfulNeighbors = array_filter($tNeighbors, function ($tn) use ($powerfulNeighborGroupId) {
            if ($tn->getGroup() === $powerfulNeighborGroupId) {
                return true;
            }
            return false;
        });
        return $powerfulNeighbors;
    }

    /**
     * Validates:
     *   1. Group has exactly one solved cell.
     *   2. The solved cell is the corner.
     *   3. At least one of the unsolved cells prohibits the solved cell's value.
     *
     * The non-corner cells of a Group like this are known as "powerful neighbors".
     */
    public function groupIsPowerfulNeighbor(Group $group): bool
    {
        if ($group->getSolvedCellsCount() !== 1) {
            return false;
        }

        foreach ($group->cells as $groupCell) {
            /* @var Cell $groupCell */
            if ($this->cellIsCorner($groupCell)) {
                if ($groupCell->getValue() === null) {
                    return false;
                }
                $cornerValue = $groupCell->getValue();
                break;
            }
        }

        foreach ($group->cells as $groupCell) {
            /* @var Cell $groupCell */
            if (!$this->cellIsCorner($groupCell)) {
                if (in_array($cornerValue, $groupCell->prohibitedValues)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function cellIsCorner(Cell $cell): bool
    {
        $tNeighbors = $this->getTNeighbors($cell);
        $tNeighborsInGroup = 0;
        foreach ($tNeighbors as $tNeighbor) {
            /* @var Cell $tNeighbor */
            if ($tNeighbor->getGroup() === $cell->getGroup()) {
                $tNeighborsInGroup++;
            }
        }

        if ($tNeighborsInGroup === 2) {
            return true;
        }
        return false;
    }

    /**
     * Solves the last remaining unsolved cell of a group by process of elimination.
     */
    public function solveLastRemaining(Group $group)
    {
        if ($group->getSolvedCellsCount() !== 2) {
            return;
        }

        foreach ($group->cells as $cell) {
            /* @var Cell $cell */
            if ($cell->getValue()) {
                $solvedValues[] = $cell->getValue();
            } else {
                $unsolvedCell = $cell;
            }
        }
        $filtered = array_unique($solvedValues);
        if (count($filtered) === 1) {
            // Two have same value, so unsolved cell must have the same.
            $unsolvedCell->setValue(reset($filtered), false);
        } else {
            // Two of three possible values are used, so remaining cell must be remaining value.
            $unusedValue = array_diff([1, 2, 3], $solvedValues);
            $unsolvedCell->setValue(reset($unusedValue), false);
        }
    }

    public function getTNeighbors(Cell $cell): array
    {
        $tNeighbors = [];
        $tNeighbors['r'] = $this->getNeighbor($cell, 'x', 1);
        $tNeighbors['d'] = $this->getNeighbor($cell, 'y', 1);
        $tNeighbors['l'] = $this->getNeighbor($cell, 'x', -1);
        $tNeighbors['u'] = $this->getNeighbor($cell, 'y', -1);
        return array_filter($tNeighbors);
    }

    /**
     * @param Cell $cell
     * @param $direction
     *   Must be one of 'x' or 'y'
     * @param int $distance
     *   Positive (right / down) or negative (left / up) integer.
     * @return Cell|null
     */
    private function getNeighbor(Cell $cell, $direction, int $distance): ?Cell
    {
        if (!in_array($direction, ['x', 'y'])) {
            throw new \Exception('Expected one of "x" or "y" for $direction.');
        }
        if ($direction === 'x') {
            $lastColumn = $this->getBoardLastColumnAlpha();
            $row = $cell->getRow();
            $column = $this->moveX($cell->getColumn(), $distance);
            if ($column === '`') {
                return null;
            }
            if ($column > $lastColumn) {
                return null;
            }
        }
        if ($direction === 'y') {
            $row = ($cell->getRow() + $distance);
            if ($row < 1) {
                return null;
            }
            if ($row > sqrt(count($this->cells))) {
                // @todo what if the board isn't square?
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
}
