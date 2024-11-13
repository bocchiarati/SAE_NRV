<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\repository\NrvRepository;

class ActionShowSoireeDetails extends Action
{

    function executeGet(): string
    {
        $repository = NrvRepository::getInstance();
        $soireeId = $_GET['id'] ?? null;

        if (is_null($soireeId)) {
            return "ID de la soirée n'est pas spécifié.";
        }

        $soiree = $repository->getSoireeById($soireeId);

        if ($soiree === null) {
            return "Soirée non trouvée.";
        }

        $soireeRenderer = new SoireeRenderer($soiree);
        return $soireeRenderer->render(SoireeRenderer::LONG);
    }

    function executePost(): string
    {
        return "nothing to print";
    }
}