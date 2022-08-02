<?php

namespace Nytpuzzlehelper\Skyscrapers;

use Balsama\Nytpuzzlehelper\Skyscrapers\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{   
    /**
     * Get results with no constraints or overrides
     */
    public function testDefaultResults()
    {
        $view = new View();

        // Asserts that the block length is three without setting it
        $this->assertTrue(($view->getBlocks() == 3));

        // Asserts that there are six solutions by default given the default block
        // length of 3.
        $results = $view->getSolutions();
        $this->assertTrue(count($results) == 6);

        // Asserts that each solution is an array with three skyscrapers.
        foreach ($results as $result) {
            $this->assertTrue((count($result) == 3));
            foreach ($result as $skyscraper) {
                $this->assertTrue(is_numeric($skyscraper));
            }
        }
    }

    /**
     * Add single and multiple constraints of each type and confirm that the
     * results are correct.
     */
    public function testConstrainedResults()
    {
        $view = new View();
        $view->addConstraint(1, '==', 1);

        // Asserts that all solutions start with 1
        $results = $view->getSolutions();
        foreach ($results as $result) {
            $this->assertTrue(($result[0] === 1));
        }
        // Asserts that only two results exist that start with 1
        $this->assertTrue((count($results) == 2));

        // Asserts that adding a directly opposing constraint returns zero results
        $view->addConstraint(1, '!=', 1);
        $results = $view->getSolutions();
        $this->assertTrue((count($results) == 0));

        // Asserts that gt constraint works
        $view = new View();
        $view->addConstraint(1, ">", 1);
        $results = $view->getSolutions();
        foreach ($results as $result) {
            $this->assertTrue($result[0] > 1);
        }

        // Asserts that lt constraint can be chained
        $view->addConstraint(3, "<", 3);
        $results = $view->getSolutions();
        foreach ($results as $result) {
            $this->assertTrue(($result[0] > 1) && ($result[2] < 3));
        }
    }

    /**
     * Constrain results to those with a certain number of skyscrapers visible.
     */
    public function testDesiredVisibleResults()
    {
        $view = new View();
        $view->setDesiredVisible(3);
        $results = $view->getSolutions();
        $this->assertTrue(count($results) == 1);
    }

    /**
     * Combine desired visible and place constraints
     */
    public function testConstraintsAndVisibleResults()
    {
        $view = new View();

        // Only two results should meet the criteria
        $view->addConstraint(1, ">", 1);
        $view->setDesiredVisible(2);
        $results = $view->getSolutions();
        $this->assertTrue(count($results) == 2);

        // Adding a second constraint should limit results to one
        $view->addConstraint(3, '==', 1);
        $results = $view->getSolutions();
        $this->assertTrue(count($results) == 1);
    }

    public function testBlockLength()
    {
        $view = new View();
        $view->setBlocks(4);
        $results = $view->getSolutions();
        $this->assertTrue(count($results) == 24);
    }
}
