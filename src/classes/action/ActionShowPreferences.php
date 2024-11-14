<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\ListSpectacle;
use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

/**
 * Classe ActionFiltre qui permet de filtrer les spectacles
 */
class ActionShowPreferences extends Action {


    // Execute l'action GET, applique les filtres si necessaires
    public function executeGet(): string {
        try {
            $pdo = NrvRepository::getInstance();
            $user = AuthnProvider::getSignedInUser();
            $spectaclesID = $pdo->findPreferences($user->getID());
            $spectacles = [];
            $listSpectacles = new ListSpectacle();
            foreach ($spectaclesID as $id) {
                $spectacles[] = $pdo->getSpectacleById($id);
            }
            $listSpectacles->setSpectacles($spectacles);
            $output = $spectacles ?
                (new ListSpectacleRenderer($listSpectacles))->render(Renderer::COMPACT) :
                "<p>Aucun spectacle programmé</p>";
        } catch (AuthException) {
            $output = "Aucun utilisateur connecté";
        }
        return '<div class="affichage">'.$output.'</div>';
    }

    // Construit les liens pour chaque categorie de filtre
    public function executePost(): string {
        return "nothing to return";
    }

}