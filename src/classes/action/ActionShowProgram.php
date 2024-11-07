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

    function executeGet(): string
    {
        $pdo = NrvRepository::getInstance();

        $spectacle = new Spectacle(null, null, null, null, null, null, null, null);
        $renderer = new SpectacleRenderer($spectacle);
        return $renderer->render(Renderer::LONG);
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}