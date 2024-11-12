<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;

class ActionToggleCancelSpectacle extends Action
{

    function executeGet(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsOrga()){
            return "Vous n'avez pas le droit d'être ici";
        }

        if(isset($_GET['id'])){
            return "Le spectacle a correctement été annulé";
        }else{
            return "Aucun spectacle a annulé";
        }
    }

    function executePost(): string
    {
        return "nothing to return";
    }
}