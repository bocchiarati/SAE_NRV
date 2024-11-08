<?php

namespace iutnc\nrv\render;

use iutnc\nrv\programme\Spectacle;
use iutnc\nrv\repository\NrvRepository;

class SpectacleRenderer implements Renderer {

    private Spectacle $spec;

    public function __construct(Spectacle $spec)
    {
        $this->spec = $spec;
    }

    public function render(int $selector): string
    {
        switch ($selector) {
            case self::COMPACT:
                $res = $this->renderCompact();
                break;
            case self::LONG:
                $res = $this->renderLong();
                break;
            default:
                $res = "Mode invalide";
                break;
        }
        return $res;
    }

    private function renderCompact(): string
    {
        // recuperer la date du spectacle
        $repository = NrvRepository::getInstance();
        $date = $repository->getDateForSpectacle($this->spec->getID());

        //cas où le spectacle n'est pas programmé à une soirée
        if(!$date){
            $deuxDate = [0 => 'Indéfini', 1 => 'Indéfini'];
        }else {
            $deuxDate = explode(' ', $date);
        }

        return <<<HTML
            <a href='?action=showSpectacleDetails&id={$this->spec->getID()}' class='spectacle-item'>
                <div class="image-container-compact-render">
                    <img src="../image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image-compact">
                    <img src="../image/triangle-rouge.png" alt="Corner Image" class="corner-image">
                    <div class="corner-text">
                        <p class="titrespect">{$this->spec->getTitre()}</p>
                        <p>{$deuxDate[0]}</p>  
                        <p>À {$deuxDate[1]}</p> 
                        <p>Durée : {$this->spec->getDurationHoursEtMin()}</p>
                    </div>
                </div>
            </a>
        HTML;
    }

    private function renderLong(): string
    {
        // recuperer la date et le lieu du spectacle
        $repository = NrvRepository::getInstance();
        $date = $repository->getDateForSpectacle($this->spec->getID());
        $location = $repository->getLieuNomForSpectacle($this->spec->getID());

        return <<<HTML
        <div style="margin: 10px;">
            <h3>{$this->spec->getTitre()}</h3>
            <strong>Groupe:</strong> {$this->spec->getGroupe()}<br>
            <strong>Date:</strong> {$date}<br>
            <strong>Lieu:</strong> {$location}<br>
            <strong>Duree:</strong> {$this->spec->getDuree()} min<br>
            <strong>Description:</strong> {$this->spec->getDescription()}<br>
            <strong>Style:</strong> {$this->spec->getNomStyle()}<br>
            <div class="image-container">
            <img src="../image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image" width="150">
            <audio controls>
                    <source src="{$this->spec->getCheminExtrait()}" type="audio/mpeg">
                    Your browser does not support the audio element.
            </audio>
            </div>
        </div>
        HTML;
    }

}