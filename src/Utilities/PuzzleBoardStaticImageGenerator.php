<?php

namespace Balsama\Nytpuzzlehelper\Utilities;

use Balsama\Nytpuzzlehelper\Board;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

class PuzzleBoardStaticImageGenerator
{
    private Board $board;
    private array $svgValues;
    private array $boardDescription;
    private float $columnWidth;
    private float $rowHeight;
    private const IMAGE_HEIGHT = 800;
    private const IMAGE_WIDTH = 800;

    public function __construct(Board $board)
    {
        $this->board = $board;
        $this->boardDescription = $board->getBoardDescription();
        $this->columnWidth = self::IMAGE_WIDTH / (count(reset($this->boardDescription)));
        $this->rowHeight = self::IMAGE_HEIGHT / count($this->boardDescription);
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

    public function saveRaster($filename = 'unnamed-puzzle-board.png', $filepath = __DIR__ . '/../../fixtures/'): void
    {
        $svg = $this->getSvg();
        $gdImage = $svg->toRasterImage(800, 800);
        imagepng($gdImage, $filepath . $filename);
    }

    public function getSvg(): SVG
    {
        $image = new SVG(800, 800);
        $doc = $image->getDocument();
        $box = new SVGRect(0, 0, 800, 800);
        $box->setStyle('fill', 'white');
        $box->setStyle('stroke', 'black');
        $box->setStyle('stroke-width', 20);
        $doc->addChild($box);
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
            $line->setStyle('stroke-dasharray', '10,10');// This doesn'yt appear to be supported.
            $doc->addChild($line);
        }

        return $image;
    }

    public function calculateSvgLinePaths(array $rows, $width = 800, $height = 800, $stroke_width = 6, $padding = 6)
    {
        $thickLines = [];
        $thinLines = [];
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
            }
        }

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

        return ['thick_lines' => $thickLines, 'thin_lines' => $thinLines];
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


    public function enumerateRegions($rows): array
    {
        $regions = [];
        foreach ($rows as $row => $columnRegions) {
            $regions = array_unique(array_merge($regions, $columnRegions));
        }
        return $regions;
    }

    protected function getLength($number_of_regions, $length, $stroke_width, $padding)
    {
        if ($number_of_regions === 0) {
              return 0;
        }

        // Half of the stroke width is drawn outside the dimensions.
        $total_stroke = $number_of_regions * $stroke_width;
        // Padding does not precede the first region.
        $total_padding = ($number_of_regions - 1) * $padding;
        // Divide the remaining length by the number of regions.
        return ($length - $total_padding - $total_stroke) / $number_of_regions;
    }

    protected function getOffset($delta, $length, $stroke_width, $padding)
    {
        // Half of the stroke width is drawn outside the dimensions.
        $stroke_width /= 2;
        // For every region in front of this add two strokes, as well as one
        // directly in front.
        $num_of_strokes = 2 * $delta + 1;
        return ($num_of_strokes * $stroke_width) + ($delta * ($length));
    }
}
