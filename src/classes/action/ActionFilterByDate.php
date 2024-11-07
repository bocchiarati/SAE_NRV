<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

class ActionFilterByDate extends Action
{

    // selection la date pour filtrer les spectacles par date
    function executeGet(): string
    {
        return <<<HTML
        <h2>Filtrer par date</h2>
        <form method="post" action="?action=filterByDate">
            <label for="date">Selectionnez une date :</label>
            <input type="date" id="date" name="date" required>
            <button type="submit">Filtrer</button>
        </form>
        HTML;
    }

    // affiche les spectacles avec details de la soiree pour la date selectionnee
    function executePost(): string
    {
        $selectedDate = $_POST['date'] ?? null;

        if (!$selectedDate) {
            return "<p>Aucune date selectionnee. Veuillez choisir une date pour filtrer les spectacles.</p>";
        }

        $repository = NrvRepository::getInstance();
        $filteredSpectacles = $repository->SpectaclesByDate($selectedDate);

        if (empty($filteredSpectacles->spectacles)) {
            return "<p>Aucune spectacle n'est prevue pour la date : $selectedDate.</p>";
        }

        $output = "<h2>Spectacles pour la date : $selectedDate</h2>";
//        foreach ($filteredSoirees as $soiree) {
//
//            $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());
//
//            if ($spectacles->valid()) {
//                $spectacleRenderer = new ListSpectacleRenderer($spectacles);
//                $output .= "<div style='margin-bottom: 20px;'>";
//                $output .= "<h3>Soiree: {$soiree->nomLieu}</h3>";
//                $output .= "<p><strong>Adresse:</strong> {$soiree->adresseLieu}</p>";
//                $output .= $spectacleRenderer->render(Renderer::LONG);
//                $output .= "</div>";
//            } else {
//                $output .= "<p>Aucun spectacle pour la soirée à {$soiree->nomLieu}.</p>";
//            }
//        }

        $render = new ListSpectacleRenderer($filteredSpectacles);
        $output .= $render->render(Renderer::LONG);
        return $output;
    }
}