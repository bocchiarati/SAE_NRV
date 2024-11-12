<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class ActionEditSoiree extends Action
{

    function executeGet(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsOrga()){
            return "Vous n'avez pas le droit d'être ici";
        }

        $repository = NrvRepository::getInstance();
        $soirees = $repository->getAllSoiree();
        $optionsSoiree = '';
        foreach ($soirees as $soireeID => [$nom, $thematique, $nomLieu]) {
            $optionsSoiree .= "<option value='{$soireeID}'>{$soireeID} - {$nom} - {$thematique} - {$nomLieu}</option>";
        }

        $locations = $repository->getAllLieu();

        // creation des options pour le select du lieu
        $options = '';
        foreach ($locations as $lieuID => $nom) {
            $options .= "<option value='{$lieuID}'>{$nom}</option>";
        }

        $spectacles = $repository->getAllSpectacle();
        $optionsSpectacle = '';
        foreach ($spectacles as $spectacleID => [$titre, $groupe]) {
            $optionsSpectacle .= "<option value='{$spectacleID}'>{$titre} - {$groupe}</option>";
        }
        return <<< END
        <h1>Modification d'une soiree :</h1>
        <form method="post" style="padding-bottom: 300px">
            <p>Soirée modifier</p>
            <select name="soiree">
                <option value="" disabled selected>Choisir une soiree</option>
                $optionsSoiree
            </select>
            <p>Modification sur : </p>
            <select name="modification" id="modification">
                <option value="" disabled selected>Choisir une modification</option>
                <option value="nom">Nom</option>
                <option value="thematique">Thematique</option>
                <option value="tarif">Tarif</option>
                <option value="date">Date</option>
                <option value="lieu">Lieu</option>
                <option value="addSpectacle">Ajouter un Spectacle</option>
            </select>
            
            <input type="text" name="nouvelleThematique" id="nouvelleThematique" style="display: none;" placeholder="Nouvelle Thematique">
            <input type="number" name="nouveauTarif" id="nouveauTarif" style="display: none;" placeholder="Nouveau Tarif">
            <input type="date" name="nouvelleDate" id="nouvelleDate" style="display: none;">
            <input type="text" name="nouveauNom" id="nouveauNom" style="display: none;" placeholder="Nouveau Nom">
            <select id="nouveauLieu" name="nouveauLieu" style="display: none;">
                <option value="" disabled selected>Choisir un lieu</option>
                $options
                <option value="Autre">Ou insérer un nouveau lieu</option>
            </select>
            <select id="nouveauSpectacle" name="nouveauSpectacle" style="display: none;">
                <option value="" disabled selected>Choisir un spectacle</option>
                $optionsSpectacle
            </select>
            <input type="text" name="new-location" id="new-location" style="display: none;" placeholder="Nom Nouveau Lieu">
            <input type="text" name="address" id="address" style="display: none;" placeholder="Adresse Nouveau Lieu">
            <button type="submit">Enregistrer</button>
        </form> 

        <script>
            // Script to show/hide the new location and address inputs based on the selection
            document.getElementById('modification').addEventListener('change', function() {
                var nomInput = document.getElementById('nouveauNom');
                var thematiqueInput = document.getElementById('nouvelleThematique');
                var tarifInput = document.getElementById('nouveauTarif');
                var dateInput = document.getElementById('nouvelleDate');
                var lieuInput = document.getElementById('nouveauLieu');
                var newLocationInput = document.getElementById('new-location');
                var addressInput = document.getElementById('address');
                var spectacleInput = document.getElementById('nouveauSpectacle');
                
                nomInput.style.display = 'none';
                thematiqueInput.style.display = 'none';
                tarifInput.style.display = 'none';
                dateInput.style.display = 'none';
                lieuInput.style.display = 'none';
                spectacleInput.style.display = 'none';
                
                
                switch(this.value){
                    case 'nom':
                        nomInput.style.display = 'block';
                        break;
                    case 'thematique':
                        thematiqueInput.style.display = 'block';
                        break;
                    case 'tarif':
                        tarifInput.style.display = 'block';
                        break;
                    case 'date':
                        dateInput.style.display = 'block';
                        break;
                    case 'lieu':
                        lieuInput.style.display = 'block';
                        break;
                    case 'addSpectacle':
                        spectacleInput.style.display = 'block';
                        break;
                }
                
                if(this.value !== 'lieu'){
                    newLocationInput.style.display = 'none';
                    addressInput.style.display = 'none';
                }
            });
            
            document.getElementById('nouveauLieu').addEventListener('change', function() {
                var newLocationInput = document.getElementById('new-location');
                var addressInput = document.getElementById('address');
                
                if (this.value === 'Autre') {
                    newLocationInput.style.display = 'block';
                    addressInput.style.display = 'block';
                } else {
                    newLocationInput.style.display = 'none';
                    addressInput.style.display = 'none';
                }
            });
        </script>
END;

    }

    function executePost(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $droit = new Authz($user);
        if(!$droit->checkIsOrga()){
            return "Vous n'avez pas le droit d'être ici";
        }
        $pdo = NrvRepository::getInstance();

        switch($_POST['modification']){
            case 'nom':
                return $pdo->modifierNom($_POST['soiree'], $_POST['nouveauNom']);
            case 'thematique':
                return $pdo->modifierThematique($_POST['soiree'], $_POST['nouvelleThematique']);
            case 'tarif':
                return $pdo->modifierTarif($_POST['soiree'], $_POST['nouveauTarif']);
            case 'date':
                return $pdo->modifierDate($_POST['soiree'], $_POST['nouvelleDate']);
            case 'lieu':
                if($_POST['nouveauLieu'] != 'Autre')
                    return $pdo->modifierLieuSoiree($_POST['soiree'], $_POST['nouveauLieu'], null, null);
                else
                    return $pdo->modifierLieuSoiree($_POST['soiree'], null, $_POST['new-location'], $_POST['address']);
            case 'addSpectacle':
                return $pdo->saveSoireeToSpectacle($_POST['nouveauSpectacle'], $_POST['soiree']);
            default :
                return "<p>aucune modification</p>";
        }
    }
}