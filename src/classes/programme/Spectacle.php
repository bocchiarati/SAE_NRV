<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;

class Spectacle {
    protected ?int $id;
    protected ?string $titre;
    protected ?string $groupe;
    protected ?int $duree;
    protected ?int $styleID;
    protected ?string $nomStyle;
    protected ?string $description;
    protected ?string $cheminExtrait;
    protected ?string $image;

    public function __construct(?string $titre, ?string $groupe, ?int $duree, ?int $styleID, ?string $nomStyle, ?string $description, ?string $cheminExtrait, ?string $image)
    {
        $this->titre = $titre;
        $this->groupe = $groupe;
        $this->duree = $duree;
        $this->styleID = $styleID;
        $this->nomStyle = $nomStyle;
        $this->description = $description;
        $this->cheminExtrait = $cheminExtrait;
        $this->image = $image;
    }

    public function setID(mixed $id){
        $this->id = $id;
    }

    public function __get(string $attribut): mixed {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }
}