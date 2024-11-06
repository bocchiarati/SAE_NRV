<?php

namespace iutnc\nrv\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\repository\DeefyRepository;

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
        <a href="?action=register">Pas de compte ? S'enregistrer</a>
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
                <a href="?action=signin">Reesayer</a>
                <br>
                <a href="?action=register">S'enregistrer</a>
            END;
        }

        return <<<END
            Bienvenue sur votre session $login<br>
        END;
    }
}