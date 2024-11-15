<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\render\Renderer;
use iutnc\nrv\repository\NrvRepository;

class ActionEditSpectacle extends Action
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

        $styles = $repository->getAllStyle();

        // creation des options pour le select du lieu
        $optionsStyles = '';
        foreach ($styles as $styleID => $nom) {
            $optionsStyles .= "<option value='{$styleID}'>{$nom}</option>";
        }

        $spectacles = $repository->getAllSpectacle();
        $optionsSpectacle = '';
        foreach ($spectacles as $spectacleID => [$titre, $groupe]) {
            $optionsSpectacle .= "<option value='{$spectacleID}'>{$titre} - {$groupe}</option>";
        }
        return <<< END
        <h1>Modification d'une spectacle :</h1>
        <form method="post" style="padding-bottom: 300px" enctype="multipart/form-data">
            <p>Spectacle modifier</p>
            <select name="spectacle">
                <option value="" disabled selected>Choisir un spectacle</option>
                $optionsSpectacle
            </select>
            <p>Modification sur : </p>
            <select name="modification" id="modification">
                <option value="" disabled selected>Choisir une modification</option>
                <option value="titre">Titre</option>
                <option value="groupe">Groupe</option>
                <option value="duree">Duree</option>
                <option value="description">Description</option>
                <option value="style">Style</option>
                <option value="extrait">Extrait</option>
                <option value="image">Image</option>
            </select>
            
            <input type="text" class='new' name="newtitre" id="newtitre" style="display: none;" placeholder="Nouvelle Thematique">
            <input type="number" class='new' name="newduree" id="newduree" style="display: none;" placeholder="Nouvelle Duree">
            <input type="text" class='new' name="newgroupe" id="newgroupe" style="display: none;" placeholder="Nouveau Groupe">
            <input type="text" class='new' name="newdescription" id="newdescription" style="display: none;" placeholder="Nouvelle Description">
            <select id="newstyle" class='new' name="newstyle" style="display: none;">
                <option value="" disabled selected>Choisir un style</option>
                    $optionsStyles
                <option value="Autre">Ou insérer un nouveau lieu</option>
            </select>
            
            <input type="text" class='new' name="nomStyle" id="nomStyle" style="display: none;" placeholder="Nom Nouveau Style">
            <select id="newextrait" class='new' name="newextrait" style="display: none;">
                <option value="modefichier">Fichier</option>
                <option value="modelien">Ou Lien</option>
            </select>
            <input type="file" class='new' id="newfichier" name="newfichier" style="display:none">
            <input type="text" class='new' id="newlien" name="newlien" style="display:none" placeholder="lien (youtube ou soundcloud)x">
            
            <input type="file" class='new' id="newimage" name="newimage" style="display: none;">  
            
            <button type="submit">Enregistrer</button>
        </form> 

        <script>
            let extraitInput = document.getElementById('newfichier');
            let lienInput = document.getElementById('newlien');
            
            let nomStyleInput = document.getElementById('nomStyle');
            // Script to show/hide the new location and address inputs based on the selection
            document.getElementById('modification').addEventListener('change', function() {
                let newInput = document.getElementsByClassName('new');
                for (let i = 0; i < newInput.length; i++) {
                    newInput[i].style.display = "none";
                }
                document.getElementById('new'+this.value).style.display = "block";
                
                if(this.value !== 'style'){
                    nomStyleInput.style.display = 'none';
                }
                
                if(this.value !== 'extrait'){
                    extraitInput.style.display = 'none';
                    lienInput.style.display = 'none';
                }
                else
                    if(document.getElementById('newextrait').value === 'modefichier'){
                        extraitInput.style.display = 'block';
                    }
                    else
                        lienInput.style.display = 'block';
                        
            })
            
            document.getElementById('newextrait').addEventListener('change', function() {
                if (this.value === 'modefichier'){
                   extraitInput.style.display = 'block';     
                   extraitInput.required = true;
                   
                   lienInput.style.display = 'none';
                   lienInput.required = false;
                }
                else if (this.value === 'modelien'){
                   extraitInput.style.display = 'none';
                   extraitInput.required = false;
                   
                   lienInput.style.display = 'block';
                   lienInput.required = true;
                }
            })
            
            document.getElementById('nouveauStyle').addEventListener('change', function() {                
                if (this.value === 'Autre') {
                    nomStyleInput.style.display = 'block';
                } else {
                    nomStyleInput.style.display = 'none';
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
            case 'titre':
                return $pdo->modifierTitre($_POST['spectacle'], $_POST['newtitre']);
            case 'groupe':
                return $pdo->modifierGroupe($_POST['spectacle'], $_POST['newgroupe']);
            case 'duree':
                return $pdo->modifierDuree($_POST['spectacle'], $_POST['newduree']);
            case 'description':
                return $pdo->modifierDescription($_POST['spectacle'], $_POST['newdescription']);
            case 'style':
                if($_POST['nouveauStyle'] != 'Autre')
                    return $pdo->modifierStyle($_POST['spectacle'], $_POST['newstyle'], null);
                else
                    return $pdo->modifierStyle($_POST['spectacle'], null, $_POST['nomStyle']);
            case 'extrait':
                if($_POST['newextrait'] === "modefichier"){
                    $upload_dir_extrait = Renderer::REPERTOIRE_EXTRAITS;
                    $extraitname = uniqid();
                    if (isset($_FILES['newfichier']) && $_FILES['newfichier']['error'] === UPLOAD_ERR_OK) {
                        $tmp = $_FILES['newfichier']['tmp_name'];
                        if ($_FILES['newfichier']['type'] === 'video/mp4') {
                            $dest = $upload_dir_extrait . $extraitname . '.mp4';
                            move_uploaded_file($tmp, $dest);
                            $extrait = $extraitname . '.mp4';
                        } else if ($_FILES['newfichier']['type'] === 'audio/mpeg') {
                            $dest = $upload_dir_extrait . $extraitname . '.mp3';
                            move_uploaded_file($tmp, $dest);
                            $extrait = $extraitname . '.mp3';
                        } else {
                            return "extrait invalide";
                        }
                    } else {
                        return "extrait non trouvée ou erreur de téléchargement";
                    }
                }
                else
                    $extrait = $_POST['newlien'];
                return $pdo->modifierExtrait($_POST['spectacle'],$extrait);
            case 'image':
                $upload_dir_image = Renderer::REPERTOIRE_IMAGE;
                $nouvelExtrait = uniqid();

                if (isset($_FILES['newimage']) && $_FILES['newimage']['error'] === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['newimage']['tmp_name'];
                    if ($_FILES['newimage']['type'] === 'image/jpeg') {
                        $dest = $upload_dir_image . $nouvelExtrait . '.jpeg';
                        move_uploaded_file($tmp, $dest);
                        $nouvelExtrait = $nouvelExtrait . '.jpeg';
                    } else {
                        return "image invalide";
                    }
                } else {
                    return "image non trouvée ou erreur de téléchargement";
                }
                return $pdo->modifierImage($_POST['spectacle'], $nouvelExtrait);
            default :
                return "<p>aucune modification</p>";
        }
    }
}