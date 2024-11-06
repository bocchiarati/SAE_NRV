<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AudioTrack;

class Playlist extends AudioList
{

    public function __construct(string $nom, array $tab=[])
    {
        parent::__construct($nom, $tab);
    }

    private function updateStats(): void
    {
        $this->nbPiste = count($this->tab);
        $this->dureeTotale = 0;
        foreach ($this->tab as $piste){
            $this->dureeTotale += $piste->duree;
        }
    }

    public function addTrack(AudioTrack $piste): void
    {
        $this->tab[] = $piste;
        $this->updateStats();
    }

    public function delTrack(int $indice): void
    {
        unset($this->tab[$indice]);
        $this->updateStats();
    }

    public function addList(array $list): void
    {
        $this->tab = array_unique(array_merge($this->tab, $list));
        $newTab = [];
        foreach ($this->tab as $piste){
            $newTab[] = $piste;
        }
        $this->tab = $newTab;
        $this->updateStats();
    }
}