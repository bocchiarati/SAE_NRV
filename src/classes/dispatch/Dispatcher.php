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
            "showSpectacleDetails" => new act\ActionShowSpectacleDetails(),
            "showSoireeDetails" => new act\ActionShowSoireeDetails(),
            "cancelSpectacle" => new act\ActionCancelSpectacle(),
            "createSoiree" => new act\ActionCreateSoiree(),
            "createSpectacle" => new act\ActionCreateSpectacle(),
            "editSoiree" => new act\ActionEditSoiree(),
            "editSpectacle" => new act\ActionEditSpectacle(),
            "cancel" => new act\ActionCancelSpectacle(),
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
        if($check->checkIsOrga()) {
            $menuOrga = <<<END
            <div class="dropdown" onmouseleave="hideDropdown()">
                <button class="nav-links bg-transparent border-0" type="button" id="dropdownMenuButton" aria-expanded="false" onclick="toggleDropdown()">
                    Menu Orga
                </button>
                <ul class="nav-links dropdown-menu bg-secondary bg-opacity-25 border-0"" aria-labelledby="dropdownMenuButton" id="dropdownMenu">
                    <li><a class="dropdown-item bg-transparent" href="?action=createSoiree">Créer Une Soirée</a></li>
                    <li><a class="dropdown-item bg-transparent" href="?action=createSpectacle">Créer Un Spectacle</a></li>
                    <li><a class="dropdown-item bg-transparent" href="?action=editSpectacle">Modifier Un Spectacle</a></li>
                    <li><a class="dropdown-item bg-transparent" href="?action=editSoiree">Modifier Une Soirée</a></li>
                </ul>       
            </div>
            
            <script>           
                // Fonction pour cacher le menu lorsqu'on quitte
                function hideDropdown() {
                    var dropdownMenu = document.getElementById("dropdownMenu");
                    dropdownMenu.classList.remove('show');  // Cacher le menu
                }
                
                // Fonction pour basculer l'état du menu (afficher ou masquer) au clic
                function toggleDropdown() {
                    var dropdownMenu = document.getElementById("dropdownMenu");
                    dropdownMenu.classList.toggle('show');  // Bascule entre afficher et masquer
                }
            </script>

            END;
        } else {
            $menuOrga = "";
        }


        $pageHtml = <<<END
        <html lang="fr">
        <head>
            <title>Festival NRV</title>
            <meta charset="utf-8">
            <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
            <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="../css/style.css">
        </head>
        <body>
            <header class="jumbotron">
                <div class="navbar d-flex align-items-center justify-content-between mb-5 p-3">
                        <div class="branding d-flex align-items-center">
                            <p id="user" class="me-5">{$user->email}</p>
                            <h1 class="ms-3">NRV.net</h1>
                        </div>
                        <div class="nav-links d-flex align-items-center gap-3">
                            <a href="?">Accueil</a>
                            <a href="?action=signin">Se connecter</a>
                            <a href="?action=signout">Se deconnecter</a>
                            <a href="?action=filtre">Programme</a> 
                            {$menuOrga} 
                        </div>
                </div>
                 <div class="container">
                    <h1>Nancy Rock Vibration Festival 2025</h1>
                    <p class="lead">1st June - 15th June 2025</p>
                    <hr class="my-4" style="width: 10%; margin: auto; border-top: 2px solid #ff8c00;">
                    <p>Year after year the festival pulls together an incredible lineup unencumbered by genre boundaries, uniting alternative favourites and discerning music fans from across the globe.</p>
                </div>
            </header>
            
            <div class="container p-2">
                <div class="d-flex flex-column align-items-center">$resultat</div>
            </div>
            
            <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        </body>
        <script>
        function toggleTab(tabName) {
            var tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                if (tab.id === tabName) {
                    tab.style.display = tab.style.display === 'block' ? 'none' : 'block';
                } else {
                    tab.style.display = 'none';
                }
            });
        }
        </script>

        </html>
        END;

        echo $pageHtml;
    }
}
