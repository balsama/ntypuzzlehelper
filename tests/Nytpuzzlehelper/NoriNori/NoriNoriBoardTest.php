<?php

namespace Nytpuzzlehelper\NoriNori;

use Balsama\Nytpuzzlehelper\NoriNori\NoriNoriBoard;
use Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class NoriNoriBoardTest extends TestCase
{

    private array $boardDescriptionSmall = [
        [ 1, 2, 2, 3, 3, 3 ],
        [ 1, 1, 2, 3, 3, 3 ],
        [ 1, 4, 4, 4, 4, 5 ],
        [ 6, 4, 4, 4, 5, 5 ],
        [ 6, 4, 4, 4, 4, 7 ],
        [ 6, 6, 8, 8, 7, 7 ]
    ];

    private array $boardPrefillsSmall = [
        [ 0, 0, 0, 0, 0, 0 ],
        [ 0, 0, 0, 0, 0, 0 ],
        [ 0, 0, 0, 0, 0, 0 ],
        [ 0, 0, 0, 0, 0, 0 ],
        [ 0, 0, 0, 0, 0, 0 ],
        [ 0, 0, 0, 0, 0, 0 ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBoard()
    {
        $board = new NoriNoriBoard($this->boardDescriptionSmall, $this->boardPrefillsSmall);
        $board->solve();
    }

}