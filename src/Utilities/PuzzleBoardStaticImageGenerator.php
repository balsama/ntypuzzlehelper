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

    public function __construct(Board $board)
    {
        $this->board = $board;
        $array = $board->getBoardDescription();
        $this->boardDescription = $board->getBoardDescription();
        $this->svgValues = $this->calculateSvgLinePaths($array);
    }

    public function getBoardDescription(): array
    {
        return $this->boardDescription;
    }
    public function getSvgValues()
    {
        return $this->svgValues;
    }

    public function getSvg()
    {
        $image = new SVG(800, 800);
        $doc = $image->getDocument();
        $box = new SVGRect(0, 0, 800, 800);
        $box->setStyle('fill', 'white');
        $box->setStyle('stroke', 'black');
        $box->setStyle('stroke-width', 20);
        $doc->addChild($box);
        foreach ($this->svgValues as $line) {
            $line = new SVGLine($line['x1'], $line['y1'], $line['x2'], $line['y2']);
            $line->setStyle('stroke', 'black');
            $line->setStyle('stroke-width', 10);
            $doc->addChild($line);
        }

        $rasterImage = $image->toRasterImage(800, 800);

        $imgJpg = imagejpeg($rasterImage, __DIR__ . '/../../fixtures/filename.jpg');
        return;
    }

    public function save($svg, string $filename = 'filename.png', $path = __DIR__ . '/../../fixtures/')
    {
        file_put_contents($path . $filename, $svg);
    }

    public function calculateSvgLinePaths(array $rows, $width = 800, $height = 800, $stroke_width = 6, $padding = 6)
    {
        $row_height = $this->getLength(count($rows), $height, $stroke_width, $padding);
        $column_width = $row_height;
        $lines = [];
        foreach ($rows as $row => $cols) {
            $vertical_offset = $row * $row_height;
            $columnCount = count($cols);
            for ($index = 0; $index < $columnCount; $index++) {
                $needsRightLine = $this->colNeedsRightLine($cols, $index);
                if ($needsRightLine) {
                    $lines[] = [
                        'x1' => $column_width * ($index + 1),
                        'y1' => $vertical_offset,
                        'x2' => $column_width * ($index + 1),
                        'y2' => $vertical_offset + $row_height,
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
            $horizontal_offset = $column_width * ($colNum);
            for ($index = 0; $index < count($columns); $index++) {
                $vertical_offset = ($row_height) * ($index + 1);
                $needsBottomLine = $this->rowNeedsBottomLine($column, $index);
                if ($needsBottomLine) {
                    $lines[] = [
                        'x1' => $horizontal_offset - 5,
                        'y1' => $vertical_offset,
                        'x2' => $horizontal_offset + ($column_width * ($index + 1)) + 5,
                        'y2' => $vertical_offset,
                    ];
                }
            }
        }

        return $lines;
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
            $foo = 21;
            $regions = array_unique(array_merge($regions, $columnRegions));
        }
        return $regions;
    }

    protected function calculateSvgValues(array $rows, $width = 800, $height = 800, $stroke_width = 6, $padding = 6)
    {
        $region_rects = [];
        $row_height = $this->getLength(count($rows), $height, $stroke_width, $padding);
        foreach ($rows as $row => $cols) {
              $column_width = $this->getLength(count($cols), $width, $stroke_width, $padding);
              $vertical_offset = $this->getOffset($row, $row_height, $stroke_width, $padding);
            foreach ($cols as $col => $region) {
                  $horizontal_offset = $this->getOffset($col, $column_width, $stroke_width, $padding);
                  // Check if this region is new, or already exists in the rectangle.
                if (!isset($region_rects[$region])) {
                      $region_rects[$region] = [
                            'x' => $horizontal_offset,
                            'y' => $vertical_offset,
                            'width' => $column_width,
                            'height' => $row_height,
                          ];
                } else {
                        // In order to include the area of the previous region and any padding
                        // or border, subtract the calculated offset from the original offset.
                        //$region_rects[$region]['width'] = $column_width + ($horizontal_offset - $region_rects[$region]['x']);
                        $region_rects[$region]['height'] = $row_height + ($vertical_offset - $region_rects[$region]['y']);
                }
            }
        }
        return $region_rects;
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
