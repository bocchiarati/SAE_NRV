<?php

namespace iutnc\nrv\render;

use DateTime;
use iutnc\nrv\programme\Soiree;
use iutnc\nrv\repository\NrvRepository;

class SoireeRenderer implements Renderer
{
    private Soiree $soiree;

    public function __construct(Soiree $soiree)
    {
        $this->soiree = $soiree;
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

    public function renderCompact(): string
    {

        $deuxDate = explode(' ', $this->soiree->date);

        return <<<HTML
        <div class="col p-4 d-flex flex-column" style="text-align: center; border-radius: 8px;">
            <h2 style="color: #ff8c00">{$this->soiree->nom}</h2>
            <div style="font-size: 1.1rem; margin-bottom: 20px;">
                <strong>Le</strong> {$deuxDate[0]} <strong>à</strong> {$deuxDate[1]} <br>
                <strong>Lieu:</strong> {$this->soiree->nomLieu}, {$this->soiree->adresseLieu}
            </div>
            <a href="?action=showSoireeDetails&id={$this->soiree->id}" class="btn btn-outline-warning btn-orange" style="text-decoration: none; border-radius: 5px; font-size: 1rem; font-weight: bold;">
                Découvrez plus et réservez votre place (ou pas) ! 
            </a>
        </div>
        HTML;
    }

    public function renderLong(): string
    {
        $pdo = NrvRepository::getInstance();
        $spectacles = $pdo->getSpectacleBySoiree($this->soiree->id);
        $spectacleRenderer = new ListSpectacleRenderer($spectacles);
        $spectacleListHTML = $spectacleRenderer->render(Renderer::LONG);

        $date = new DateTime($this->soiree->date);
        $formattedDate = $date->format('d M Y');
        $formattedTime = $date->format('H:i');

        return <<<HTML
        <div class="soiree-details">
            <h1 class="display-3 text-center mb-3 mt-3" >{$this->soiree->nom}</h1>
            <p class="lead">
                    <strong>Date:</strong> {$formattedDate} at {$formattedTime} <br>
                    <strong>Lieu:</strong> {$this->soiree->nomLieu} - {$this->soiree->adresseLieu} <br>
                    <strong>Thématique:</strong> {$this->soiree->thematique} <br>
                    <strong>Tarif:</strong> {$this->soiree->tarif}€
            </p>
            <div class="spectacle-list">
                {$spectacleListHTML}
            </div>
        </div>
        HTML;
    }

}