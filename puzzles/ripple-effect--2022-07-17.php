<?php
include_once __DIR__ . '/../vendor/autoload.php';

$date = '2022-07-17';

$board = [
    [1, 1, 2, 3, 3, 3, 3],
    [4, 4, 2, 5, 6, 7, 3],
    [2, 2, 2, 5, 6, 7, 3],
    [8, 8, 9, 5, 10, 11, 10],
    [12, 12, 9, 5, 10, 10, 10],
    [9, 9, 9, 13, 10, 14, 13],
    [15, 15, 16, 13, 13, 13, 13],
];
$boardPrefills = [
    [0, 0, 1, 5, 3, 0, 0],
    [0, 0, 3, 0, 2, 0, 0],
    [0, 4, 0, 0, 0, 2, 0],
    [0, 0, 0, 0, 0, 0, 0],
    [0, 0, 5, 0, 3, 0, 0],
    [0, 0, 2, 1, 5, 0, 0],
    [1, 0, 0, 3, 0, 0, 4],
];

$board = new \Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard($board, $boardPrefills);
$board->solve();
$prettySolution = $board->getPrettySolution();
print $prettySolution;
file_put_contents(__DIR__ . '/solutions/ripple-effect--' . $date . '.txt', $prettySolution);
