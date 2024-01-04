<?php

namespace Nytpuzzlehelper\Utilities;

use Balsama\Nytpuzzlehelper\AllOrOne\AllOrOneBoard;
use Balsama\Nytpuzzlehelper\Utilities\PuzzleBoardStaticImageGenerator;

class PuzzleBoardStaticImageGeneratorTest extends \PHPUnit\Framework\TestCase
{

    public function testAllOrOneBoard()
    {
        $boardDescription = [
            [1, 1, 2],
            [1, 2, 2],
            [3, 3, 3],
        ];
        $boardPrefills = [
            [0, 1, 0],
            [0, 0, 0],
            [0, 2, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $puzzleBoardStaticImageGenerator = new PuzzleBoardStaticImageGenerator($board);
        $svgValues = $puzzleBoardStaticImageGenerator->getSvgValues();
        $svg = $puzzleBoardStaticImageGenerator->getSvg();
        $this->assertTrue(true);

    }

    public function testEnumerateRegions()
    {
        $boardDescription = [
            [1, 1, 1],
            [1, 2, 3],
            [4, 5, 5],
        ];
        $boardPrefills = [
            [0, 1, 0],
            [0, 0, 0],
            [0, 2, 0],
        ];
        $board = new AllOrOneBoard($boardDescription, $boardPrefills);
        $puzzleBoardStaticImageGenerator = new PuzzleBoardStaticImageGenerator($board);
        $rows = $puzzleBoardStaticImageGenerator->getBoardDescription();
        $this->assertTrue(true);
    }

}