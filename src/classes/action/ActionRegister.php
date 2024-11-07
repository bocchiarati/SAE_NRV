<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;

class ActionRegister extends Action
{

    function executeGet(): string
    {
        return <<<END
        <h2>Creer un compte Deefy</h2>
        <form method="post" action="?action=register">
            <input type="email" name="login" placeholder="Adresse email" required autocomplete="true" autofocus>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit">Valider</button>
        </form>
        <br>
        <a href="?action=signin">Dèjà inscrit ? Se connecter</a>
        END;

    }

    function executePost(): string
    {
        $login = $_POST['login'];
        $mdp = $_POST['mdp'];
        try {
            AuthnProvider::register($login, $mdp);
        } catch (AuthException $e) {
            return <<<END
                Nom d'utilisateur invalide
                <br>
                <a href="?action=register">Reesayer</a>
                <br>
                <a href="?action=signin">Se connecter</a>
            END;
        }

        return <<<END
            Bienvenue sur votre session $login<br>
        END;
    }
}