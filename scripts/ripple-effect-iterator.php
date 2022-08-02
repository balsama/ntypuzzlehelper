<?php
include_once __DIR__ . '/../vendor/autoload.php';

use Balsama\Nytpuzzlehelper\Exception\UnableToSolveException;
use Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

$finder = new Finder();
$finder->files()->in(__DIR__ . '/../puzzles/ripple-effect');
$finder->sortByName();

foreach ($finder as $file) {
    $puzzle = Yaml::parseFile($file->getRealPath());

    $board = new RippleEffectBoard($puzzle['board'], $puzzle['prefills']);
    $time = -microtime(true);
    try {
        $board->solve();
    } catch (UnableToSolveException $e) {
        echo "Unable to solve puzzle from " . $puzzle['date'] . " :(\n";
    }
    $time += microtime(true);

    $solution = $board->getPrettySolution();
    echo "\nSolved puzzle from " . $puzzle['date'] . ":\n(Duration: " . sprintf('%f', $time) . " seconds)\n $solution \n";
    file_put_contents(__DIR__ . '/../puzzles/solutions/ripple-effect--' . $puzzle['date'] . '.txt', $board->getPrettySolution());
}
