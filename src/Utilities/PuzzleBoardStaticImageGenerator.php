<?php

namespace Balsama\Nytpuzzlehelper\Utilities;

use Balsama\Nytpuzzlehelper\Board;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Texts\SVGText;
use SVG\SVG;

class PuzzleBoardStaticImageGenerator
{
    private array $svgValues;
    private Board $board;
    private array $boardDescription;
    private array $boardPrefills;
    private array $boardShaded;
    private float $columnWidth;
    private float $rowHeight;

    private const IMAGE_HEIGHT = 800;
    private const IMAGE_WIDTH = 800;
    private float $fontSize;

    public function __construct(Board $board)
    {
        $this->board = $board;
        $this->boardDescription = $board->getBoardDescription();
        $this->boardPrefills = $board->getBoardPrefills();
        $this->boardShaded = $board->getBoardShaded();
        $this->columnWidth = self::IMAGE_WIDTH / (count(reset($this->boardDescription)));
        $this->rowHeight = self::IMAGE_HEIGHT / count($this->boardDescription);
        $this->fontSize = $this->rowHeight * 0.4;
        $this->svgValues = $this->calculateSvgLinePaths($this->boardDescription);
    }

    public function getBoardDescription(): array
    {
        return $this->boardDescription;
    }
    public function getSvgValues()
    {
        return $this->svgValues;
    }

    public function saveRaster($filename = 'unnamed-puzzle-board.png', $filepath = __DIR__ . '/../../images/generated/'): void
    {
        $svg = $this->getSvg();
        @$gdImage = $svg->toRasterImage(800, 800);
        imagepng($gdImage, $filepath . $filename);
    }

    public function save2x3Grid($filename = 'unnamed-puzzle-board--page.png', $filepath = __DIR__ . '/../../images/generated/'): void
    {
        $canvas = imagecreatetruecolor(1800, 2740);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, 1800, 2740, $white);

        $headerText = $this->board->meta['puzzle_type'];
        $subHeaderText = $this->board->meta['date'];
        $header = new SVG(1800, 100);
        $doc = $header->getDocument();

        $title = new SVGText($headerText, 0, 40,);
        $title->setStyle('stroke', 'black');
        $title->setStyle('stroke-width', 1);
        $title->setStyle('fill', 'black');
        $title->setStyle('font-size', '32px');
        $doc->addChild($title);

        $subhead = new SVGText($subHeaderText, 0, 76,);
        $subhead->setStyle('stroke', 'black');
        $subhead->setStyle('stroke-width', 0);
        $subhead->setStyle('fill', 'black');
        $subhead->setStyle('font-size', '24px');
        $doc->addChild($subhead);

        @$headerGdImage = $header->toRasterImage(1800, 100);
        imagecopy($canvas, $headerGdImage, 0, 0, 0, 0, 1800, 100);

        $puzzleSvg = $this->getSvg();
        @$puzzleGdImage = $puzzleSvg->toRasterImage(800, 800);

