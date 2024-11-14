<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\repository\NrvRepository;

class ActionSavePreference extends Action
{

    /**
     * @throws AuthException
     * @throws RepoException
     */
    function executeGet(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsOrga()) {
            return "Vous n'avez pas le droit d'être ici";
        }

        if(isset($_GET['id'])) {
            $pdo = NrvRepository::getInstance();
            $pdo->saveSpectaclePreferences($pdo->getSpectacleByID($_GET['id']));
            return "Le spectacle a correctement été enregistrer";
        } else {
            return "Aucun spectacle";
        }
    }

    function executePost(): string
    {
        return "nothing to return";
    }
}