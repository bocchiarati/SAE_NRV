<?php

namespace iutnc\deefy\repository;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\User;
use iutnc\deefy\exception\AuthException;
use iutnc\deefy\exception\RepoException;
use PDO;

class DeefyRepository
{
    private \PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function getInstance(): DeefyRepository
    {
        if (is_null(self::$instance)) {
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Error reading configuration file");
        }
        self::$config = [
            'dsn' => $conf['driver'].":host=".$conf['host'].";dbname=".$conf['dbname'].";charset=utf8",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }

    /**
     * @param int $id
     * @return Playlist la playlist avec tout ses track
     * @throws RepoException si aucune playlist n'est trouvée
     */
    public function findPlaylistById(int $id): Playlist
    {
        $query = 'Select * from playlist where id = '.$id.';';
        $resultat = $this->pdo->query($query);
        $fetch = $resultat->fetch();
        if (isset($fetch['nom'])) {
            $playlist = new Playlist($fetch['nom']);
        }else{
            throw new RepoException();
        }
        $playlist->setID($id);

        //Ajoute les track dans la playlist
        $query = 'Select * from playlist2track pt inner join track t on t.id = pt.id_track where id_pl = :id order by no_piste_dans_liste';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        while ($fetch = $stmt->fetch()){
            if($fetch['type'] == 'A'){
                $track = new AlbumTrack($fetch['titre'],$fetch['titre_album'],$fetch['duree'],$fetch['annee_album'],$fetch['filename'],$fetch['genre'],$fetch['artiste_album'],$fetch['numero_album']);
            }else{
                $track = new PodcastTrack($fetch['titre'],$fetch['filename'],$fetch['auteur_podcast'],$fetch['date_podcast'],$fetch['duree'],$fetch['genre']);
            }
            $track->setID($fetch['id']);
            $playlist->addTrack($track);
        }
        return $playlist;
    }

    /**
     * @param int $idPlaylist
     * @return int l'user qui est l'owner de la playlist
     */
    public function findOwnerPlaylist(int $idPlaylist): int
    {
        $query = 'Select * from user2playlist where id_pl = '.$idPlaylist.';';
        $resultat = $this->pdo->query($query);
        $fetch = $resultat->fetch();
        return $fetch['id_user'];
    }

    /**
     * @param int $id
     * @return array les playlist de la base appartenant à l'utilisateur
     */
    public function findAllPlaylistsByUser(int $id): array
    {
        $tab = [];
        $query = 'Select * from user2playlist up inner join playlist p on p.id = up.id_pl where id_user = :id_user order by id_pl';
        $resultat = $this->pdo->prepare($query);
        $resultat->bindParam(':id_user', $id, PDO::PARAM_INT);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $playlist = new Playlist($fetch['nom']);
            $playlist->setID($fetch['id']);
            $tab[] = $playlist;
        }
        return $tab;
    }

