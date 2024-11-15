<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\repository\NrvRepository;

class ActionCreateSoiree extends Action
{

    function executeGet(): string {

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
        $locations = $repository->getAllLieu();

        // creation des options pour le select du lieu
        $options = '';
        foreach ($locations as $lieuID => $nom) {
            $options .= "<option value='{$lieuID}'>{$nom}</option>";
        }
        return <<< END
    
<h1 style="text-align: center; font-size:60px">Creation D'une soirée</h1>
<form method="post" action="?action=createSoiree">
    <h3>Insérer une date de début</h3>
    <input type="date" id="date" name="date" required>
    
    <h3>Horaire de début</h3>
    <input type="time" id="time" name="time" required>


    <h3>Sélectionner un lieu</h3>
    <select id="location" name="location">
        <option value="" disabled selected>Choisir un lieu</option>
        $options
        <option value="Autre">Ou insérer un nouveau lieu</option>
    </select>

    <input type="text" id="new-location" name="new-location" placeholder="Nouveau lieu" style="display: none;">
    <input type="text" id="address" name="address" placeholder="Adresse" style="display: none;">
    <input type="number" id="places" name="places" placeholder="Capacité" style="display:None;">
    
    <input type="number" id="tarif" name="tarif" placeholder="Tarif" required>
    <input type="text" id="nom" name="nom" placeholder="Nom" required>
    <input type="text" id="thematique" name="thematique" placeholder="Thematique" required>
    
    <button type="submit">Créer</button>
</form>
<script>
    // Script to show/hide the new location and address inputs based on the selection
    document.getElementById('location').addEventListener('change', function() {
        var newLocationInput = document.getElementById('new-location');
        var addressInput = document.getElementById('address');
        var placeInput = document.getElementById('places');
        
        if (this.value === 'Autre') {
            newLocationInput.style.display = 'block';
            addressInput.style.display = 'block';
            placeInput.style.display = 'block';
        } else {
            newLocationInput.style.display = 'none';
            addressInput.style.display = 'none';
            placeInput.style.display = 'none';
        }
    });
</script>
END;

    }

    /**
     * @throws RepoException
     */
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


        // Vérifie que le format est bien "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'])) {
            $_POST['date'] = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
        } else {
            return "Date invalide (Format attendu : YYY-MM-DD, Date reçu : ".$_POST['date'].")";
        }

        // Valide le temps au format "HH:MM:SS"
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $_POST['time']) || preg_match('/^\d{2}:\d{2}$/', $_POST['time'])) {
            $_POST['time'] = filter_var($_POST['time'], FILTER_SANITIZE_STRING);
        } else {
            return "Heure invalide (Format attendu : HH:MM:SS, heure reçu : ".$_POST['time'].")";
        }

        //TODO vérifier tous les parametres envoyé par le client

        $pdo = NrvRepository::getInstance();
        if($_POST['location'] === "Autre"){
            $pdo->saveSoiree($_POST['date'], $_POST['time'], null, $_POST['new-location'], $_POST['address'], $_POST['places'], $_POST['tarif'], $_POST['nom'], $_POST['thematique']);
            $message =  "<p>Soirée et Lieu créée avec succes</p>";
        }
        else{
            $pdo->saveSoiree($_POST['date'], $_POST['time'], $_POST['location'],null, null, null, $_POST['tarif'], $_POST['nom'], $_POST['thematique']);
            $message = "Soirée créée avec succes";
        }
        return $message;
    }
}