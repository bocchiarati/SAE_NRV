<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action as act;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\auth\User;
use iutnc\nrv\exception\AuthException;

class Dispatcher
{
    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function run(): void
    {

        //Cas pour se deconnecter
        if($this->action == "signout"){
            AuthnProvider::signout();
            $this->action = "signin";
        }

        //Les autres actions sur le site
        $action = match ($this->action) {
            "signin" => new act\ActionSignin(),
            "register" => new act\ActionRegister(),
            "showProgram" => new act\ActionShowProgram(),
            "showSpectacleDetails" => new act\ActionShowSpectacleDetails(),
            "cancelSpectacle" => new act\ActionCancelSpectacle(),
            "createSoiree" => new act\ActionCreateSoiree(),
            "createSpectacle" => new act\ActionCreateSpectacle(),
            "editSoiree" => new act\ActionEditSoiree(),
            "editSpectacle" => new act\ActionEditSpectacle(),
            "filtre" => new act\ActionFiltre(),
            default => new act\ActionDefaut(),
        };
        $this->renderPage($action->execute());
    }

    /**
     * Rendu du site web
     */
    public function renderPage(string $resultat): void
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            $user = new User(-1,"Non connecté","mdp",User::STANDARD_USER);
        }

        $check = new Authz($user);
        if($check->checkRole(USER::ORGANISATOR_USER))
            $superAdmin = <<<END
        <ul class="nav">
            <li><a href="?action=createSoiree">Creer Une Soirée</a></li>
            <li><a href="?action=createSpectacle">Creer Un Spectacle</a></li>
            <li><a href="?action=cancelSpectacle">Annuler un spectacle</a></li>
            <li><a href="?action=editSpectacle">Modifier Un Spectacle</a></li> 
            <li><a href="?action=editSoiree">Modifier Une Soirée</a></li>         
        </ul>
END;

        else
            $superAdmin = "";


        $pageHtml = <<<END
        <html lang="fr">
        <header>
        <title>NRV</title>
        <meta charset="utf-8">
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../css/style.css">
        </header>
        <body>
        <div class="titre">
        <p id="user">{$user->email}</p>
        <h1>FESTIVAL NRV</h1>
        </div>
        <ul class="nav">
            <li><a href="?">Accueil</a></li>
            <li><a href="?action=signin">Se connecter</a></li>
            <li><a href="?action=signout">Se deconnecter</a></li>
            <li><a href="?action=showProgram">Afficher le programme</a></li>
            <li><a href="?action=filtre">Filtrer</a></li>
                    
        </ul>
        {$superAdmin}
        
        
        <div class="resultat">$resultat</div>
        </body>
        </html>
        END;

        echo $pageHtml;
    }
}