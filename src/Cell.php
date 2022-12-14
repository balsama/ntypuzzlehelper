<?php

namespace Balsama\Nytpuzzlehelper;

class Cell
{
    public string $cellId;

    public function __construct(
        private int $row,
        private string $column,
        private int $group,
        private int|null $value = null,
        public bool $valueIsMutable = true,
        public array $previousAttempts = [],
        public array $possibleValues = [],
        public array $prohibitedValues = [],
    ) {
        $this->cellId = md5($this->row . $this->column);
    }

    public function getRow(): int
    {
        return $this->row;
    }
    public function getColumn(): string
    {
        return $this->column;
    }
    public function getGroup(): int
    {
        return $this->group;
    }

    public function setValue(?int $value, $mutable = true): static
    {
        if ($this->valueIsMutable) {
            $this->value = $value;
            if ($mutable === false) {
                $this->valueIsMutable = false;
            }
            return $this;
        } else {
            throw new \Exception("Value of cell $this->row $this->column is immutable.");
        }
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getCurrentKnownAllowedValues(): array
    {
        if (($this->valueIsMutable === false) && (isset($this->value))) {
            return [$this->value];
        }
        return array_diff($this->possibleValues, $this->prohibitedValues);
    }
}
