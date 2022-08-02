<?php
include_once __DIR__ . '/../vendor/autoload.php';

$date = '2022-07-10';

$board = [
    [1, 2, 2, 3, 4, 5, 5],
    [6, 7, 7, 3, 4, 5, 5],
    [6, 7, 7, 3, 4, 5, 5],
    [8, 8, 8, 9, 9, 10, 11],
    [12, 12, 12, 9, 9, 9, 11],
    [12, 12, 12, 13, 13, 13, 14],
    [15, 16, 16, 13, 13, 14, 14],
];
$boardPrefills = [
    [1, 2, 0, 0, 0, 4, 5],
    [0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0],
    [0, 0, 1, 4, 5, 0, 0],
    [0, 3, 0, 0, 0, 3, 0],
    [0, 5, 0, 1, 0, 2, 0],
    [0, 0, 0, 0, 0, 0, 0],
];

$board = new \Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard($board, $boardPrefills);
$board->solve();
$prettySolution = $board->getPrettySolution();
print $prettySolution;
file_put_contents(__DIR__ . '/solutions/ripple-effect--' . $date . '.txt', $prettySolution);
