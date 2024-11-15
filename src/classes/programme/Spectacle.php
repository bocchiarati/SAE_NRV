<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;

class Spectacle {
    protected ?int $id;

    protected ?int $soireeID;
    protected ?string $titre;
    protected ?string $groupe;
    protected ?int $duree;
    protected ?int $styleID;
    protected ?string $nomStyle;
    protected ?string $description;
    protected ?string $cheminExtrait;
    protected ?string $image;
    protected bool $cancel;

    public function __construct(?string $titre, ?string $groupe, ?int $duree, ?int $styleID, ?string $nomStyle, ?string $description, ?string $cheminExtrait, ?string $image, bool $cancel,  ?int $soireeID = null)
    {
        $this->titre = $titre;
        $this->groupe = $groupe;
        $this->duree = $duree;
        $this->styleID = $styleID;
        $this->nomStyle = $nomStyle;
        $this->description = $description;
        $this->cheminExtrait = $cheminExtrait;
        $this->image = $image;
        $this->cancel = $cancel;
        if (isset($soireeID)) {
            $this->soireeID = $soireeID;
        }
    }

    public function setID(mixed $id){
        $this->id = $id;
    }

    public function __get(string $attribut): mixed {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }

    public function getSoireeID(): ?string {
        if(isset($this->soireeID))
            return $this->soireeID;
        else
            return null;
    }

    // getters
    public function getID(): ?int {
        return $this->id;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function getGroupe(): ?string {
        return $this->groupe;
    }

    public function getDuree(): ?int {
        return $this->duree;
    }

    public function getStyleID(): ?int {
        return $this->styleID;
    }

    public function getNomStyle(): ?string {
        return $this->nomStyle;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getCheminExtrait(): ?string {
        return $this->cheminExtrait;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    // convertir duree en heures et minutes
    public function getDurationHoursEtMin(): string {
        if ($this->duree === null) {
            return "Unknown duration";
        }

        $hours = intdiv($this->duree, 60);
        $minutes = $this->duree % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} h {$minutes}";
        } elseif ($hours > 0) {
            return "{$hours} h";
        } else {
            return "{$minutes} m";
        }
    }


}