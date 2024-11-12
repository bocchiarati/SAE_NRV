<?php

namespace iutnc\nrv\render;

use iutnc\nrv\exception\RepoException;
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
        if($date === "NULL"){
            $deuxDate = [0 => 'Indéfini', 1 => 'Indéfini'];
        }else {
            $deuxDate = explode(' ', $date);
        }

        $annuler = '';
        if($repository->getSpectacleAnnuler($this->spec->getID())){
            $annuler = '<img src="../image/annuler.png" alt="Cancel Image" class="position-absolute top-0 start-0 h-30 w-100 mt-3">';
        }

        return <<<HTML
            <a href='?action=showSpectacleDetails&id={$this->spec->getID()}' class='spectacle-item'>
                <div class="image-container-compact-render">
                    <img src="../image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image-compact">
                    <img src="../image/triangle-rouge.png" alt="Corner Image" class="corner-image">
                    {$annuler}
                    <div class="position-absolute bottom-0 start-0 h-25 fs-6 lh-1 mb-1 ms-1">
                        <p class="m-0 w-75">{$this->spec->getTitre()}</p>
                        <p class="m-0">{$deuxDate[0]}</p>
                        <p class="m-0">À {$deuxDate[1]}</p>  
                        <p class="m-0">Durée : {$this->spec->getDurationHoursEtMin()}</p>
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
        //cas où le spectacle n'est pas programmé à une soirée
        if($date === "NULL"){
            $date = 'Non programmé';
        }
        $location = $repository->getLieuNomForSpectacle($this->spec->getID());
        if($location === "NULL"){
            $location = 'Non programmé';
        }

        if(str_ends_with($this->spec->getCheminExtrait(), "mp3")) {
            $extrait = <<<END
            <audio controls>
                <source src="../extrait/{$this->spec->getCheminExtrait()}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            END;
        }
        else if (str_ends_with($this->spec->getCheminExtrait(), "mp4")) {
            $extrait = <<<END
            <video controls>
                <source src="../extraits/{$this->spec->getCheminExtrait()}" type="video/mp4">
                Your browser does not support the video element.
            </video>
            END;

        }
        else {
            $extrait = "";

        }

        return <<<HTML
        <div style="margin: 10px; display:flex">
            <div>
                <h3>{$this->spec->getTitre()}</h3>
                <strong>Groupe:</strong> {$this->spec->getGroupe()}<br>
                <strong>Date:</strong> {$date}<br>
                <strong>Lieu:</strong> {$location}<br>
                <strong>Duree:</strong> {$this->spec->getDuree()} min<br>
                <strong>Description:</strong> {$this->spec->getDescription()}<br>
                <strong>Style:</strong> {$this->spec->getNomStyle()}<br>
                
            </div>
            $extrait
            <div class="image-container" style="margin-left: 10px">
                <img src="../image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image" width="290">
            </div>
        </div>
        HTML;
    }

}