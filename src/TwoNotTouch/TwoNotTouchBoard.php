<?php

namespace Balsama\Nytpuzzlehelper\TwoNotTouch;

use Balsama\Nytpuzzlehelper\Cell;
use Balsama\Nytpuzzlehelper\Group;

class TwoNotTouchBoard extends \Balsama\Nytpuzzlehelper\Board
{

    public function __construct(array $boardDescription)
    {
        $width = count(reset($boardDescription));
        $height = count($boardDescription);
        parent::__construct($boardDescription, $this->getPrefills($width, $height));
    }

    private function findGroupNeighbors(int $groupId): array
    {
        // ..
    }

    private function getImpact(Group $group, Cell $cell): int{
        // ...
    }

    private function getPrefills(int $width, int $height): array
    {
        $row = [];
        for ($i = 0; $i < $width; $i++) {
            $row[] = 0;
        }
        $board = [];
        for ($i = 1; $i < $height; $i++) {
            $board[] = $row;
        }
        return $board;
    }

}