<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;

class ListSpectacle implements \Iterator{
    protected array $spectacles = [];
    protected ?int $id;
    private int $position = 0;

    public function current(): Spectacle {
        return $this->spectacles[$this->position];
    }

    public function next(): void {
        ++$this->position;
    }

    public function key(): int {
        return $this->position;
    }

    public function valid(): bool {
        return isset($this->spectacles[$this->position]);
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function setID(int $idListSpectacle): void{
        $this->id = $idListSpectacle;
    }

    public function setSpectacles(array $spectacles): void
    {
        $this->spectacles = $spectacles;
    }

    public function __get(string $attribut): mixed {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }

    public function getSpectacles(): array {
        return $this->spectacles;
    }
    
    public function addSpectacle(Spectacle $s): void
    {
        $this->spectacles[] = $s;
    }

    public function delSpectacle(Spectacle $s): void{
        foreach ($this->spectacles as $key => $spec){
            if ($spec->id == $s->id){
                unset($this->spectacles[$key]);
                return;
            }
        }
    }

    public function contientSpectacle(Spectacle $s): bool{
        foreach ($this->spectacles as $spec){
            if ($spec->id == $s->id){
                return true;
            }
        }
        return false;
    }
}