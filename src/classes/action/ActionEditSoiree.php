<?php

namespace iutnc\nrv\action;

use iutnc\nrv\action\Action;
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
        return <<< END
        <h1>Modification d'une soiree :</h1>
        <form method="post">
            <p>Soirée modifier</p>
            <select name="soiree">
                <option value="" disabled selected>Choisir une soiree</option>
                $optionsSoiree
            </select>
            <p>Modification sur : </p>
            <select name="modification">
                <option value="" disabled selected>Choisir une modification</option>
                <option value="nom">Nom</option>
                <option value="thematique">Thematique</option>
                <option value="tarif">Tarif</option>
                <option value="date">Date</option>
                <option value="lieu">Lieu</option>
            </select>
        </form> 
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
    }
}