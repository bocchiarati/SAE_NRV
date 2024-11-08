<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\Spectacle;
use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\repository\NrvRepository;

class ActionShowProgram extends Action
{

    /**
     * @throws RepoException
     */
    function executeGet(): string
    {
        $pdo = NrvRepository::getInstance();
        $spectacles = $pdo->findAllSpectacle();
        $affichage = "";
        if(count($spectacles->spectacles) > 0){
            $renderer = new ListSpectacleRenderer($spectacles);
            $affichage .= $renderer->render(Renderer::COMPACT);
        }
        else {
            $affichage .= "<p>Aucun spectacle programmé</p>";
        }
        return $affichage;
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}