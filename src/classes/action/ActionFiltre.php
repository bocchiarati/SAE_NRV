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
                $spectacles = $pdo->SpectaclesByStyle($_GET['id']);

                $render = new ListSpectacleRenderer($spectacles);
                $output = $render->render(Renderer::LONG);
            }

            if($_GET['filter'] === "location") {
                //FILTRE LOCATION
                $selectedLocation = $_GET['id'];

                $repository = NrvRepository::getInstance();
                $filteredSoirees = $repository->SoireeByLocation($selectedLocation);

                if (empty($filteredSoirees)) {
                    return "<p>Aucun spectacle n'est prevue pour ce lieu.</p>";
                }

                $output = "<h2>Spectacles pour le lieu selectionne</h2><ul>";
                foreach ($filteredSoirees as $soiree) {
                    $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());

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
                $output .= "</ul>";
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
            <style>
            .dropdown {
                position: relative;
                display: inline-block;
            }
            .dropdown-button {
                background-color: #FF0000;
                color: white;
                padding: 10px;
                font-size: 16px;
                border: none;
                cursor: pointer;
            }
            .dropdown-content {
                display: none;
                position: absolute;
                background-color: #f9f9f9;
                min-width: 160px;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                z-index: 1;
            }
            .dropdown-content a {
                color: black;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
            }
            .dropdown-content a:hover {
                background-color: #f1f1f1;
            }
            .dropdown:hover .dropdown-content {
                display: block;
            }
            .dropdown:hover .dropdown-button {
                background-color: #ffcccc;
                color: red;
            }
            /* Container for the form */
            form {
                display: flex;
                flex-direction: column;
                font-family: Arial, sans-serif;
                width: 170px;
            }
        
            /* Input date styling */
            #date {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border-radius: 4px;
                border: 1px solid #ccc;
                font-size: 14px;
                box-sizing: border-box;
            }
        
            /* Button styling */
            button {
                background-color: #ff0000;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s ease;
            }
        
            /* Button hover effect */
            button:hover {
                background-color: #ff0000;
            }
        
            /* Focus effect on input */
            #date:focus {
                border-color: #ff0000;
                outline: none;
            }
            .container {
                display: flex;
                flex-direction: column;
            }
        </style>
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
                <form method="post" action="?action=filtre">
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
        $filteredSoirees = $repository->SoireeByDate($selectedDate);

        if (empty($filteredSoirees)) {
            return "<p>Aucune spectacle n'est prevue pour la date : $selectedDate.</p>";
        }

        $output = "<h2>Spectacles pour la date : $selectedDate</h2><ul>";
        foreach ($filteredSoirees as $soiree) {

            $spectacles = $repository->programmeSpectacleBySoiree($soiree->getID());

            $renderer = new ListSpectacleRenderer($spectacles);

            $output.= $renderer->render(Renderer::LONG);
        }
        $output .= "</ul>";
        return $output;
    }

}