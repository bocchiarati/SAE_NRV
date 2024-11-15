<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthException;

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
        <a href="?action=register" class="btn btn-outline-warning btn-orange p-2 mb-4">Pas de compte ? S'enregistrer</a>
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
            <div class="alert alert-danger text-center mt-4" role="alert">
                Erreur lors de la connexion avec vos identifiants.
            </div>
            <div class="d-flex justify-content-center align-items-center flex-column mb-4">
                <a href="?action=signin" class="btn btn-outline-warning btn-orange p-2 my-2">Réessayer</a>
                <a href="?action=register" class="btn btn-outline-warning btn-orange p-2">S'enregistrer</a>
            </div>
            END;
        }

        return <<<END
        <div class="alert alert-success text-center d-flex justify-content-center" role="alert">
            Connexion réussie en tant que {$login}. Redirection vers la page d'accueil dans <span id="countdown" class = 'ms-2'>3</span>...
        </div>
        <script>
            var seconds = 3;
            var countdown = document.getElementById('countdown');
            var interval = setInterval(function() {
                seconds--;
                countdown.innerHTML = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = '?action=default';
                }
            }, 1000);
        </script>
        END;
    }
}