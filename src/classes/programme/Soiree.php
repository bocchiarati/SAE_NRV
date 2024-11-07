<?php

namespace iutnc\nrv\programme;

use iutnc\nrv\repository\NrvRepository;

class Soiree extends ListSpectacle {

    protected ?string $date;
    protected int $lieuID;
    protected ?string $nomLieu;
    protected ?string $adresseLieu;

    public function __construct($date, $lieuID) {
        $this->date = $date;
        $this->lieuID = $lieuID;
        $pdo = NrvRepository::getInstance();
        $this->nomLieu = $pdo->nomLieuByID($this->lieuID);
        $this->adresseLieu = $pdo->adresseLieuByID($this->lieuID);
    }






}