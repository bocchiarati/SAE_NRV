<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class ActionSignin extends Action
{

    function executeGet(): string
    {
        return <<<END
        <form method="post" action="?action=signin">
            <input type="text" name="login" placeholder="Login" required autocomplete="true" autofocus>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit">Valider</button>
        </form>
        <br>
        <a href="?action=register" class="btn btn-outline-warning btn-orange p-2">Pas de compte ? S'enregistrer</a>
        END;
    }

    function executePost(): string
    {
        $login = filter_var($_POST['login'],FILTER_SANITIZE_URL);
        $mdp = $_POST['mdp'];
        try {
            AuthnProvider::signin($login, $mdp);
        } catch (AuthException $e) {
            return <<<END
                Erreur avec vos credentials
                <br>
                <a href="?action=signin" class="btn btn-outline-warning btn-orange p-1">Reesayer</a>
                <br>
                <a href="?action=register" class="btn btn-outline-warning btn-orange p-1">S'enregistrer</a>
            END;
        }

        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "erreur";
        }

        $pdo = NrvRepository::getInstance();
        $list = $pdo->findPreferences($user->id);
        $_SESSION['pref'] = serialize($list);

        return <<<END
            Bienvenue sur votre session $login<br>
        END;
    }
}