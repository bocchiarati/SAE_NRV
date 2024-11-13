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

        $spectacleRenderer = new ListSpectacleRenderer($spectacles);
        $output .= $spectacleRenderer->render(Renderer::LONG);

        return $output;
    }
}