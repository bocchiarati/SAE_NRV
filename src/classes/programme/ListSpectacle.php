<?php

namespace iutnc\nrv\programme;

abstract class ListSpectacle implements \Iterator{
    protected array $spectacles = [];
    public function current() {
        return $this->tab[$this->position];    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->tab[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }
}