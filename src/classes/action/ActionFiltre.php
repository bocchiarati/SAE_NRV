<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\repository\NrvRepository;

/**
 * Classe ActionFiltre qui permet de filtrer les spectacles
 */
class ActionFiltre extends Action {

    private string $output = "";
    private $pdo;

    public function __construct() {
        parent::__construct();
        $this->pdo = NrvRepository::getInstance();
    }

    // Execute l'action GET, applique les filtres si necessaires
    public function executeGet(): string {
        if (isset($_GET['filter']) && isset($_GET['id'])) {
            $this->handleFilters($_GET['filter'], $_GET['id']);
        } else {
            $this->showAllSpectacles();
        }

        return $this->buildHTML();
    }

    // gestion des filtres pour les spect
    private function handleFilters($filter, $id): void
    {
        // Afficher tous les spectacles si 'Tous' est selectionne
        if ($id === 'all') {
            switch ($filter) {
                case 'style':
                case 'location':
                case 'date':
                    $this->showAllSpectacles();
                    break;
            }
        }
        // Sinon, filtrer les spectacles selon le filtre selectionne
        else{
            switch ($filter) {
                case 'style':
                    $spectacles = $this->pdo->getSpectaclesByStyle($id);
                    $this->output = (new ListSpectacleRenderer($spectacles))->render(Renderer::LONG);
                    break;
                case 'location':
                    $this->filterByLocation($id);
                    break;
                case 'date':
                    $this->filterByDate($id);
                    break;
            }
        }

    }

    // Afficher tous les spectacles disponibles
    private function showAllSpectacles() : void
    {
        $spectacles = $this->pdo->findAllSpectacle();
        $this->output = $spectacles ?
            (new ListSpectacleRenderer($spectacles))->render(Renderer::COMPACT) :
            "<p>Aucun spectacle programmé</p>";
    }

    // Filtrer les spectacles par lieu
    private function filterByLocation($locationId): void
    {
        $filteredSoirees = $this->pdo->getSoireeByLocation($locationId);
        if (empty($filteredSoirees)) {
            $this->output = "<p>Aucun spectacle n'est prévu pour ce lieu.</p>";
        } else {
            foreach ($filteredSoirees as $soiree) {
                $this->output .= (new SoireeRenderer($soiree))->render(Renderer::LONG);
            }
        }
    }

    // Filtrer les spectacles par date
    private function filterByDate(string $date) : void {
        $filteredSoirees = $this->pdo->getSoireeByDate($date);
        if (empty($filteredSoirees)) {
            $this->output = "<p>Aucun spectacle n'est prévu pour cette date.</p>";
        } else {
            foreach ($filteredSoirees as $soiree) {
                $this->output .= (new SoireeRenderer($soiree))->render(Renderer::LONG);
            }
        }
    }

    // Construit HTML pour l'interface utilisateur
    private function buildHTML(): string {
        $styleOptions = $this->buildDropdownLinks($this->pdo->getAllStyle(), 'style');
        $locationOptions = $this->buildDropdownLinks($this->pdo->getAllLieu(), 'location');
        $dateOptions = $this->buildDropdownLinks($this->pdo->getAllDate(), 'date');

        // fuction toggleTab pour basculer entre les buttons de filtre (style, location, date), ecrit en JS en Dispatcher
        return <<<HTML
        <div class="filter-container">
            <div class="tabs">
                <button onclick="toggleTab('style')">Styles</button>
                <button onclick="toggleTab('location')">Lieux</button>
                <button onclick="toggleTab('date')">Jours</button>
            </div>
            <div class="tab-content" id="style">{$styleOptions}</div>
            <div class="tab-content" id="location" style="display:none;">{$locationOptions}</div>
            <div class="tab-content" id="date" style="display:none;">{$dateOptions}</div>
        </div>
        <div class="affichage">{$this->output}</div>
        HTML;
    }

    // Construit les liens pour chaque categorie de filtre
    private function buildDropdownLinks(array $items, string $filterType): string {
        $allLabels = [
            'style' => 'Tous les styles',
            'location' => 'Tous les lieux',
            'date' => 'Tous les jours'
        ];

        $links = "<div class='dropdown-links-container d-flex flex-wrap p-2'>";
        $links .= "<a href='?action=filtre&filter=$filterType&id=all'>{$allLabels[$filterType]}</a>";

        foreach ($items as $id => $name) {
            $links .= "<a href='?action=filtre&filter=$filterType&id=$id'>$name</a>";
        }
        $links .= "</div>";
        return $links;
    }

    // Execute l'action POST, applique le filtre de date
    public function executePost(): string {
        $date = $_POST['date'] ?? null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->output = "<p>Erreur avec la date envoyée</p>";
        } else {
            $this->filterByDate($date);
        }
        return $this->executeGet();
    }

}