<?php

namespace iutnc\nrv\programme;

abstract class ListSpectacle implements \Iterator{
    protected array $spectacles = [];
    public function current(): Spectacle {
        return $this->tab[$this->position];    }

    public function next(): void {
        ++$this->position;
    }

    public function key(): int {
        return $this->position;
    }

    public function valid(): bool {
        return isset($this->tab[$this->position]);
    }

    public function rewind(): void {
        $this->position = 0;
    }
}