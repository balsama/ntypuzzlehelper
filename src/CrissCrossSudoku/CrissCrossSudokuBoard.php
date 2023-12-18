<?php

namespace Balsama\Nytpuzzlehelper\CrissCrossSudoku;

use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Exception\UnableToSolveException;
use Balsama\Nytpuzzlehelper\Group;
use Laminas\Text\Table\Decorator\Blank;
use MathieuViossat\Util\ArrayToTextTable;
use Nytpuzzlehelper\CrissCrossSudoku\CrissCrossSudokuTest;

/**
 * @rules
 *   Place the given words in the shaded spaces. Then finish the puzzle using the usual sudoku rules. Do not repeat a
 *   letter in any row, column, or 3x2 outlined area.
 * @startdate
 *   2023-September-10
 * @endate
 *   ??
 * @notes
 *   This puzzle is stupid. The first step is just a chore. Then the rest is tedious.
 *   You are required to solve the first step manually (that is, place the stupid words in the stupid shaded spaces).
 * @see CrissCrossSudokuTest for an example.
 */
class CrissCrossSudokuBoard extends \Balsama\Nytpuzzlehelper\Board
{
    private const ALLOWED = [1, 2, 3, 4, 5, 6];
    private const BOARD_DESCRIPTION = [
        [1, 1, 1, 2, 2, 2],
        [1, 1, 1, 2, 2, 2],
        [3, 3, 3, 4, 4, 4],
        [3, 3, 3, 4, 4, 4],
        [5, 5, 5, 6, 6, 6],
        [5, 5, 5, 6, 6, 6],
    ];
    private int $newlySolved;
    private array $map;
    public function __construct(array $alphaBoardPrefills)
    {
        $this->map = $this->getAlphaMap($alphaBoardPrefills);
        $boardPrefills = $this->convertAlphaPrefills($alphaBoardPrefills);
        parent::__construct(self::BOARD_DESCRIPTION, $boardPrefills);
    }

    public function solve()
    {
        $this->newlySolved = 0;
        $this->fillInCants();
        $this->solveSelfEvidentCells();

        $unsolvedCount = count($this->findAllUnsolvedCells());
        if ($unsolvedCount) {
            if ($this->newlySolved) {
                return $this->solve();
            }
            throw new UnableToSolveException(
                'Unable to solve Criss Cross Sudoku puzzle',
                0,
                null,
                null,
                $this->getCurrentState(),
            );
        }

        return $this->getAlphaPuzzleState();
    }

    public function fillInCants()
    {
        foreach ($this->cells as $cell) {
            if ($cell->valueIsMutable) {
                $this->fillCellCants($cell);
            }
        }
    }

    public function solveSelfEvidentCells()
    {
        foreach ($this->cells as $cell) {
            /* @var Cell $cell */
            if (!$cell->valueIsMutable) {
                continue;
            }
            if (count($cell->prohibitedValues) === 5) {
                $value = array_diff(self::ALLOWED, $cell->prohibitedValues);
                if (count($value) !== 1) {
                    throw new \Exception('There should only be one value left.');
                }
                $cell->setValue(reset($value), false);
                $this->newlySolved++;
            }
        }
    }

    public function fillCellCants(Cell $cell)
    {
        $group = $this->getCellGroup($cell->getGroup());
        $groupCants = $group->getSolvedCellsValues();

        $row = $this->getCellRow($cell->getRow());
        $rowGroup = new Group($row, 100);
        $rowCants = $rowGroup->getSolvedCellsValues();

        $column = $this->getCellColumn($cell->getColumn());
        $columnGroup = new Group($column, 200);
        $columnCants = $columnGroup->getSolvedCellsValues();

        $cants = array_unique(array_merge($groupCants, $rowCants, $columnCants));
        $cell->prohibitedValues = $cants;
    }

    public function convertAlphaPrefills(array $alphaPrefills): array
    {
        $row = 0;
        $convertedPrefills = $alphaPrefills;
        foreach ($convertedPrefills as $alphaRow) {
            $column = 0;
            foreach ($alphaRow as $alphaCell) {
                if (is_string($alphaCell)) {
                    $convertedPrefills[$row][$column] = $this->replaceAlphaWithNumeric($alphaCell);
                }
                $column++;
            }
            $row++;
        }
        return $convertedPrefills;
    }

    public function replaceAlphaWithNumeric(string $alpha): int
    {
        return $this->map[$alpha];
    }
    public function replaceNumericWithAlpha(int $n): string
    {
        $value = array_search($n, $this->map);
        return $value;
    }

    private function getAlphaMap(array $alphaPrefills): array
    {
        $map = [];
        $n = 1;
        foreach ($alphaPrefills as $row) {
            foreach ($row as $cell) {
                if (is_numeric($cell)) {
                    continue;
                }
                if (!is_string($cell)) {
                    throw new \Exception('Cell must be numeric or string');
                }
                if (array_key_exists($cell, $map)) {
                    continue;
                }
                $map[$cell] = $n;
                $n++;
            }
        }
        return $map;
    }

    public function getAlphaPuzzleState(): array
    {
        $numericState = $this->getCurrentState();
        $alphaState = $numericState;
        $row = 0;
        foreach ($numericState as $numericStateRow) {
            $column = 0;
            foreach ($numericStateRow as $numericStateCell) {
                if (is_numeric($numericStateCell)) {
                    $alphaState[$row][$column] = $this->replaceNumericWithAlpha($numericStateCell);
                }
                $column++;
            }
            $row++;
        }
        return $alphaState;
    }

    public function getPrettySolution()
    {
        $state = $this->getAlphaPuzzleState();
        $renderer = new ArrayToTextTable($state);
        $renderer->setDecorator(new Blank());
        return $renderer->getTable();
    }
}
