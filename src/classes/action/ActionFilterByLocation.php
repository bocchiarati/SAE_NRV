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

        if(isset($_GET['id'])){
            $selectedLocation = $_POST['id'];

            $repository = NrvRepository::getInstance();
            $filteredSoirees = $repository->SoireeByLocation($selectedLocation);

            if (empty($filteredSoirees)) {
                return "<p>Aucun spectacle n'est prevue pour ce lieu.</p>";
            }

            $output = "<h2>Spectacles pour le lieu selectionne</h2><ul>";
            foreach ($filteredSoirees as $soiree) {
                $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());

                if (count($spectacles->spectacles)>=1) {
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
        }else {

            $repository = NrvRepository::getInstance();
            $locations = $repository->getAllLieu();

            // creation des options pour le select du lieu
            $options = '';
            foreach ($locations as $lieuID => $nom) {
                $options .= "<a href='?action=filterByLocation&id={$lieuID}'>{$nom}</a>";
            }

            return <<<HTML
            <h2>Filtrer par lieu</h2>
            <div class="options">
                $options
            </div>
            HTML;
        }
    }

    // affiche les spectacles avec details de la soiree pour le lieu selectionne
    function executePost(): string
    {
        return "";
    }
}