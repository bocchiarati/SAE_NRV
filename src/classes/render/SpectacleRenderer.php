<?php

namespace iutnc\nrv\render;

use DateTime;
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
        $date = $repository->getDateForSpectacle($this->spec->getSoireeID());

        //cas où le spectacle n'est pas programmé à une soirée
        if($date === "NULL"){
            $formattedDate = 'Indéfini';
            $formattedTime = 'Indéfini';
        }else {
            $date = new \DateTime($date);
            $formattedDate = $date->format('d M Y');
            $formattedTime = $date->format('H:i');
        }

        $annuler = '';
        if($repository->getSpectacleAnnuler($this->spec->getID())){
            $annuler = '<img src="image/annuler.png" alt="Cancel Image" class="position-absolute top-0 start-0 h-30 w-100 mt-3" style="background-color: rgba(255, 255, 255, 0.3)">';
        }

        return <<<HTML
            <a href='?action=showSpectacleDetails&spectacleid={$this->spec->getID()}&soireeid={$this->spec->getSoireeID()}' class='spectacle-item'>
            <div class="image-container-compact-render position-relative">
                <img src="image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image-compact w-100">
                
                {$annuler}
        
                <div class="triangle position-absolute bottom-0 start-0"></div>
                <div class="triangle-text position-absolute start-0 bottom-0 text-white" style="transform: translateY(-20%);">
                    <p class="m-0" style="max-width: 50%; overflow: hidden; text-overflow: ellipsis;">{$this->spec->getTitre()}</p>
                    <p class="m-0" style="max-width: 61%">{$formattedDate}</p>
                    <p class="m-0" style="max-width: 70%">À {$formattedTime}</p>
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
            $date = new DateTime();
        }else{
            $date = new DateTime($date);
        }
        $location = $repository->getLieuNomForSpectacle($this->spec->getSoireeID());
        if($location === "NULL"){
            $location = 'Non programmé';
        }

        // verifier la source de l'extrait
        $extrait = "";
        if (strpos($this->spec->getCheminExtrait(), "youtu.be/")) {
            $videoId = explode("/", $this->spec->getCheminExtrait())[3];
            $videoId = explode("?", $videoId)[0]; // Gestion des URL avec des parametres sup
            $extrait = <<<END
                <iframe width="560" height="315" src="https://www.youtube.com/embed/$videoId" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            END;
        } else if (str_ends_with($this->spec->getCheminExtrait(), "mp3")) {
            $extrait = <<<END
            <audio controls>
                <source src="extrait/{$this->spec->getCheminExtrait()}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            END;
        } else if (str_ends_with($this->spec->getCheminExtrait(), "mp4")) {
            $extrait = <<<END
            <video controls>
                <source src="extraits/{$this->spec->getCheminExtrait()}" type="video/mp4">
                Your browser does not support the video element.
            </video>
            END;

        }
        else {
            $extrait = "";

        }

        $formattedDate = $date->format('d M Y');
        $formattedTime = $date->format('H:i');

        return <<<HTML
        <div style="margin: 10px; display:flex">
            <div class="me-3">
                <h3 class="render-long-title mt-2 mb-3">{$this->spec->getTitre()}</h3>
                <strong class="render-long-strong mb-2">Groupe:</strong> {$this->spec->getGroupe()}<br>
                <strong class="render-long-strong mb-2">Date:</strong> {$formattedDate} at {$formattedTime} <br>
                <strong class="render-long-strong mb-2">Lieu:</strong> {$location}<br>
                <strong class="render-long-strong mb-2">Duree:</strong> {$this->spec->getDuree()} min<br>
                <strong class="render-long-strong mb-2">Description:</strong> {$this->spec->getDescription()}<br>
                <strong class="render-long-strong mb-2">Style:</strong> {$this->spec->getNomStyle()}<br>
                <strong class="render-long-strong mb-2">Capacité :</strong> {$repository->getCapacite($this->spec->getSoireeID())}<br>
            </div>
            $extrait
            <div class="image-container" style="margin-left: 10px">
                <img src="image/{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" class="image" width="290">
            </div>
        </div>
        HTML;
    }
}