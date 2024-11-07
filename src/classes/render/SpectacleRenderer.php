<?php

namespace iutnc\nrv\render;

use iutnc\nrv\programme\Spectacle;

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
        return <<<HTML
            <div style="margin: 10px;">
                <strong>Titre:</strong> {$this->spec->getTitre()}<br>
                <img src="{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" width="100">
            </div>
        HTML;
    }

    private function renderLong(): string
    {
        return <<<HTML
            <div style="margin: 10px;">
                <h3>{$this->spec->getTitre()}</h3>
                <strong>Groupe:</strong> {$this->spec->getGroupe()}<br>
                <strong>Duree:</strong> {$this->spec->getDuree()} min<br>
                <strong>Description:</strong> {$this->spec->getDescription()}<br>
                <strong>Style:</strong> {$this->spec->getNomStyle()}<br>
                <img src="{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" width="150">
            </div>
        HTML;
    }

}