<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class ActionPromoteToOrga extends Action {
    function executeGet(): string {
        $options = "";
        $pdo = NrvRepository::getInstance();
        foreach($pdo->getAllUsersNotAdmin() as $id => $user) {
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
        $login = filter_var($_POST['login'],FILTER_SANITIZE_URL);
        $pdo = NrvRepository::getInstance();

        return $pdo->promoteOrga($_POST['login']);
    }
}