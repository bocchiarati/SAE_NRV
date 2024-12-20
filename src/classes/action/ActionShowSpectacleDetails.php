<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
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
        $spectacleID = $_GET['spectacleid'] ?? null;
        $soireeID = $_GET['soireeid'] ?? null;
        if (!$spectacleID) {
            return "<p>Aucun spectacle selectionne.</p>";
        }

        $repository = NrvRepository::getInstance();
        try {
            if(isset($soireeID))
                $spectacle = $repository->getSpectacleBySoireeToSpectacle($soireeID, $spectacleID);
            else
                $spectacle = $repository->getSpectacleByID($spectacleID);

            $renderer = new SpectacleRenderer($spectacle);

            // affichage des spectacles du meme style et lieu et date sans le spectacle actuel
            $spectaclesMemeStyle = $repository->getSpectaclesByStyleSansActuel($spectacle->getStyleID(), $spectacleID, $soireeID);
            $spectaclesMemeLieu = $repository->getSpectaclesByLieuSansActuel($spectacleID, $soireeID);
            $spectaclesMemeDate = $repository->getSpectaclesByDateSansActuel($spectacleID, $soireeID);

            $rendererStyle = new ListSpectacleRenderer($spectaclesMemeStyle);
            $rendererLieu = new ListSpectacleRenderer($spectaclesMemeLieu);
            $rendererDate = new ListSpectacleRenderer($spectaclesMemeDate);

            // si il y a des spectacles similaires on les affiche
            $affichageSimilaires = "";
            if ($spectaclesMemeStyle->getSpectacles() || $spectaclesMemeLieu->getSpectacles() || $spectaclesMemeDate->getSpectacles()) {
                $affichageSimilaires .= "<h2 class='mt-5 mb-3'>VOUS AIMEREZ AUSSI</h2>";

                if ($spectaclesMemeStyle->getSpectacles()) {
                    $affichageSimilaires .= "<h3>Spectacles du même style:</h3>"
                        . $rendererStyle->render(Renderer::COMPACT);
                }

                if ($spectaclesMemeLieu->getSpectacles()) {
                    $affichageSimilaires .= "<h3>Spectacles du même lieu:</h3>"
                        . $rendererLieu->render(Renderer::COMPACT);
                }

                if ($spectaclesMemeDate->getSpectacles()) {
                    $affichageSimilaires .= "<h3>Spectacles à la même date:</h3>"
                        . $rendererDate->render(Renderer::COMPACT);
                }
            }

            $annulerSpectacle = '';
            try {
                $user = AuthnProvider::getSignedInUser();
                $droit = new Authz($user);
                if($droit->checkIsOrga()){
                    $annulerSpectacle = "<a href='?action=cancel&id={$spectacleID}' class='btn-spect-det m-2'>Annuler le spectacle</a>";
                }
            } catch (AuthException $e) {
                //aucun user connecté
            }

            $savePreference = "<a href='?action=savePreference&id={$spectacleID}' class='btn-spect-det m-2'><img src='image/EtoileVide.png' alt='Like' height='23'></a>";
            if(isset($_SESSION['pref'])){
                $listpref = unserialize($_SESSION['pref']);
                if($listpref->contientSpectacle($spectacle)){
                    $savePreference = "<a href='?action=savePreference&id={$spectacleID}' class='btn-spect-det m-2'><img src='image/EtoilePleine.png' alt='Like' height='23'></a>";
                }
            }

            $boutonSoiree = "<a href='?action=showSoireeDetails&id={$soireeID}' class='btn-spect-det m-2'>Accèder à la Soirée</a>";

            return $renderer->render(Renderer::LONG) .
                "<div class='d-flex align-items-center'>" . $annulerSpectacle . $savePreference . $boutonSoiree . "</div>" .
                $affichageSimilaires;

        } catch (RepoException $e) {
            return "<p>Erreur lors de la recuperation du spectacle : {$e->getMessage()}</p>";
        }
    }

    function executePost(): string
    {
        return "Nothing to return";
    }
}