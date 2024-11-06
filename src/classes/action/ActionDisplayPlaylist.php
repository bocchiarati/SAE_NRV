<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\exception\RepoException;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\repository\DeefyRepository;

class ActionDisplayPlaylist extends Action
{

    function executeGet(): string
    {
        $pdo = DeefyRepository::getInstance();
        if(isset($_GET['id']))
            try {
                $_SESSION['playlist'] = serialize($pdo->findPlaylistById($_GET['id']));
            } catch (RepoException $e) {
                return "Aucune playlist trouvée dans la base de donnée";
            }

        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $playlist = unserialize($_SESSION['playlist']);
        $authz = new Authz($user);
        if($authz->checkPlaylistOwner($playlist->id)) {
            $renderer = new AudioListRenderer($playlist);
            return $renderer->render(Renderer::LONG);
        }else{
            return "Vous n'avez pas la permission d'afficher cette playlist";
        }
    }

    function executePost(): string
    {
        return "";
    }
}