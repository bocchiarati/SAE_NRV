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
        $output = '';

        $pdo = NrvRepository::getInstance();
        $spectacles = $pdo->getSpectacleBySoiree($this->soiree->id);
        $deuxDate = explode(' ', $this->soiree->date);

        $spectacleRenderer = new ListSpectacleRenderer($spectacles);
        $output .= "<div style='margin-bottom: 20px;'>";
        $output .= "<h3>Soiree : {$this->soiree->nom}</h3>";
        $output .= "<h3>-> {$this->soiree->nomLieu}</h3>";
        $output .= "<p><strong>Le:</strong> {$deuxDate[0]} <strong>à</strong> {$deuxDate[1]}</p>";
        $output .= "<p><strong>Adresse:</strong> {$this->soiree->adresseLieu}</p>";
        $output .= $spectacleRenderer->render(Renderer::LONG);
        $output .= "</div>";

        return $output;
    }
}