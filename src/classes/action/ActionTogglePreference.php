<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\ListSpectacle;
use iutnc\nrv\repository\NrvRepository;

class ActionTogglePreference extends Action
{

    /**
     * @throws AuthException
     * @throws RepoException
     */
    function executeGet(): string
    {
        if(isset($_GET['id'])) {
            $pdo = NrvRepository::getInstance();
            $spec = $pdo->getSpectacleBySoireeToSpectacle($pdo->getSoireeIDBySpectacleID($_GET['id']),$_GET['id']);
            if(isset($_SESSION['pref'])){
                $listSpectacle = unserialize($_SESSION['pref']);
            }else{
                $listSpectacle = new ListSpectacle();
            }
            if(!$listSpectacle->contientSpectacle($spec)) {
                $listSpectacle->addSpectacle($spec);
            }else{
                $listSpectacle->delSpectacle($spec);
            }
            $_SESSION['pref'] = serialize($listSpectacle);
            header('Location: index.php?action=showSpectacleDetails&spectacleid='.$_GET['id'].'&soireeid='.$pdo->getSoireeIDBySpectacleID($_GET['id']));
            return "";
        } else {
            return "Aucun spectacle";
        }
    }

    function executePost(): string
    {
        return "nothing to return";
    }
}