    /**
     * @return array toutes les playlist de la base
     */
    public function findAllPlaylist(): array
    {
        $tab = [];
        $query = 'Select * from user2playlist up inner join playlist p on p.id = up.id_pl order by id_pl';
        $resultat = $this->pdo->prepare($query);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $playlist = new Playlist($fetch['nom']);
            $playlist->setID($fetch['id']);
            $tab[] = $playlist;
        }
        return $tab;
    }

    /**
     * @param Playlist $pk playlist à save
     * @return Playlist playlist save
     * @throws \iutnc\deefy\exception\AuthException lance une exception si l'user n'est pas connecté
     */
    public function saveEmptyPlaylist(PlayList $pk): PlayList
    {
        $query = "INSERT INTO playlist (nom) VALUES (:nom)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $pk->nom]);
        $pk->setID($this->pdo->lastInsertId());

        $query = "INSERT INTO user2playlist (id_user,id_pl) VALUES (:id_user,:id_pl)";
        $stmt = $this->pdo->prepare($query);
        $user = AuthnProvider::getSignedInUser();
        $stmt->execute(['id_user' => $user->id,'id_pl' => $pk->id]);
        return $pk;
    }

    /**
     * @param PodcastTrack $track sauvegarde le podcast dans la playlist
     * @param Playlist $playlist
     * @return PodcastTrack
     */
    public function savePodcastTrack(PodcastTrack $track, Playlist $playlist): PodcastTrack
    {
        $query = "INSERT INTO track (titre,genre,duree,filename,type,artiste_album,titre_album,annee_album,numero_album,auteur_podcast,date_podcast) VALUES (:titre,:genre,:duree,:filename,'P',NULL,NULL,NULL,NULL,:auteur_podcast,:date_podcast)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'titre' => $track->titre,
            'genre' => $track->genreInt,
            'duree' => $track->duree,
            'filename' => $track->nomFichier,
            'auteur_podcast' => $track->auteur,
            'date_podcast' => $track->date
        ]);
        $track->setID($this->pdo->lastInsertId());

        $this->saveTrackIntoPlaylist($playlist, $track);

        return $track;
    }

    /**
     * @param AlbumTrack $track sauvegarde l'album track dans la playlist
     * @param Playlist $playlist
     * @return AlbumTrack
     */
    public function saveAlbumTrack(AlbumTrack $track, Playlist $playlist): AlbumTrack
    {
        $query = "INSERT INTO track (titre,genre,duree,filename,type,artiste_album,titre_album,annee_album,numero_album,auteur_podcast,date_podcast) VALUES (:titre,:genre,:duree,:filename,'A',:artiste_album,:titre_album,:annee_album,:numero_album,NULL,NULL)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'titre' => $track->titre,
            'genre' => $track->genreInt,
            'duree' => $track->duree,
            'filename' => $track->nomFichier,
            'artiste_album' => $track->artiste,
            'titre_album' => $track->album,
            'annee_album' => $track->annee,
            'numero_album' => $track->numPiste
        ]);
        $track->setID($this->pdo->lastInsertId());

        $this->saveTrackIntoPlaylist($playlist, $track);

        return $track;
    }

    /**
     * @param Playlist $playlist
     * @param AudioTrack $track sauvegarde le track dans la table playlist2track avec sa position dans la liste
     */
    private function saveTrackIntoPlaylist(Playlist $playlist, AudioTrack $track) : void{
        $position = 1;
        foreach ($playlist as $key => $value) {
            if ($value->id === $track->id) {
                break;
            }
            $position++;
        }

        $query = "INSERT INTO playlist2track (id_pl,id_track,no_piste_dans_liste) VALUES (:id_pl,:id_track,:no_piste)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_pl' => $playlist->id,'id_track' => $track->id,'no_piste' => $position]);
    }

    /**
     * @param string $email
     * @return User retourne l'user associé à l'email
     * @throws AuthException
     */
    public function getUser(string $email) : User {
        $query = "select id,passwd,role from user where email = ? ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1,$email);
        $stmt->execute();
        $donnee = $stmt->fetch();
        if(!isset($donnee[0])){
            throw new AuthException("Utilisateur introuvable");
        }
        return new User($donnee[0],$donnee[1],$donnee[2]);
    }

    /**
     * @param string $email
     * @param string $mdpHash
     * @return User sauvegarde un user avec son email et mot de passe hashé
     * @throws AuthException
     */
    public function saveUser(string $email, string $mdpHash): User {
        $query = "select id,passwd,role from user where email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        if($stmt->rowCount() === 1){
            throw new AuthException("Utilisateur déjà existant");
        }
        $query = "insert into User (email,passwd,role) values (:email,:passwd,:role)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email,'passwd' => $mdpHash,'role' => User::STANDARD_USER]);
        return new User($this->pdo->lastInsertId(),$mdpHash,User::STANDARD_USER);
    }
}