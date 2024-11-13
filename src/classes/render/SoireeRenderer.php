<?php

namespace iutnc\nrv\render;

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
        $output = '';

        $pdo = NrvRepository::getInstance();
        $deuxDate = explode(' ', $this->soiree->date);

        return <<<HTML
        <div class="promo-section" style="padding: 40px 20px; text-align: center; border-radius: 8px;">
            <div style="font-size: 1.1rem; margin-bottom: 20px;">
                <strong>Le:</strong> {$deuxDate[0]} <strong>à</strong> {$deuxDate[1]} <br>
                <strong>Lieu:</strong> {$this->soiree->nomLieu}, {$this->soiree->adresseLieu}
            </div>
            <a href="?action=showSoireeDetails&id={$this->soiree->id}" style="padding: 12px 30px; background-color: #f1590a; color: white; text-decoration: none; border-radius: 5px; font-size: 1rem; font-weight: bold; transition: background-color 0.3s;">
                Découvrez plus et réservez votre place (ou pas) ! 
            </a>
        </div>
        HTML;
    }

    private function renderLong(): string
    {
        $pdo = NrvRepository::getInstance();
        $spectacles = $pdo->getSpectacleBySoiree($this->soiree->id);
        $deuxDate = explode(' ', $this->soiree->date);

        $spectacleRenderer = new ListSpectacleRenderer($spectacles);
    }
}