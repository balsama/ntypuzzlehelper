<?php

namespace Balsama\Nytpuzzlehelper\Image;

use Imagick;

class PuzzleImage
{
    private Imagick $originalImage;

    public function __construct($image_path)
    {
        $this->originalImage = new Imagick($image_path);
        $this->base6x6 = new Imagick(__DIR__ . '/../../tests/images/6x6-base.png');
        $this->findRegions();
    }

    private function findRegions()
    {
        $foo = $this->originalImage->getImageGeometry();
        $bar = $this->originalImage->getImage();
        $baz = $this->originalImage->transverseImage();
        $diff = $this->originalImage->compareImageChannels($this->base6x6, imagick::CHANNEL_ALL, imagick::METRIC_FUZZERROR);

        /* @var Imagick $diffImage */
        $diffImage = $diff[0];
        $diffImage->writeImage(__DIR__ . '/../../tests/images/METRIC_FUZZERROR.png');
        $foo = 21;
    }

    public function convertBlackWhite(Imagick $image)
    {
        $image->thresholdImage(50, Imagick::CHANNEL_BLACK);
    }

}