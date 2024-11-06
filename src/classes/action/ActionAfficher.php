<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\repository\DeefyRepository;

class ActionAfficher extends Action
{

    public function executeGet(): string
    {
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Aucun utilisateur connecté";
        }

        $lien = '';

        // Génération des playlists
        $bd = DeefyRepository::getInstance();
        $playlists = $bd->findAllPlaylistsByUser($user->id);
        foreach ($playlists as $key => $value) {
            $lien .= "<a href='?action=display-playlist&id=$value->id'>$value->nom</a><br>";
        }
        return $lien;
    }

    public function executePost(): string
    {
        return "";
    }
}