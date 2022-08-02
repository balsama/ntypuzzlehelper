<?php
/**
 * Created by PhpStorm.
 * User: adam.balsam
 * Date: 9/28/16
 * Time: 12:12 PM
 */

namespace Balsama\Nytpuzzlehelper\Skyscrapers;
use NajiDev\Permutation;

class View {

    /**
     * @var array solutions
     *   The possible order of skyscrapers given defined constraints, number of
     *   blocks and desired visible skyscrapers.
     */
    private $solutions;

    /**
     * @param array $solutions
     */
    private function setSolutions($solutions) {
        $this->solutions = $solutions;
    }

    /**
     * @return array
     */
    public function getSolutions() {
        return $this->solutions;
    }

    /**
     * View constructor.
     */
    public function __construct() {
        $permutations = $this->getAllPermutations();
        $this->setSolutions($permutations);
    }

    /**
     * @var null or int
     *   The number that should be visible from a view, if known.
     */
    private $desiredVisible = null;
    public function setDesiredVisible($desiredVisible) {
        $this->desiredVisible = $desiredVisible;
        $this->applyVisibleFilter();
    }

    /**
     * @return null or int
     */
    private function getDesiredVisible() {
        return $this->desiredVisible;
    }

    /**
     * Filters $solutions to only include results in which the $desiredVisible
     * number of skyscrapers are visible.
     */
    private function applyVisibleFilter() {
        $desiredVisible = $this->getDesiredVisible();
        $currentSolutions = $this->getSolutions();

        $validPermutations = [];
        foreach ($currentSolutions as $currentSolution) {
            if ($this->getVisible($currentSolution) == $desiredVisible) {
                $validPermutations[] = $currentSolution;
            }
        }
        $this->setSolutions($validPermutations);
    }

    /**
     * Applies all currently defined $constraints.
     */
    private function applyConstraints() {
        $currentSolutions = $this->getSolutions();
        $constraints = $this->getConstraints();
        if (empty($constraints)) {
            return;
        }
        foreach ($constraints as $constraint) {
            $this->applyConstraint($currentSolutions, $constraint);
        }
    }

    /**
     * Removes permutations from $solutions that don't meet the passed
     * $constraint.
     *
     * @param $currentSolutions
     *   The subset of all permutations that are currently valid.
     * @param $constraint
     *   A single constraint to be applied.
     */
    private function applyConstraint($currentSolutions, $constraint) {
        $validConstrainedPermutations = [];
        foreach ($currentSolutions as $currentSolution) {
            switch($constraint['operator']) {
                case ">":
                    if ($currentSolution[$constraint['position']] > $constraint['value']) {
                        $validConstrainedPermutations[] = $currentSolution;
                    }
                    break;
                case "<":
                    if ($currentSolution[$constraint['position']] < $constraint['value']) {
                        $validConstrainedPermutations[] = $currentSolution;
                    }
                    break;
                case "==":
                    if ($currentSolution[$constraint['position']] == $constraint['value']) {
                        $validConstrainedPermutations[] = $currentSolution;
                    }
                    break;
                case "!=":
                    if ($currentSolution[$constraint['position']] != $constraint['value']) {
                        $validConstrainedPermutations[] = $currentSolution;
                    }
            }
        }
        $this->setSolutions($validConstrainedPermutations);
    }

    /**
     * @var array
     *   Stores any constraints that have been set on possible building heights.
     */
    private $constraints = [];

    /**
     * Sets a constraint.
     *
     * @param int $position
     *   The position of the building (1 is the closest to you)
     * @param string $operator
     *   The operator for the function. Allowed values:
     *   - < (less then)
     *   - > (greater then)
     *   - == (equals)
     * @param int $value
     *   The value for the operator to act on.
     * @throws \Exception
     */
    public function addConstraint($position, $operator, $value) {
        if ($position > $this->getBlocks()) {
            throw new \Exception('Position must be less than total blocks.');
        }
        $position--;
        if (!in_array($operator, ['>', '<', '==', '!='])) {
            throw new \Exception('Operator must be one of \'>\', \'<\', \'==\', \'!=\'');
        }
        if ((!is_int($value)) || ($value < 1)) {
            throw new \Exception('Value must be a positive integer');
        }
        $this->constraints = $this->getConstraints();
        $this->constraints[] = [
            'position' => $position,
            'operator' => $operator,
            'value' => $value,
        ];
        $this->applyConstraints();
    }

    /**
     * Gets current defined constraints.
     */
    private function getConstraints() {
        return $this->constraints;
    }

    /**
     * Gets all the possible permutations of skyscrapers given the block length.
     */
    private function getAllPermutations() {
        $blocks = $this->getBlocks();

        // Convert the number of blocks into the height of buildings within it
        // (given that only one building of each height can exist in a row or
        // column).
        $buildings = $this->integerToArray($blocks);
        $permutations = [];

        $iterations = new Permutation\PermutationIterator($buildings);
        foreach ($iterations as $iteration) {
            $permutations[] = $iteration;
        }

        return $permutations;
    }

    /**
     * The number of blocks to the furthest skyscraper from the view.
     *
     * @var int
     */
    private $blocks = 3;

    /**
     * Sets the number of blocks.
     *
     * @param int $blocks
     *   The number of blocks to the furthest skyscraper from the view.
     * @throws \Exception
     */
    public function setBlocks($blocks) {
        if ($blocks > 5) {
            throw new \Exception("We would need more power for that.");
        }
        $this->blocks = $blocks;
        $permutations = $this->getAllPermutations();
        $this->setSolutions($permutations);
    }

    /**
     * Returns the number of blocks from the view.
     *
     * @return int
     *   The number of blocks to the furthest skyscraper from the view.
     */
    public function getBlocks() {
        return $this->blocks;
    }

    /**
     * Given an array of building heights, gets the number that are visible.
     */
    public function getVisible($orderedScrapers) {
        $prevScraper = 0;
        $visibleCount = 0;
        foreach($orderedScrapers as $scraper) {
            if ($scraper > $prevScraper) {
                $visibleCount++;
                $prevScraper = $scraper;
            }
        }
        return $visibleCount;
    }

    /**
     * Converts an integer into an array of integers that come before it.
     *
     * @param $int
     *   The integer to array-ize
     * @return array
     */
    private function integerToArray($int) {
        $depth = [];
        $i = $int;
        while ($i > 0) {
            $depth[] = $i;
            $i--;
        }
        return $depth;
    }
}
