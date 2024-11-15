<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class ActionSavePreferenceRepository extends Action
{

    function executeGet(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Vous devez être connecté";
        }

        $pdo = NrvRepository::getInstance();
        if(isset($_SESSION['pref'])){
            $pdo->delAllPreferences($user->id);
            $listpref = unserialize($_SESSION['pref']);
            foreach ($listpref->spectacles as $spec){
                $pdo->saveSpectaclePreferences($user->id,$spec->id,$spec->soireeID);
            }
            return "Vos préférences ont été mise à jour sur votre compte";
        }else{
            return "Aucune préférence enregistrée en session";
        }
    }

    function executePost(): string
    {
        return "nothing to print";
    }
}