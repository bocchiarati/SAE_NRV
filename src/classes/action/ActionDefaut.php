<?php

namespace iutnc\deefy\action;

class ActionDefaut extends Action
{

    public function executeGet(): string
    {
        return "<h1>Bienvenue sur Deefy !</h1>";
    }

    public function executePost(): string
    {
        return "Aucune action selectionnee ou action invalide";
    }
}