<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

class ActionFiltre extends Action {

    function executeGet(): string
    {
        $affichage = "";

        if(isset($_GET['filter']) && isset($_GET['id'])){

            if($_GET['filter'] === "style") {
                //FILTRE STYLE

                $pdo = NrvRepository::getInstance();
                $spectacles = $pdo->getSpectaclesByStyle($_GET['id']);

                $render = new ListSpectacleRenderer($spectacles);
                $output = $render->render(Renderer::LONG);
            }

            if($_GET['filter'] === "location") {
                //FILTRE LOCATION
                $selectedLocation = $_GET['id'];

                $repository = NrvRepository::getInstance();
                $filteredSoirees = $repository->getSoireeByLocation($selectedLocation);

                if (empty($filteredSoirees)) {
                    return "<p>Aucun spectacle n'est prevue pour ce lieu.</p>";
                }

                $output = "<h2>Spectacles pour le lieu selectionne</h2><ul>";
                $output .= "";
                foreach ($filteredSoirees as $soiree) {
                    $spectacles = $repository->getSpectacleBySoiree($soiree->getID());

                    if (count($spectacles->spectacles) >= 1) {
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
                $output .= "</div>";
            }
        } else {
            //FILTRE PAR DEFAUT
            $output = "Filtre par défaut";
        }

        $choix = '';

        $pdo = NrvRepository::getInstance();
        $listStyle = $pdo->getAllStyle();

        foreach ($listStyle as $key => $style) {
            $choix .= '<a href="?action=filtre&filter=style&id=' . $key . '">' . $style . '</a>';
        }

        $repository = NrvRepository::getInstance();
        $locations = $repository->getAllLieu();

        // creation des options pour le select du lieu
        $options = '';
        foreach ($locations as $lieuID => $nom) {
            $options .= "<a href='?action=filtre&filter=location&id={$lieuID}'>{$nom}</a>";
        }

        $affichage .= <<<HTML
        <div class="container">
            <div class="filtres">
                <h2>Filtrer par style</h2>
                <div class="dropdown">
                    <button class="dropdown-button">Sélectionner un style</button>
                    <div class="dropdown-content">
                        $choix
                    </div>
                </div>
                
                <h2>Filtrer par date</h2>
                <form method="post" action="?action=filtre" id="filtre">
                    <input type="date" id="date" name="date" required>
                    <button type="submit">Filtrer</button>
                </form>
                
                <h2>Filtrer par lieu</h2>
                <div class="dropdown">
                    <button class="dropdown-button">Sélectionner un lieu</button>
                    <div class="dropdown-content">
                        $options
                    </div>
                </div>
            </div>
        </div>
         <div class="affichage">
            {$output}
        </div>
        HTML;

        return $affichage;
    }

    function executePost(): string
    {
        $selectedDate = $_POST['date'] ?? null;

        if (!$selectedDate) {
            return "<p>Aucune date selectionnee. Veuillez choisir une date pour filtrer les spectacles.</p>";
        }

        $repository = NrvRepository::getInstance();
        $filteredSoirees = $repository->getSoireeByDate($selectedDate);

        if (empty($filteredSoirees)) {
            return "<p>Aucune spectacle n'est prevue pour la date : $selectedDate.</p>";
        }

        $output = "<h2>Spectacles pour la date : $selectedDate</h2><ul>";
        foreach ($filteredSoirees as $soiree) {

            $spectacles = $repository->getSpectacleBySoiree($soiree->getID());

            $renderer = new ListSpectacleRenderer($spectacles);

            $output.= $renderer->render(Renderer::LONG);
        }
        $output .= "</ul>";
        return $output;
    }

}