<?php

namespace iutnc\nrv\action;

class ActionDefaut extends Action
{

    public function executeGet(): string
    {
        return "<h1>Bienvenue au Festival NRV !</h1>";
    }

    public function executePost(): string
    {
        return "Aucune action selectionnée ou action invalide";
    }
}