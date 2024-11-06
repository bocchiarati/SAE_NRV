<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\repository\DeefyRepository;

class ActionAddTrack extends Action
{

    public function executePost(): string
    {

        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthException $e) {
            return "Vous devez être connecté pour pouvoir ajouter une playlist";
        }

        $playlist = unserialize($_SESSION['playlist']);
        $authz = new Authz($user);
        $pdo = DeefyRepository::getInstance();
        if(!$authz->checkPlaylistOwner($pdo->findOwnerPlaylist($playlist->id))) {
            return "Vous n'avez pas la permission d'ajouter un track à cette playlist";
        }

        $upload_dir = Renderer::REPERTOIRE_AUDIO;
        $filename = uniqid();
        $tmp = $_FILES['inputfile']['tmp_name'];
        if (($_FILES['inputfile']['error'] === UPLOAD_ERR_OK) && ($_FILES['inputfile']['type'] === 'audio/mpeg') ) {

            $dest = $upload_dir.$filename.'.mp3';
            move_uploaded_file($tmp, $dest );
            $fichier = $filename.'.mp3';

        } else {
            return "fichier invalide";
        }

        $playlist = unserialize($_SESSION['playlist']);

        if(
            empty(filter_var($_POST['nomTrack'], FILTER_SANITIZE_STRING)) ||
            empty(filter_var($_POST['genre'], FILTER_SANITIZE_STRING)) ||
            filter_var($_POST['duree'], FILTER_SANITIZE_NUMBER_INT) === false
        ){ return "Paramètre invalide détecté, ajout annulé";}

        if ($_POST['typeTrack'] === 'album') {
            if (
                empty(filter_var($_POST['album'], FILTER_SANITIZE_STRING)) ||
                filter_var($_POST['annee'], FILTER_SANITIZE_NUMBER_INT) === false ||
                empty(filter_var($_POST['nomArtiste'], FILTER_SANITIZE_STRING)) ||
                filter_var($_POST['numPiste'], FILTER_SANITIZE_NUMBER_INT) === false
            ) {
                return "Paramètre invalide détecté, ajout annulé";
            }
            $track = new AlbumTrack($_POST['nomTrack'], filter_var($_POST['album'], FILTER_SANITIZE_URL), filter_var($_POST['duree'], FILTER_SANITIZE_NUMBER_INT), filter_var($_POST['annee'], FILTER_SANITIZE_NUMBER_INT), $fichier, filter_var($_POST['genre'], FILTER_SANITIZE_NUMBER_INT), filter_var($_POST['nomArtiste'], FILTER_SANITIZE_URL), filter_var($_POST['numPiste'], FILTER_SANITIZE_NUMBER_INT));
            $repo = DeefyRepository::getInstance();
            $track = $repo->saveAlbumTrack($track,$playlist);
            $playlist->addTrack($track);
        }else{
            if (
                empty(filter_var($_POST['auteur'], FILTER_SANITIZE_STRING)) ||
                empty(filter_var($_POST['date'], FILTER_SANITIZE_STRING))
            ) {
                return "Paramètre invalide détecté, ajout annulé";
            }
            $track = new PodcastTrack($_POST['nomTrack'],$fichier, filter_var($_POST['auteur'], FILTER_SANITIZE_URL), filter_var($_POST['date'], FILTER_SANITIZE_URL), filter_var($_POST['duree'],FILTER_SANITIZE_NUMBER_INT), filter_var($_POST['genre'], FILTER_SANITIZE_NUMBER_INT));
            $repo = DeefyRepository::getInstance();
            $track = $repo->savePodcastTrack($track,$playlist);
            $playlist->addTrack($track);
        }
        $_SESSION['playlist'] = serialize($playlist);
        return "<div>Track ajouté a la playlist</div>  <a href='?action=add-track'>Ajouter encore une piste ?</a>";
    }

    function executeGet(): string
    {
        if(!isset($_SESSION['playlist'])){
            return "Veuillez d'abord créer une playlist";
        }

        $formulaire = '
        <form method="post" action="?action=addTrack" enctype="multipart/form-data">
            <input type="text" name="nomTrack" placeholder="Nom du track" required autocomplete="true" autofocus>
            <input type="text" name="duree" placeholder="Duree du track" required autocomplete="true">
            
            <label>Type de piste :</label>
            <select name="typeTrack" id="typeTrack" required onchange="toggleTrackFields()">
                <option value="album">Album</option>
                <option value="podcast">Podcast</option>
            </select>
            <br>
        
            <!-- Champs spécifiques pour AlbumTrack -->
            <div id="albumFields">
                <input type="text" name="album" placeholder="Nom de l\'album" autocomplete="true">
                <input type="text" name="nomArtiste" placeholder="Nom de l\'artiste" autocomplete="true">
                <input type="number" min="1925" max="2025" name="annee" placeholder="Année de l\'album" autocomplete="true">
                <input type="number" name="numPiste" placeholder="Numéro de la piste" autocomplete="true">
            </div>
        
            <!-- Champs spécifiques pour PodcastTrack -->
            <div id="podcastFields" style="display:none;">
                <input type="text" name="auteur" placeholder="Auteur du podcast" autocomplete="true">
                <input type="date" name="date" placeholder="Date du podcast">
            </div>
        
            <label>Choisissez un genre :</label>
            <select name="genre" required>
        ';

        // Génération des genres depuis la classe AudioTrack
        $genres = AudioTrack::getGenres();
        foreach ($genres as $key => $value) {
            $formulaire .= "<option value=\"$value\">$key</option>";
        }

        $formulaire .= '
            </select>
            <br>
            <input type="file" name="inputfile" required>
            <button type="submit">Valider</button>
        </form>
        
        <script>
        function toggleTrackFields() {
            var typeTrack = document.getElementById("typeTrack").value;
            var albumFields = document.getElementById("albumFields");
            var podcastFields = document.getElementById("podcastFields");
        
            if (typeTrack === "album") {
                albumFields.style.display = "block";
                podcastFields.style.display = "none";
            } else if (typeTrack === "podcast") {
                albumFields.style.display = "none";
                podcastFields.style.display = "block";
            }
        }
        </script>
        ';

        return $formulaire;

    }
}