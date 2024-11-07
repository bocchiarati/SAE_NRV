<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\Spectacle;
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
        foreach ($spectacles as $spec) {
            $renderer = new SpectacleRenderer($spec);
            $affichage .= $renderer->render(Renderer::COMPACT)."<br>";
        }
        return $affichage;
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}