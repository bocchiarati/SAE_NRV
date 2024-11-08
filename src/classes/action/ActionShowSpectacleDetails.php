<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\RepoException;
use iutnc\nrv\render\ListSpectacleRenderer;
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
            $spectacle = $repository->getSpectacleByID($spectacleID);

            $renderer = new SpectacleRenderer($spectacle);

            // affichage des spectacles du meme style et lieu sans le spectacle actuel
            $spectaclesMemeStyle = $repository->getSpectaclesByStyleSansActuel($spectacle->getStyleID(), $spectacleID);
            $spectaclesMemeLieu = $repository->getSpectaclesByLieuSansActuel($spectacleID);

            $rendererStyle = new ListSpectacleRenderer($spectaclesMemeStyle);
            $rendererLieu = new ListSpectacleRenderer($spectaclesMemeLieu);

            $affichageSimilaires = "<h2>VOUS AIMEREZ AUSSI</h2>";

            $affichageSimilaires .= "<h3>Spectacles du même style:</h3>";
            $affichageSimilaires .= $rendererStyle->render( Renderer::COMPACT);

            $affichageSimilaires .= "<h3>Spectacles du même lieu:</h3>";
            $affichageSimilaires .= $rendererLieu->render(Renderer::COMPACT);

            // affichage des spectacles du meme date sans le spectacle actuel
            // TO DO

            return $renderer->render(Renderer::LONG) . $affichageSimilaires;

        } catch (RepoException $e) {
            return "<p>Erreur lors de la recuperation du spectacle : {$e->getMessage()}</p>";
        }
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}