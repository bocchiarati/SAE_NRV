<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\repository\NrvRepository;

class ActionFiltre extends Action {

    private string $output = "";

    function executeGet(): string
    {
        $affichage = "";
        $pdo = NrvRepository::getInstance();

        if(isset($_GET['filter']) && isset($_GET['id'])){

            if($_GET['filter'] === "style") {
                //FILTRE STYLE

                $spectacles = $pdo->getSpectaclesByStyle($_GET['id']);

                $render = new ListSpectacleRenderer($spectacles);
                $this->output = $render->render(Renderer::LONG);
            }

            if($_GET['filter'] === "location") {
                //FILTRE LOCATION
                $selectedLocation = $_GET['id'];

                $filteredSoirees = $pdo->getSoireeByLocation($selectedLocation);

                if (empty($filteredSoirees)) {
                    return "<p>Aucun spectacle n'est prevue pour ce lieu.</p>";
                }

                $this->output = "<h2>Spectacles pour le lieu selectionne</h2><ul>";
                $this->output .= "";
                foreach ($filteredSoirees as $soiree) {
                    $spectacles = $pdo->getSpectacleBySoiree($soiree->id);
                    if (count($spectacles->spectacles) >= 1) {
                        $soireeRenderer = new SoireeRenderer($soiree);
                        $this->output .= $soireeRenderer->render(Renderer::LONG);
                    } else {
                        $this->output .= "<p>Aucun spectacle pour la soiree a {$soiree->nomLieu}.</p>";
                    }
                }
                $this->output .= "</div>";
            }
        }else{
            if(!isset($_POST['date'])) {
                $spectacles = $pdo->findAllSpectacle();
                $this->output = "";
                if (count($spectacles->spectacles) > 0) {
                    $renderer = new ListSpectacleRenderer($spectacles);
                    $this->output .= $renderer->render(Renderer::COMPACT);
                } else {
                    $this->output = "<p>Aucun spectacle programmé</p>";
                }
            }
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
            <div class="filtres d-flex justify-content-around w-100">
                <div class="d-flex flex-column">
                    <h2>Filtrer par style</h2>
                    <div class="dropdown">
                        <button class="dropdown-button">Sélectionner un style</button>
                        <div class="dropdown-content">
                            $choix
                        </div>
                    </div>
                </div>
                
                <div class="d-flex flex-column">
                    <h2>Filtrer par date</h2>
                    <form method="post" action="?action=filtre" id="filtre">
                        <input type="date" id="date" name="date" required>
                        <button type="submit">Filtrer</button>
                    </form>
                </div>
                
                <div class="d-flex flex-column">
                    <h2>Filtrer par lieu</h2>
                    <div class="dropdown">
                        <button class="dropdown-button">Sélectionner un lieu</button>
                        <div class="dropdown-content">
                            $options
                        </div>
                    </div>
                </div>
            </div>
            <div class="affichage w-100">
            {$this->output}
            </div>
        HTML;

        return $affichage;
    }

    function executePost(): string
    {
        $date = $_POST['date'];

        // Vérifie que le format est bien "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $selectedDate = filter_var($date, FILTER_SANITIZE_STRING);
        } else {
            $this->output = "<p>Erreur avec la date envoyée</p>";
            return $this->executeGet();
        }

        $repository = NrvRepository::getInstance();
        $filteredSoirees = $repository->getSoireeByDate($selectedDate);

        if (empty($filteredSoirees)) {
            $this->output = "<p>Aucun spectacle n'est prevu pour la date : $selectedDate.</p>";
            return $this->executeGet();
        }

        $this->output = "<h2>Spectacles pour la date : $selectedDate</h2><ul>";
        foreach ($filteredSoirees as $soiree) {
            $renderer = new SoireeRenderer($soiree);
            $this->output .= $renderer->render(Renderer::COMPACT);
        }
        $this->output .= "</ul>";
        return $this->executeGet();
    }

}