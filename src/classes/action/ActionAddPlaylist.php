<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\repository\DeefyRepository;

class ActionAddPlaylist extends Action
{

    public function executeGet(): string
    {
        return <<<END
        <form method="post" action="?action=addPlaylist">
            <input type="text" name="nomPlaylist" placeholder="nom de la playlist" required autocomplete="true" autofocus>
            <button type="submit">Valider</button>
        </form>
        END;
    }

    public function executePost(): string
    {
        try {
            AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Vous devez être connecté pour pouvoir sauvegarder une playlist";
        }

        $nom = filter_var($_POST['nomPlaylist'],FILTER_SANITIZE_URL);
        $playlist = new Playlist($nom);
        $repo = DeefyRepository::getInstance();
        $playlist = $repo->saveEmptyPlaylist($playlist);
        $_SESSION['playlist'] = serialize($playlist);

        return <<<END
            Playlist créé : $playlist->nom <br>
            <a href="index.php?action=addTrack">Ajouter une piste à votre nouvelle playlist ?</a>
        END;
    }
}