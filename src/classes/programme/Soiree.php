<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\exception\InvalidPropertyNameException;
use iutnc\nrv\repository\NrvRepository;

class Soiree extends ListSpectacle {

    protected ?int $id;
    protected ?string $date;
    protected ?string $nom;
    protected ?string $thematique;
    protected int $tarif;
    protected ?int $lieuID;
    protected ?string $nomLieu;
    protected ?string $adresseLieu;

    public function __construct($date, $lieuID, $nomLieu, $adresseLieu, $nom, $thematique, $tarif) {
        $this->date = $date;
        $this->lieuID = $lieuID;
        $this->nomLieu = $nomLieu;
        $this->adresseLieu = $adresseLieu;
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->tarif = $tarif;
    }
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function __get(string $attribut): mixed {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }
}