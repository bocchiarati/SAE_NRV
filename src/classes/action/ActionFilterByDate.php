<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;

class ActionFilterByDate extends Action
{

    function executeGet(): string
    {
        $res = '
        <div>
            
        </div>
        ';
        return $res;
    }

    function executePost(): string
    {
        return "";
    }
}