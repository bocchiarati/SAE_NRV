<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
use iutnc\nrv\repository\NrvRepository;

class ActionFilterByDate extends Action
{

    // selection la date pour filtrer les soirees par date
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

    // affiche les soirees avec les spectacles pour la date selectionnee
    function executePost(): string
    {
        $selectedDate = $_POST['date'] ?? null;

        if (!$selectedDate) {
            return "<p>Aucune date selectionnee. Veuillez choisir une date pour filtrer les soirees.</p>";
        }

        $repository = NrvRepository::getInstance();
        $filteredSoirees = $repository->SoireeByDate($selectedDate);
//        $soireesWithSpectacles = $repository->programmeSpectacleBySoiree($selectedDate);


        if (empty($filteredSoirees)) {
            return "<p>Aucune soiree n'est prevue pour la date : $selectedDate.</p>";
        }

        $output = "<h2>Soirees pour la date : $selectedDate</h2><ul>";
        foreach ($filteredSoirees as $soiree) {
            $output .= "<li>Soiree: {$soiree->nomLieu} - Adresse: {$soiree->adresseLieu}</li>";

            // affiche les spectacles pour chaque soiree
            $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());
            if (!empty($spectacles)) {
                $output .= "<ul>";
                foreach ($spectacles as $spectacle) {
                    $output .= "<li>
                        <strong>Titre:</strong> {$spectacle->titre}<br>
                        <strong>Groupe:</strong> {$spectacle->groupe}<br>
                        <strong>Duree:</strong> {$spectacle->duree} min<br>
                        <strong>Description:</strong> {$spectacle->description}<br>
                        <strong>Style:</strong> {$spectacle->nomStyle}<br>
                        <img src='{$spectacle->image}' alt='{$spectacle->titre}' width='100'><br>
                    </li>";
                }
                $output .= "</ul>";
            } else {
                $output .= "<p>Aucun spectacle pour cette soirée.</p>";
            }
        }
        $output .= "</ul>";
        return $output;
    }
}