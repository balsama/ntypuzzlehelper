<?php

namespace Balsama\Nytpuzzlehelper;

class Coordinate
{
    public string $legacyId;
    public string $legacyColumn;
    public int $weight;

    public function __construct(
        public int $column,
        public int $row,
    ) {
        $this->legacyColumn = $this->getAlphaColumn($this->column);
        $this->legacyId = md5($row . $this->legacyColumn);
        $this->weight = $column + $row;
    }

    public function getNeighborByRelativeCoordinate(Coordinate $coordinate): Coordinate
    {
        $column = ($this->column + $coordinate->column);
        $row = ($this->row + $coordinate->row);
        return new \Balsama\Nytpuzzlehelper\Coordinate($column, $row);
    }

    public function getAlphaColumn(int $column): string
    {
        if ($column < 1) {
            throw new \Exception("I don't think we really know what will happen here, so let's ensure we address it.");
            return (string) $column;
        }
        return $this->num2alpha($column);
    }

    private function num2alpha($n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(96 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }

    private function moveX($start, int $distance): string
    {
        $ord = ord($start) + $distance;
        $character = chr($ord);
        return $character;
    }
}
