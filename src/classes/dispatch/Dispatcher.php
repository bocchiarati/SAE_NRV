<?php

namespace iutnc\nrv\dispatch;

use iutnc\nrv\action as act;
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
            "signin" => new act\ActionSignin(),
            "register" => new act\ActionRegister(),
            "showProgram" => new act\ActionShowProgram(),
            "filterByDate" => new act\ActionFilterByDate(),
            "filterByLocation" => new act\ActionFilterByLocation(),
            "filterByStyle" => new act\ActionFilterByStyle(),
            default => new act\ActionDefaut(),
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
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
            }
            body {
                font-family: 'Roboto', sans-serif;
                background-color: #f4f4f4;
                display: flex;
                flex-direction: column;
            }
            .titre {
                background-color: white;
                color: #333;
                text-align: center;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .nav ul{
                margin: 0;
            }
            li {
                list-style-type: none;
            }
            .nav{
                height: 10%;
                display: flex;
                justify-content: center;
            }
            .nav li{
                margin: auto;
            }
            .nav a {
                text-decoration: none;
                color: #FF0000; /* Rouge éclatant pour plus de vivacité */
                font-weight: bold;
                padding: 0.5rem 1rem;
                background-color: #ffe6e6;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }
            a:hover {
                background-color: #ffcccc;            
            }
            .resultat {
                padding: 1%;
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
        </style>
        </header>
        <body>
        <h1 class="titre">FESTIVAL NRV</h1>
        <ul class="nav">
            <li><a href="?">Accueil</a></li>
            <li><a href="?action=signin">Se connecter</a></li>
            <li><a href="?action=signout">Se deconnecter</a></li>
            <li><a href="?action=showProgram">Afficher le programme</a></li>
            <li><a href="?action=filterByDate">Filtrer par date</a></li>
            <li><a href="?action=filterByStyle">Filtrer par style</a></li>
            <li><a href="?action=filterByLocation">Filtrer par lieu</a></li>
        </ul>
        <div class="resultat">$resultat</div>
        </body>
        </html>
        END;

        echo $pageHtml;
    }
}