        $dst_y = 100;
        $incr = 920;
        for ($i = 1; $i < 4; $i++) {
            imagecopy($canvas, $puzzleGdImage, 0, $dst_y, 0, 0, 800, 800);
            imagecopy($canvas, $puzzleGdImage, 1000, $dst_y, 0, 0, 800, 800);
            $dst_y = $dst_y + $incr;
        }
        imagepng($canvas, $filepath . $filename);
    }


    public function getSvg(): SVG
    {
        SVG::addFont(__DIR__ . '/../../fonts/font.ttf');
        $image = new SVG(800, 800);
        $doc = $image->getDocument();
        $box = new SVGRect(0, 0, 800, 800);
        $box->setStyle('fill', 'white');
        $box->setStyle('stroke', 'black');
        $box->setStyle('stroke-width', 20);
        $doc->addChild($box);
        foreach ($this->svgValues['shaded'] as $shaded) {
            $rect = new SVGRect($shaded['x'], $shaded['y'], $shaded['width'], $shaded['height']);
            $rect->setStyle('fill', 'gray');
            $doc->addChild($rect);
        }
        foreach ($this->svgValues['thick_lines'] as $line) {
            $line = new SVGLine($line['x1'], $line['y1'], $line['x2'], $line['y2']);
            $line->setStyle('stroke', 'black');
            $line->setStyle('stroke-width', 10);
            $line->setStyle('stroke-linecap', 'square');//This doesn't appear to be supported.
            $doc->addChild($line);
        }
        foreach ($this->svgValues['thin_lines'] as $line) {
            $line = new SVGLine($line['x1'], $line['y1'], $line['x2'], $line['y2']);
            $line->setStyle('stroke', 'black');
            $line->setStyle('stroke-width', 1);
            $line->setStyle('stroke-dasharray', '10,10');// This doesn't appear to be supported.
            $doc->addChild($line);
        }
        foreach ($this->svgValues['prefills'] as $prefill) {
            $char = new SVGText($prefill['value'], $prefill['x'], $prefill['y']);
            $char->setStyle('stroke', 'black');
            $char->setStyle('stroke-width', 0);
            $char->setStyle('fill', 'black');
            $char->setStyle('font-size', $this->fontSize . 'px');
            $doc->addChild($char);
        }
        $box = new SVGRect(0, 0, 800, 800);
        $box->setStyle('fill', 'transparent');
        $box->setStyle('stroke', 'black');
        $box->setStyle('stroke-width', 20);
        $doc->addChild($box);

        return $image;
    }

    public function calculateSvgLinePaths(array $rows, $width = 800, $height = 800, $stroke_width = 6, $padding = 6)
    {
        $thickLines = [];
        $thinLines = [];
        $shaded = [];
        $prefills = [];
        foreach ($rows as $row => $cols) {
            $vertical_offset = $row * $this->rowHeight;
            $columnCount = count($cols);
            for ($index = 0; $index < $columnCount; $index++) {
                $needsRightLine = $this->colNeedsRightLine($cols, $index);
                if ($needsRightLine) {
                    $thickLines[] = [
                        'x1' => $this->columnWidth * ($index + 1),
                        'y1' => $vertical_offset,
                        'x2' => $this->columnWidth * ($index + 1),
                        'y2' => $vertical_offset + $this->rowHeight,
                    ];
                } else {
                    $thinLines[] = [
                        'x1' => $this->columnWidth * ($index + 1),
                        'y1' => $vertical_offset,
                        'x2' => $this->columnWidth * ($index + 1),
                        'y2' => $vertical_offset + $this->rowHeight,
                    ];
                }
                if ($this->cellHasPrefill($row, $index)) {
                    $prefill = $this->cellHasPrefill($row, $index);
                    $prefills[] = [
                        'value' => $prefill,
                        'x' => ($this->columnWidth * ($index)) + ($this->columnWidth * 0.3),
                        'y' => ($vertical_offset + $this->rowHeight) - ($this->rowHeight * 0.3),
                    ];
                }
                if ($this->cellIsShaded($row, $index)) {
                    $shaded[] = [
                        'x' => $this->columnWidth * ($index),
                        'y' => $vertical_offset,
                        'height' => $this->rowHeight,
                        'width' => $this->columnWidth,
                    ];
                }
            }
        }

        $columns = [];
        foreach ($rows as $cols) {
            for ($index = 0; $index < count($rows); $index++) {
                $columns[$index][] = $cols[$index];
            }
        }

        foreach ($columns as $colNum => $column) {
            $horizontal_offset = $this->getColumnX1($colNum + 1);
            for ($index = 0; $index < count($columns); $index++) {
                $vertical_offset = $this->getRowY1($index + 1);
                $needsBottomLine = $this->rowNeedsBottomLine($column, $index);
                if ($needsBottomLine) {
                    $thickLines[] = [
                        'x1' => $horizontal_offset - 5,
                        'y1' => $vertical_offset,
                        'x2' => $horizontal_offset + $this->columnWidth + 4,
                        'y2' => $vertical_offset,
                    ];
                } else {
                    $thinLines[] = [
                        'x1' => $horizontal_offset - 5,
                        'y1' => $vertical_offset,
                        'x2' => $horizontal_offset + $this->columnWidth + 4,
                        'y2' => $vertical_offset,
                    ];
                }
            }
        }

        return ['thick_lines' => $thickLines, 'thin_lines' => $thinLines, 'shaded' => $shaded, 'prefills' => $prefills];
    }

    public function cellIsShaded($row, $column): bool
    {
        if (!$this->boardShaded) {
            return false;
        }
        return $this->boardShaded[$row][$column];
    }

    public function cellHasPrefill($row, $column): null|int|string
    {
        if (!$this->boardPrefills) {
            return null;
        }
        $prefill = $this->boardPrefills[$row][$column];
        return $prefill;
    }

    public function getColumnX1($columnNumber)
    {
        if ($columnNumber < 1) {
            throw new \Exception('$columnNumber should be indexed starting at 1.');
        }
        return ($this->columnWidth * ($columnNumber - 1));
    }

    public function getRowY1($rowNumber)
    {
        if ($rowNumber < 1) {
            throw new \Exception('$rowNumber should be indexed starting at 1.');
        }
        return ($this->rowHeight * $rowNumber);
    }

    public function rowNeedsBottomLine(array $rows, int $index): bool
    {
        if (!array_key_exists($index + 1, $rows)) {
            return false;
        }
        $thisRowRegion = $rows[$index];
        $nextRowRegion = $rows[$index + 1];
        if ($thisRowRegion !== $nextRowRegion) {
            return true;
        }
        return false;
    }

    public function colNeedsRightLine(array $columns, int $index): bool
    {
        if (!array_key_exists($index + 1, $columns)) {
            return false;
        }
        $thisColumnRegion = $columns[$index];
        $nextColumnRegion = $columns[$index + 1];
        if ($nextColumnRegion !== $thisColumnRegion) {
            return true;
        }
        return false;
    }
}
