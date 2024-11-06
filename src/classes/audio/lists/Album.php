<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\lists\AudioList;

class Album extends AudioList
{

    private string $artiste;
    private string $dateSortie;

    public function __construct(string $nom, array $tab)
    {
        parent::__construct($nom, $tab);
    }

    public function setArtiste(string $artiste): void
    {
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $dateSortie): void
    {
        $this->dateSortie = $dateSortie;
    }

}