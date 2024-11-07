<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
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
        $filteredSoirees = $repository->SoireeByDate($selectedDate);

        if (empty($filteredSoirees)) {
            return "<p>Aucune spectacle n'est prevue pour la date : $selectedDate.</p>";
        }

        $output = "<h2>Spectacles pour la date : $selectedDate</h2><ul>";
        foreach ($filteredSoirees as $soiree) {

            $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());

            foreach ($spectacles as $spectacle) {
                // info sur le spectacle
                $output .= "<li><strong>Titre:</strong> {$spectacle->titre}</li>";
                $output .= "<div style='margin-left: 20px;'>
                                <strong>Groupe:</strong> {$spectacle->groupe}<br>
                                <strong>Duree:</strong> {$spectacle->duree} min<br>
                                <strong>Description:</strong> {$spectacle->description}<br>
                                <strong>Style:</strong> {$spectacle->nomStyle}<br>
                                <img src='{$spectacle->image}' alt='{$spectacle->titre}' width='100'><br>
                            </div>";

                // info sur la soiree du spectacle
                $output .= "<p style='margin-left: 20px;'><strong>Soiree:</strong> {$soiree->nomLieu} - <strong>Adresse:</strong>  {$soiree->adresseLieu}</p>";
            }
        }
        $output .= "</ul>";
        return $output;
    }
}