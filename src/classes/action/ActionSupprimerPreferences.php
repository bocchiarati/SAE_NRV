<?php

namespace iutnc\nrv\action;

class ActionSupprimerPreferences extends Action
{

    function executeGet(): string
    {
        unset($_SESSION['pref']);
        return "Vos preferences ont correctement été effacé";
    }

    function executePost(): string
    {
        return "nothing to print";
    }
}