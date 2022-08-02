<?php
include_once __DIR__ . '/../vendor/autoload.php';

$board = [
    [1, 1, 2, 3, 4, 5, 6],
    [7, 7, 8, 3, 4, 5, 5],
    [7, 9, 8, 3, 4, 10, 11],
    [9, 9, 12, 3, 13, 10, 11],
    [9, 14, 12, 3, 13, 11, 11],
    [15, 15, 12, 3, 13, 11, 11],
    [15, 15, 15, 3, 16, 16, 16],
];
$boardPrefills = [
    [0, 0, 0, 6, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 4, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0],
    [1, 0, 0, 0, 0, 0, 5],
    [0, 2, 0, 0, 0, 2, 0],
];

$board = new \Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard($board, $boardPrefills);
$board->solve();
$prettySolution = $board->getPrettySolution();
print $prettySolution;
