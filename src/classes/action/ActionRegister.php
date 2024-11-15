<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;

class ActionRegister extends Action
{

    function executeGet(): string
    {
        return <<<END
        <h2 class="mt-3">Creer un compte NRV</h2>
        <form method="post" action="?action=register">
            <input type="email" name="login" placeholder="Adresse email" required autocomplete="true" autofocus>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit">Valider</button>
        </form>
        <br>
        <a href="?action=signin" class="btn btn-outline-warning btn-orange p-2 mb-4">Dèjà inscrit ? Se connecter</a>
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
                <div class="alert alert-danger" role="alert">
                    Nom d'utilisateur invalide. Veuillez réessayer.
                </div>
                <div class="text-center">
                    <a href="?action=register" class="btn btn-outline-warning btn-orange p-1">Réessayer</a>
                    <a href="?action=signin" class="btn btn-outline-warning btn-orange p-1">Se connecter</a>
                </div>
            END;
        }

        return <<<END
            <div class="alert alert-success" role="alert">
                Compte créé avec succès. Bienvenue $login!<br>
                Vous serez redirigé vers la page d'accueil...
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = '?action=default';
                }, 3000);
            </script>
        END;
    }
}