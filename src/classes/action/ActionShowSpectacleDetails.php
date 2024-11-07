<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\RepoException;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\repository\NrvRepository;

// classe qui affiche les details d'un spectacle
class ActionShowSpectacleDetails extends Action
{

    function executeGet(): string
    {
        $spectacleID = $_GET['id'] ?? null;

        if (!$spectacleID) {
            return "<p>Aucun spectacle selectionne.</p>";
        }

        $repository = NrvRepository::getInstance();
        try {
            $spectacle = $repository->SpectacleByID($spectacleID);

            $renderer = new SpectacleRenderer($spectacle);
            return $renderer->render(Renderer::LONG);

        } catch (RepoException $e) {
            return "<p>Erreur lors de la recuperation du spectacle : {$e->getMessage()}</p>";
        }
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}