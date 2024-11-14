<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class ActionPromoteToOrga extends Action {
    function executeGet(): string {

        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsAdmin()){
            return "Vous n'avez pas le droit d'être ici";
        }

        $options = "";
        $pdo = NrvRepository::getInstance();
        foreach($pdo->getAllUsersNotOrga() as $id => $user) {
            $options .= "<option value='$id'>$user</option>";
        }
        return <<< END
        <form method="post" action="?action=promoteOrga">
            <select name="login">
                $options
            </select>
            <button type="submit">Promouvoir</button>
        </form>
        <br>
        END;

    }

    /**
     * @throws AuthException
     */
    function executePost(): string
    {

        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsAdmin()){
            return "Vous n'avez pas le droit d'être ici";
        }

        if(filter_var($_POST['login'],FILTER_VALIDATE_INT)) {
            $pdo = NrvRepository::getInstance();
            return $pdo->promoteOrga($_POST['login']);
        }else{
            return "Problème avec l'id user";
        }
    }
}