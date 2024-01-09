<?php

/**
 * @usage `php image-from-board -f <path_to_yaml> -o <output_filename>
 */

require __DIR__ . '/../vendor/autoload.php';

use Balsama\Nytpuzzlehelper\Board;
use Balsama\Nytpuzzlehelper\Utilities\PuzzleBoardStaticImageGenerator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;

if (getopt('f:')) {
    $boardDefinitionFile = getopt('f:')['f'];
}
else {
    throw new InvalidArgumentException('Provide an input filename with the -f option.');
}

if (getopt('o:')) {
    $outputFilename = getopt('o:')['o'];
}
else {
    $outputFilename = __DIR__ . '/../fixtures/board--' . $boardDefinitionFile . '.jpg';
}

$fs = new Filesystem();
if (!$fs->exists($boardDefinitionFile)) {
    throw new \PHPUnit\Event\InvalidArgumentException('Unable to find input file.');
}

$puzzle = Yaml::parseFile($boardDefinitionFile);
if (!array_key_exists('prefills', $puzzle)) {
    $puzzle['prefills'] = [];
}
$meta = ['puzzle_type' => $puzzle['puzzle_type'], 'date' => $puzzle['date']];
$board = new Board($puzzle['board'], $puzzle['prefills'], $puzzle['shaded'], $meta);
$puzzleBoardStaticImageGenerator = new PuzzleBoardStaticImageGenerator($board);

// Save a single image.
$puzzleBoardStaticImageGenerator->saveRaster($puzzle['puzzle_type'] . '--' . $puzzle['date'] . '.png');

// Save a 2x3 grid of images.
$puzzleBoardStaticImageGenerator->save2x3Grid($puzzle['puzzle_type'] . '--' . $puzzle['date'] . '--page.png');
