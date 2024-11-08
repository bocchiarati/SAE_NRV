<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\repository\NrvRepository;

class ActionCreateSpectacle extends Action
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
    <p>Selectoinner une soirée</p>
    <select id="soiree" name="soiree">
        <option value="" disabled selected>Choisir une soiree</option>
        $options
    </select>
    <p>Selectionner un lieu</p>
    <select id="style" name="style">
        <option value="" disabled selected>Choisir un lieu</option>
        $options
    </select>
    
    <input type="text" id="new-location" name="new-location" placeholder="Nouveau lieu">
    <input type="text" id="address" name="address" placeholder="Adresse"">

    <button type="submit">Créer</button>
</form>
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

        $pdo = NrvRepository::getInstance();
        if($_POST['location'] === "Autre"){
            $pdo->saveSoiree($_POST['date'], null, $_POST['new-location'], $_POST['address']);
            $message =  "<p>Soirée et Lieu créée avec succes</p>";
        }
        else{
            $pdo->saveSoiree($_POST['date'], $_POST['location'],null, null);
            $message = "Soirée créée avec succes";
        }
        return $message;
    }
}