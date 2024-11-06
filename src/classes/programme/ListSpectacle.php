<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;

abstract class ListSpectacle implements \Iterator{
    protected array $spectacles = [];
    protected ?int $id;

    private int $position = 0;

    public function current() {
        return $this->spectacles[$this->position];    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->spectacles[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }

    public function setID(int $idListSpectacle): void{
        $this->id = $idListSpectacle;
    }

    public function __get(string $attribut): mixed {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }
}