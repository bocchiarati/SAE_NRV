<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

class ActionFilterByStyle extends Action
{

    function executeGet(): string
    {

        if(isset($_GET['id'])){
            $pdo = NrvRepository::getInstance();
            $spectacles = $pdo->SpectaclesByStyle($_GET['id']);

            $render = new ListSpectacleRenderer($spectacles);
            return $render->render(Renderer::LONG);
        }else {

            $choix = '';

            $pdo = NrvRepository::getInstance();
            $listStyle = $pdo->getAllStyle();

            foreach ($listStyle as $key => $style) {
                $choix .= '<a href="?action=filterByStyle&id=' . $key . '">' . $style . '</a>';
            }


            return <<<HTML
            <h2>Filtrer par style</h2>
            <div class="options">
                $choix
            </div>
            HTML;
        }
    }

    function executePost(): string
    {
        return "";
    }
}