<?php

namespace Balsama\Nytpuzzlehelper;

class Cell
{

    public function __construct(
        private int $row,
        private int $column,
        private int $group,
        public int|null $value = null,
    ) {}

    public function getRow(): int
    {
        return $this->row;
    }
    public function getColumn(): int
    {
        return $this->column;
    }
    public function getGroup(): int
    {
        return $this->group;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

}