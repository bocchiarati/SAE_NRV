<?php

namespace iutnc\nrv\programme;

class Soiree extends ListSpectacle {

    protected int $id;
    protected ?string $date;
    protected array $lieuID;
    protected array $nomLieu;
    protected array $adresseLieu;

    public function __construct() {
    }


}