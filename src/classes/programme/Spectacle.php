<?php

namespace iutnc\nrv\programme;

class Spectacle {
    public int $id;
    protected ?string $titre;
    protected ?string $groupe;
    protected int $duree;
    protected int $styleID;
    protected ?string $nomStyle;
    protected ?string $description;
    protected ?string $cheminExtrait;

    public function __construct() {

    }

    public function setID(mixed $id){
        $this->id = $id;
    }
}