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
         <style>
        .image-container {
            position: relative;
            display: inline-block;
        }
        .image {
            display: block;
            width: 350px; 
            height: 437px; 
        }
        .corner-image {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px; 
            height: 200px; 
        }
        .corner-text {
            position: absolute;
            bottom: 10px;
            left: 5px;
            color: white;
            font-size: 15px;
            text-align: left;
            line-height: 1.2;
            font-weight: bold;
        }
        </style>
            <div class="image-container">
                <img src="../image/{$this->spec->getImage()}" alt="Main Image" class="image" width="400">
                <img src="../image/triangle.png" alt="Corner Image" class="corner-image">
                <div class="corner-text">
                    <p>{$this->spec->getTitre()}</p>
                    <p>{$this->spec->getGroupe()}</p>    
                    <p>{$this->spec->getNomStyle()}</p>
                </div>
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
                <img src=../image/"{$this->spec->getImage()}" alt="{$this->spec->getTitre()}" width="150">
            </div>
        HTML;
    }

}