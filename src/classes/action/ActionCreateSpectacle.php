<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\render\Renderer;
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

        $upload_dir_extrait = Renderer::REPERTOIRE_EXTRAITS;
        $upload_dir_image = Renderer::REPERTOIRE_IMAGE;

        $filename = uniqid();
        $tmp = $_FILES['inputfile']['tmp_name'];
        if (($_FILES['inputfile']['error'] === UPLOAD_ERR_OK) && ($_FILES['inputfile']['type'] === 'audio/mpeg') ) {

            $dest = $upload_dir_extrait.$filename.'.mp3';
            move_uploaded_file($tmp, $dest );
            $fichier = $filename.'.mp3';

        } else {
            return "fichier invalide";
        }


        $repository = NrvRepository::getInstance();
        $styles = $repository->getAllStyle();
        $soirees = $repository->getAllSoiree();

        // creation des options pour le select du lieu
        $optionsStyle = '';
        foreach ($styles as $styleID => $nom) {
            $optionsStyle .= "<option value='{$styleID}'>{$nom}</option>";
        }

        $optionsSoiree = '';
        foreach ($soirees as $soireeID => [$nom, $thematique, $nomLieu]) {
            $optionsSoiree .= "<option value='{$soireeID}'>{$nom} - {$thematique} - {$nomLieu}</option>";
        }
        return <<< END
    
    <h1 style="text-align: center; font-size:60px">Creation D'un Spectacle</h1>
    <form method="post" action="?action=createSpectacle">
    <p>Selectoinner une soirée</p>
    <select id="soiree" name="soiree">
        <option value="" disabled selected>Choisir une soiree</option>
        $optionsSoiree
    </select>
    <p>Selectionner un Titre</p>
    <input type="text" id="titre" name="titre" placeholder="Titre">
    <input type="text" id="groupe" name="groupe" placeholder="Groupe">
    <input type="text" id="duree" name="duree" placeholder="Duree">
    <input type="text" id="description" name="description" placeholder="Description">
    <p>Choisir un extrait audio ou video</p>
    <input type="file" id="extrait" name="extrait" required>
    <p>Choisir une image</p>
    <input type="file" id="image" name="image" required>    
    <select id="style" name="style">
        <option value="" disabled selected>Choisir un style</option>
        $optionsStyle
        <option value="Autre">Nouveau style</option>
    </select>
    
    <input type="text" id="nomStyle" name="nomStyle" placeholder="Nom du nouveau Style" style="display: none;">

    <button type="submit">Créer</button>
</form>

<script>
    // Script to show/hide the new location and address inputs based on the selection
    document.getElementById('style').addEventListener('change', function() {
        var nomStyleInput = document.getElementById('nomStyle');
        
        if (this.value === 'Autre') {
            nomStyleInput.style.display = 'block';
        } else {
            nomStyleInput.style.display = 'none';
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

        $pdo = NrvRepository::getInstance();
        if($_POST['style'] === "Autre"){
            $pdo->saveSpectacle($_POST['titre'], $_POST['groupe'], $_POST['duree'], $_POST['description'], $_POST['extrait'], $_POST['image'], null, $_POST['nomStyle']);
            $message =  "<p>Soirée et Lieu créée avec succes</p>";
        }
        else{
            $pdo->saveSpectacle($_POST['titre'], $_POST['groupe'], $_POST['duree'], $_POST['description'], $_POST['extrait'], $_POST['image'], $_POST['style'], null);
            $message = "Soirée créée avec succes";
        }
        return $message;
    }
}