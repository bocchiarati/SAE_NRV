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
        <form method="post" style="padding-bottom: 300px">
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
            
            <input type="text" name="nouveauTitre" id="nouveauTitre" style="display: none;" placeholder="Nouvelle Thematique">
            <input type="number" name="nouvelleDuree" id="nouvelleDuree" style="display: none;" placeholder="Nouvelle Duree">
            <input type="text" name="nouveauGroupe" id="nouveauGroupe" style="display: none;" placeholder="Nouveau Groupe">
            <input type="text" name="nouvelleDescription" id="nouvelleDescription" style="display: none;" placeholder="Nouvelle Description">
            <select id="nouveauStyle" name="nouveauStyle" style="display: none;">
                <option value="" disabled selected>Choisir un style</option>
                    $optionsStyles
                <option value="Autre">Ou insérer un nouveau lieu</option>
            </select>
            
            <input type="text" name="nomStyle" id="nomStyle" style="display: none;" placeholder="Nom Nouveau Style">
            <input type="file" id="nouvelExtrait" name="nouvelExtrait" style="display: none;">
            <input type="file" id="nouvelleImage" name="nouvelleImage" style="display: none;">  
            
            <button type="submit">Enregistrer</button>
        </form> 

        <script>
            // Script to show/hide the new location and address inputs based on the selection
            document.getElementById('modification').addEventListener('change', function() {
                var titreInput = document.getElementById('nouveauTitre');
                var groupeInput = document.getElementById('nouveauGroupe');
                var dureeInput = document.getElementById('nouvelleDuree');
                var descriptionInput = document.getElementById('nouvelleDescription');
                var styleInput = document.getElementById('nouveauStyle');
                var extraitInput = document.getElementById('nouvelExtrait');
                var imageInput = document.getElementById('nouvelleImage');
                
                titreInput.style.display = 'none';
                groupeInput.style.display = 'none';
                dureeInput.style.display = 'none';
                descriptionInput.style.display = 'none';
                styleInput.style.display = 'none';
                extraitInput.style.display = 'none';
                imageInput.style.display = 'none';
                
                
                switch(this.value){
                    case 'titre':
                        titreInput.style.display = 'block';
                        break;
                    case 'groupe':
                        groupeInput.style.display = 'block';
                        break;
                    case 'duree':
                        dureeInput.style.display = 'block';
                        break;
                    case 'description':
                        descriptionInput.style.display = 'block';
                        break;
                    case 'style':
                        styleInput.style.display = 'block';
                        break;
                    case 'extrait':
                        extraitInput.style.display = 'block';
                        break;
                    case 'image':
                        imageInput.style.display = 'block';
                        break;
                }
                
                if(this.value !== 'style'){
                    document.getElementById('nomStyle').style.display = 'none';
                }
            });
            
            document.getElementById('nouveauStyle').addEventListener('change', function() {
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
                return $pdo->modifierTitre($_POST['spectacle'], $_POST['nouveauTitre']);
            case 'groupe':
                return $pdo->modifierGroupe($_POST['spectacle'], $_POST['nouveauGroupe']);
            case 'duree':
                return $pdo->modifierDuree($_POST['spectacle'], $_POST['nouvelleDuree']);
            case 'description':
                return $pdo->modifierDescription($_POST['spectacle'], $_POST['nouvelleDescription']);
            case 'style':
                if($_POST['nouveauStyle'] != 'Autre')
                    return $pdo->modifierStyle($_POST['spectacle'], $_POST['nouveauStyle'], null);
                else
                    return $pdo->modifierStyle($_POST['spectacle'], null, $_POST['nomStyle']);
            case 'extrait':
                return $pdo->modifierExtrait($_POST['spectacle'], $_POST['nouvelExtrait']);
            case 'image':
                $upload_dir_image = Renderer::REPERTOIRE_IMAGE;
                $nomNouvelleImage = uniqid();

                if (isset($_FILES['nouvelleImage']) && $_FILES['nouvelleImage']['error'] === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['nouvelleImage']['tmp_name'];
                    if ($_FILES['nouvelleImage']['type'] === 'image/jpeg') {
                        $dest = $upload_dir_image . $nomNouvelleImage . '.jpeg';
                        move_uploaded_file($tmp, $dest);
                        $nomNouvelleImage = $nomNouvelleImage . '.jpeg';
                    } else {
                        return "image invalide";
                    }
                } else {
                    return "image non trouvée ou erreur de téléchargement";
                }
                return $pdo->modifierImage($_POST['spectacle'], $nomNouvelleImage);
            default :
                return "<p>aucune modification</p>";
        }
    }
}