<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

class ActionFilterByLocation extends Action
{

    // selection le lieu pour filtrer les spectacles par lieu
    function executeGet(): string
    {
        $repository = NrvRepository::getInstance();
        $locations = $repository->getAllLieu();

        // creation des options pour le select du lieu
        $options = '';
        foreach ($locations as $lieuID => $nom) {
            $options .= "<option value='{$lieuID}'>{$nom}</option>";
        }

        return <<<HTML
        <h2>Filtrer par lieu</h2>
        <form method="post" action="?action=filterByLocation">
            <label for="location">Selectionnez un lieu :</label>
            <select id="location" name="location" required>
                <option value="">--Choisissez un lieu--</option>
                $options
            </select>
            <button type="submit">Filtrer</button>
        </form>
        HTML;
    }

    // affiche les spectacles avec details de la soiree pour le lieu selectionne
    function executePost(): string
    {
        $selectedLocation = $_POST['location'] ?? null;

        if (!$selectedLocation) {
            return "<p>Aucun lieu selectione. Veuillez choisir un lieu pour filtrer les spectacles.</p>";
        }

        $repository = NrvRepository::getInstance();
        $filteredSoirees = $repository->SoireeByLocation($selectedLocation);

        if (empty($filteredSoirees)) {
            return "<p>Aucun spectacle n'est prevue pour ce lieu.</p>";
        }

        $output = "<h2>Spectacles pour le lieu selectionne</h2><ul>";
        foreach ($filteredSoirees as $soiree) {
            $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());

            if ($spectacles->valid()) {
                $spectacleRenderer = new ListSpectacleRenderer($spectacles);
                $output .= "<div style='margin-bottom: 20px;'>";
                $output .= "<h3>Soiree: {$soiree->nomLieu}</h3>";
                $output .= "<p><strong>Adresse:</strong> {$soiree->adresseLieu}</p>";
                $output .= $spectacleRenderer->render(Renderer::LONG);
                $output .= "</div>";
            } else {
                $output .= "<p>Aucun spectacle pour la soiree a {$soiree->nomLieu}.</p>";
            }
        }
        $output .= "</ul>";
        return $output;
    }
}