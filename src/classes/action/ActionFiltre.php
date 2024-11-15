<?php

namespace iutnc\nrv\action;

use iutnc\nrv\programme\ListSpectacle;
use iutnc\nrv\render\ListSpectacleRenderer;
use iutnc\nrv\render\Renderer;
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
                case 'pref':
                    if(isset($_SESSION['pref'])){
                        $spectacles = unserialize($_SESSION['pref']);
                    }else{
                        $spectacles = new ListSpectacle();
                    }
                    $this->output = (new ListSpectacleRenderer($spectacles))->render(Renderer::LONG);
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
                    $spectacles = $this->pdo->getSpectaclesByLocation($id);
                    $this->output = (new ListSpectacleRenderer($spectacles))->render(Renderer::LONG);
                    break;
                case 'date':
                    $spectacles = $this->pdo->getSpectaclesByDate($id);
                    $this->output = (new ListSpectacleRenderer($spectacles))->render(Renderer::LONG);
                    break;
                case 'pref':
                    if(isset($_SESSION['pref'])){
                        $spectacles = unserialize($_SESSION['pref']);
                    }else{
                        $spectacles = new ListSpectacle();
                    }
                    $this->output = (new ListSpectacleRenderer($spectacles))->render(Renderer::LONG);
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

    // Construit HTML pour l'interface utilisateur
    private function buildHTML(): string {
        $styleOptions = $this->buildDropdownLinks($this->pdo->getAllStyle(), 'style');
        $locationOptions = $this->buildDropdownLinks($this->pdo->getAllLieu(), 'location');
        $dateOptions = $this->buildDropdownLinks($this->pdo->getAllDate(), 'date');

        $prefOptions = <<<END
        <div class='dropdown-links-container d-flex flex-wrap p-2'>
            <a href='?action=filtre&filter=pref&id=all'>Toutes Vos Préférences</a>
            <a href='?action=delpref'>Supprimer Vos Préférences</a>
            <a href='?action=saveprefrepo'>Sauvegarder vos préférences sur votre compte (être connecté)</a>
        </div>
        END;

        if(isset($_GET['filter'])){
            switch ($_GET['filter']){
                case 'location':
                    $displayLieu = 'block';
                    break;
                case 'date':
                    $displayDate = 'block';
                    break;
                case 'pref':
                    $displayPref = 'block';
                    break;
                default:
                    $displayStyle = 'block';
                    break;
            }
        }
        $activeStyle = (isset($_GET['filter']) && $_GET['filter'] == 'style') ? 'active-filter' : '';
        $activeLocation = (isset($_GET['filter']) && $_GET['filter'] == 'location') ? 'active-filter' : '';
        $activeDate = (isset($_GET['filter']) && $_GET['filter'] == 'date') ? 'active-filter' : '';
        $activePref = (isset($_GET['filter']) && $_GET['filter'] == 'pref') ? 'active-filter' : '';

        // fonction toggleTab pour basculer entre les buttons de filtre (style, location, date), ecrit en JS en Dispatcher
        return <<<HTML
        <div class="filter-container align-self-start ms-4 mt-3">
            <div class="tabs">
                <button onclick="toggleTab('style')" class="{$activeStyle}">Styles</button>
                <button onclick="toggleTab('location')" class="{$activeLocation}">Lieux</button>
                <button onclick="toggleTab('date')" class="{$activeDate}">Jours</button>
                <button onclick="toggleTab('pref')" class="{$activePref}">Préférences</button>
            </div>
            <div class="tab-content" id="style" style="display:{$this->getDisplay('style')}">{$styleOptions}</div>
            <div class="tab-content" id="location" style="display:{$this->getDisplay('location')};">{$locationOptions}</div>
            <div class="tab-content" id="date" style="display:{$this->getDisplay('date')};">{$dateOptions}</div>
            <div class="tab-content" id="pref" style="display:{$this->getDisplay('pref')};">{$prefOptions}</div>
        </div>
        <div class="affichage">{$this->output}</div>
        HTML;
    }

    private function getDisplay($filterType): string {
        return (isset($_GET['filter']) && $_GET['filter'] == $filterType) ? 'block' : 'none';
    }

    // Construit les liens pour chaque categorie de filtre
    private function buildDropdownLinks(array $items, string $filterType): string {
        $allLabels = [
            'style' => 'Tous les styles',
            'location' => 'Tous les lieux',
            'date' => 'Tous les jours'
        ];

        // obtenir le filtre et l'id actuels
        $currentFilter = $_GET['filter'] ?? '';
        $currentId = $_GET['id'] ?? '';

        $links = "<div class='dropdown-links-container d-flex flex-wrap p-2'>";
        $allActive = ($currentFilter == $filterType && $currentId == 'all') ? 'class="active"' : '';
        $links .= "<a href='?action=filtre&filter=$filterType&id=all' $allActive>{$allLabels[$filterType]}</a>";

        if($filterType === 'date'){
            foreach ($items as $date) {
                $dateAffiche = new \DateTime($date);
                $dateAfficheFormatted = $dateAffiche->format('d M Y');
                $isActive = ($currentFilter === $filterType && $date === $currentId) ? 'class="active"' : '';
                $links .= "<a href='?action=filtre&filter=$filterType&id=$date' $isActive>$dateAfficheFormatted</a>";
            }
        }else {
            foreach ($items as $id => $name) {
                // verifier si le lien est actif
                $activeClass = ($currentFilter == $filterType && $id == $currentId) ? 'class="active"' : '';
                $links .= "<a href='?action=filtre&filter=$filterType&id=$id' $activeClass>$name</a>";
            }
        }

        $links .= "</div>";
        return $links;
    }

    public function executePost(): string {
        return "nothing to return";
    }

}