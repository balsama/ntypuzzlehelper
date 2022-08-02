<?php
include_once __DIR__ . '/../vendor/autoload.php';

$date = '2022-07-24';

$board = [
    [1, 2, 2, 3, 4, 4, 5],
    [2, 2, 2, 3, 4, 4, 4],
    [2, 2, 6, 3, 3, 4, 4],
    [6, 6, 6, 7, 8, 8, 8],
    [9, 9, 10, 10, 8, 11, 11],
    [9, 9, 9, 10, 11, 11, 11],
    [12, 9, 9, 10, 11, 11, 13],
];
$boardPrefills = [
    [0, 0, 5, 0, 0, 0, 0],
    [0, 7, 0, 2, 0, 3, 0],
    [0, 0, 1, 0, 4, 0, 5],
    [0, 4, 0, 0, 0, 1, 0],
    [6, 0, 2, 0, 3, 0, 0],
    [0, 3, 0, 1, 0, 7, 0],
    [0, 0, 0, 0, 5, 0, 0],
];

$board = new \Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard($board, $boardPrefills);
$board->solve();
$prettySolution = $board->getPrettySolution();
print $prettySolution;
file_put_contents(__DIR__ . '/solutions/ripple-effect--' . $date . '.txt', $prettySolution);