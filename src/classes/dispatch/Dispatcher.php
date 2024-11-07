<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action\ActionDefaut;
use iutnc\nrv\action\ActionRegister;
use iutnc\nrv\action\ActionSignin;
use iutnc\nrv\auth\AuthnProvider;

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
            "signin" => new ActionSignin(),
            "register" => new ActionRegister(),
            default => new ActionDefaut(),
        };
        $this->renderPage($action->execute());
    }

    /**
     * Rendu du site web
     */
    public function renderPage(string $resultat): void
    {
        $pageHtml = <<<END
        <html lang="fr">
        <header>
        <title>NRV</title>
        <meta charset="utf-8">
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'JetBrains Mono', monospace;
                background-color: #f4f4f4;
                margin: 0;
            }
            h1 {
                background-color: white;
                color: #333;
                text-align: center;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            ul{
                margin: 0;
            }
            li {
                list-style-type: none;
            }
            .nav li{
                padding: 5px 0;
            }
            a {
                text-decoration: none;
                color: #007BFF;
                font-weight: bold;
                background-color: #e2e6ea;
                border-radius: 5px;
            }
            a:hover {
                background-color: #d1d5d9;
            }
            div {
                margin: 0 20px;
                padding: 15px;
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
        </style>
        </header>
        <body>
        <h1>FESTIVAL NRV</h1>
        <ul class="nav">
            <li><a href="?">Accueil</a></li>
            <li><a href="?action=signin">Se connecter</a></li>
            <li><a href="?action=signout">Se deconnecter</a></li>
        </ul>
        <br>
        <div>$resultat</div>
        </body>
        </html>
        END;

        echo $pageHtml;
    }
}