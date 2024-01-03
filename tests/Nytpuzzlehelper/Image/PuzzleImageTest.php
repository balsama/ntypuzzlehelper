<?php

namespace Nytpuzzlehelper\Image;

use Balsama\Nytpuzzlehelper\Image\PuzzleImage;

class PuzzleImageTest extends \PHPUnit\Framework\TestCase
{
    private PuzzleImage $puzzleImage;

    public function testFindRegions()
    {
        $this->puzzleImage = new PuzzleImage(__DIR__ . '/../../images/2023-08-07.jpeg');
    }

}