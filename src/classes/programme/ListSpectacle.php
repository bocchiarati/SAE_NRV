<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;

abstract class ListSpectacle implements \Iterator{
    protected array $spectacles = [];
<<<<<<< HEAD
    protected ?int $id;

    private int $position = 0;

    public function current() {
        return $this->spectacles[$this->position];    }
=======
    public function current(): Spectacle {
        return $this->tab[$this->position];    }
>>>>>>> d12c96e34bd6df750e776de01000e3860f908df8

    public function next(): void {
        ++$this->position;
    }

    public function key(): int {
        return $this->position;
    }

<<<<<<< HEAD
    public function valid() {
        return isset($this->spectacles[$this->position]);
=======
    public function valid(): bool {
        return isset($this->tab[$this->position]);
>>>>>>> d12c96e34bd6df750e776de01000e3860f908df8
    }

    public function rewind(): void {
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