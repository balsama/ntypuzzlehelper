<?php
/**
 * @usage
 *   Solve all puzzles in puzzles/ripple-effect:
 *     $ php ./scripts/ripple-effect-iterator.php
 *   Solve the puzzle from 14 August only by passing the `-d` argument with a date:
 *     $ php ./scripts/ripple-effect-iterator.php -d 2022-08-14
 */

/* @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../vendor/autoload.php';

use Balsama\Nytpuzzlehelper\Exception\UnableToSolveException;
use Balsama\Nytpuzzlehelper\RippleEffect\RippleEffectBoard;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

$puzzleDirectory = 'ripple-effect';
if (getopt('p:')) {
    $puzzleDirectory = getopt('p:');
}

$finder = new Finder();
$finder->files()->in(__DIR__ . '/../puzzles/' . $puzzleDirectory);
$finder->sortByName();

foreach ($finder as $file) {
    $puzzle = Yaml::parseFile($file->getRealPath());

    if (getopt('d:')) {
        if (strtotime(getopt('d:')['d'])) {
            if (strtotime($puzzle['date']) !== strtotime(getopt('d:')['d'])) {
                continue;
            }
        }
    }

    $board = new RippleEffectBoard($puzzle['board'], $puzzle['prefills']);
    $time = -microtime(true);

    try {
        $board->solve();
    } catch (UnableToSolveException $e) {
        $time += microtime(true);
        echo "Unable to solve puzzle from " . $puzzle['date'] . " :(\n";
        echo "(Duration: " . sprintf('%f', $time) . " seconds\n";
        continue;
    }
    $time += microtime(true);

    $solution = $board->getPrettySolution();
    echo "\nSolved puzzle from " . $puzzle['date'] . ":\n(Duration: " . sprintf('%f', $time) . " seconds)\n $solution \n";
    file_put_contents(__DIR__ . '/../puzzles/solutions/ripple-effect--' . $puzzle['date'] . '.txt', $board->getPrettySolution());
}
