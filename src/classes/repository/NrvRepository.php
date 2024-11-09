<?php

namespace iutnc\nrv\repository;


use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\User;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\ListSpectacle;
use iutnc\nrv\programme\Soiree;
use iutnc\nrv\programme\Spectacle;

class NrvRepository
{
    private \PDO $pdo;
    private static ?NrvRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function getInstance(): NrvRepository
    {
        if (is_null(self::$instance)) {
            self::$instance = new NrvRepository(self::$config);
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
     * Retourne l'utilisateur associe a l'email
     * @param string $email
     * @return User retourne l'user associé à l'email
     * @throws AuthException
     */
    public function getUser(string $email) : User {
        $query = "select userid,mdp,roleid from user where email = ? ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1,$email);
        $stmt->execute();
        $donnee = $stmt->fetch();
        if(!isset($donnee[0])){
            throw new AuthException("Utilisateur introuvable");
        }
        return new User($donnee[0], $email,$donnee[1],$donnee[2]);
    }

    /**
     * Sauvegarde un utilisateur avec son email et mot de passe hashe
     * @param string $email
     * @param string $mdpHash
     * @return User sauvegarde un user avec son email et mot de passe hashé
     * @throws AuthException
     */
    public function saveUser(string $email, string $mdpHash): User {
        $query = "select userid,mdp,roleid from user where email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        if($stmt->rowCount() === 1){
            throw new AuthException("Utilisateur déjà existant");
        }
        $query = "insert into User (email,mdp,roleid) values (:email,:mdp,:roleid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email,'mdp' => $mdpHash,'roleid' => User::STANDARD_USER]);
        return new User($this->pdo->lastInsertId(), $email ,$mdpHash,User::STANDARD_USER);
    }

    // retourne la liste des spectacles par date

    /**
     * @throws RepoException
     */
    public function getSoireeByDate(?string $date): array{
        $listSoiree = [];

        $query = "SELECT * FROM soiree WHERE date = :date";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['date' => $date]);

        while ($fetch = $resultat->fetch()){
            $soiree = new Soiree($fetch['date'],$fetch['lieuID'],$this->getNomLieuByID($fetch['lieuID']),$this->adresseLieuByID($fetch['lieuID']), $fetch['nom'], $fetch['thematique'], $fetch['tarif']);
            $soiree->setID($fetch['soireeID']);
            $listSoiree[] = $soiree;
        }

        return $listSoiree;
    }

    // retourne la liste des spectacles par style
    public function getSpectaclesByStyle(int $style): ListSpectacle
    {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT * FROM spectacle sp 
        WHERE sp.styleID = :style;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['style' => $style]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->getNomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image'], $fetch['annuler']);
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);

        return $listSpectacles;
    }

    public function saveSpectaclePreferences(Spectacle $s): Spectacle {
        $query = "INSERT INTO userpreferences (userid,spectacleid) VALUES (:userid,:spectacleid)";
        $stmt = $this->pdo->prepare($query);
        $user = AuthnProvider::getSignedInUser();
        $stmt->execute(['userid' => $user->id,'spectacleid' => $s->id]);
        return $s;
    }

    // retourne la liste des spectacles par soiree
    public function getSpectacleBySoiree(int $soireeID): ListSpectacle {
        $spectacles = [];
        $query = 'SELECT s.spectacleID, s.titre, s.groupe, s.duree, s.description, s.extrait, s.image, s.styleID, s.annuler
              FROM Spectacle s
              INNER JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              WHERE sts.soireeID = :soireeID
              ORDER BY s.spectacleID';

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['soireeID' => $soireeID]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->getNomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image'], $fetch['annuler']);
            $spectacle->setID($fetch['spectacleID']);
            $spectacles[] = $spectacle;
        }

        $listSpectacle = new ListSpectacle();
        $listSpectacle->setSpectacles($spectacles);

        return $listSpectacle;
    }

    public function findPreferences(int $userid): int
    {
        $query = 'Select * from userspreferences where userid = '.$userid.';';
        $resultat = $this->pdo->query($query);
        $fetch = $resultat->fetch();
        return $fetch['spetacleid'];
    }

    // retourne le nom du lieu par son id
    public function getNomLieuByID(int $id):?string{
        $query = "select nom from lieu where lieuid = :id ;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['id' => $id]);
        if($resultat->rowCount() === 0){
            throw new RepoException("Lieu introuvable");
        }
        $fetch = $resultat -> fetch();
        return $fetch['nom'];
    }

    // retourne l'adresse du lieu par son id
    public function adresseLieuByID(int $id):?string{
        $query = "select adresse from lieu where lieuid = :id ;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['id' => $id]);
        if($resultat->rowCount() === 0){
            throw new RepoException("Adresse introuvable");
        }
        $fetch = $resultat -> fetch();
        return $fetch['adresse'];
    }

    // retourne le nom du style par son id
    public function getNomStyleByID(int $id):?string{
        $query = "select nomstyle from stylemusic where styleid = :id ;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['id' => $id]);
        if($resultat->rowCount() === 0){
            throw new RepoException("Style introuvable");
        }
        $fetch = $resultat -> fetch();
        return $fetch['nomstyle'];
    }

    /**
     * @throws RepoException
     */
    public function saveSoiree(?string $date, ?string $time, ?int $lieuID, ?string $nomLieu, ?string $adresse, float $tarif, ?string $nom, ?string $thematique): Soiree {
        if($lieuID === null){
            $query = "insert into Lieu (nom, adresse) values (:nom, :adresse)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['nom' => $nomLieu,'adresse' => $adresse]);
            $lieuID = $this->pdo->lastInsertId();
        }

        $query = "insert into Soiree (tarif, nom, thematique, date, lieuID) values (:tarif, :nom, :thematique, :date,:lieuid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['tarif' => $tarif, 'nom' => $nom, 'thematique' => $thematique,'date' => $date.' '.$time,'lieuid' => $lieuID]);
        $soiree = new Soiree($date,$lieuID, $this->getNomLieuByID($lieuID),$this->adresseLieuByID($lieuID), $nom, $thematique, $tarif);
        $soiree->setID($this->pdo->lastInsertId());
        return $soiree;
    }

    /**
     * @throws RepoException
     */
    public function findAllSpectacle(): ListSpectacle {
        $spectacles = [];
        $query = 'Select * from spectacle';
        $resultat = $this->pdo->prepare($query);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $spec = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'], $this->getNomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image'], $fetch['annuler']);
            $spec->setID($fetch['spectacleID']);
            $spectacles[] = $spec;
        }
        $list = new ListSpectacle();
        $list->setSpectacles($spectacles);
        return $list;
    }

    public function getAllStyle(): array{
        $tab = [];
        $query = "Select * from stylemusic ;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['styleID']] = $fetch['nomstyle'];
        }
        return $tab;
    }

    public function getAllLieu(): array{
        $tab = [];
        $query = "Select * from lieu ;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['lieuID']] = $fetch['nom'];
        }
        return $tab;
    }

    // retourne la liste des soirees par lieu
    public function getSoireeByLocation(int $lieuID): array
    {
        $listSoiree = [];

        $query = "SELECT * FROM Soiree WHERE lieuID = :lieuID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['lieuID' => $lieuID]);

        while ($fetch = $stmt->fetch()) {
            $soiree = new Soiree($fetch['date'],$fetch['lieuID'],$this->getNomLieuByID($fetch['lieuID']),$this->adresseLieuByID($fetch['lieuID']), $fetch['nom'], $fetch['thematique'], $fetch['tarif']);
            $soiree->setID($fetch['soireeID']);
            $listSoiree[] = $soiree;
        }

        return $listSoiree;
    }

    // retourne le spectacle par son id
    public function getSpectacleByID(int $id): Spectacle
    {
        $query = "SELECT * FROM spectacle WHERE spectacleID = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $fetch = $stmt->fetch();
        if($fetch === false){
            throw new RepoException("Spectacle introuvable");
        }
        $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->getNomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image'], $fetch['annuler']);
        $spectacle->setID($fetch['spectacleID']);
        return $spectacle;
    }

    //  retourne la date du spectacle par son id
    public function getDateForSpectacle(int $spectacleID): string {
        $query = "SELECT s.date 
                  FROM Soiree s
                  INNER JOIN SoireeToSpectacle sts ON s.soireeID = sts.soireeID
                  WHERE sts.spectacleID = :spectacleID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleID' => $spectacleID]);
        $result = $stmt->fetch();

        return $result['date'];
    }

    // retourne le nom du lieu du spectacle
    public function getLieuNomForSpectacle(int $spectacleID): string {
        $query = "SELECT l.nom 
                  FROM Lieu l
                  INNER JOIN Soiree s ON l.lieuID = s.lieuID
                  INNER JOIN SoireeToSpectacle sts ON s.soireeID = sts.soireeID
                  WHERE sts.spectacleID = :spectacleID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleID' => $spectacleID]);
        $result = $stmt->fetch();

        return $result['nom'];
    }

    // retourne la liste des spectacles par style sans le spectacle actuel pour la page ActionShowSpectacleDetails
    public function getSpectaclesByStyleSansActuel(int $style, int $currentSpectacleID): ListSpectacle
    {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT * FROM spectacle sp 
              WHERE sp.styleID = :style AND sp.spectacleID != :currentSpectacleID;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['style' => $style, 'currentSpectacleID' => $currentSpectacleID]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'],
                $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'],
                $fetch['image'], $fetch['annuler']);
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);

        return $listSpectacles;
    }

    // retourne la liste des spectacles par lieu sans le spectacle actuel pour la page ActionShowSpectacleDetails
    public function getSpectaclesByLieuSansActuel(int $currentSpectacleID): ListSpectacle {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT s.*
              FROM Spectacle s
              JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              JOIN Soiree so ON sts.soireeID = so.soireeID
              WHERE so.lieuID IN (
                  SELECT DISTINCT so2.lieuID
                  FROM Soiree so2
                  JOIN SoireeToSpectacle sts2 ON so2.soireeID = sts2.soireeID
                  WHERE sts2.spectacleID = :currentSpectacleID
              ) AND s.spectacleID != :currentSpectacleID;";

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['currentSpectacleID' => $currentSpectacleID]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'],
                $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'],
                $fetch['image'], $fetch['annuler']
            );
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);
        return $listSpectacles;
    }

    // retourne la liste des spectacles par date sans le spectacle actuel pour la page ActionShowSpectacleDetails
    public function getSpectaclesByDateSansActuel(string $currentSpectacleID): ListSpectacle {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT s.*
              FROM Spectacle s
              JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              JOIN Soiree so ON sts.soireeID = so.soireeID
              WHERE so.date IN (
                  SELECT DISTINCT so2.date
                  FROM Soiree so2
                  JOIN SoireeToSpectacle sts2 ON so2.soireeID = sts2.soireeID
                  WHERE sts2.spectacleID = :currentSpectacleID
              ) AND s.spectacleID != :currentSpectacleID;";

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['currentSpectacleID' => $currentSpectacleID]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle( $fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'],
                $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'],
                $fetch['image'], $fetch['annuler']
            );
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);
        return $listSpectacles;
    }

    public function getAllSoiree(): array{
        $tab = [];
        $query = "Select * from soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['soireeID']] = [$fetch['nom'], $fetch['thematique'], $this->getNomLieuByID($fetch['lieuID'])];
        }
        return $tab;
    }

    public function saveSpectacle(string $titre, string $groupe, int $duree, string $description, ?string $extrait, string $image, ?int $styleID, ?string $nomStyle, ?int $soireeID): Spectacle {
        if($styleID === null){
            $query = "insert into stylemusic (nomstyle) values (:nomStyle)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['nomStyle' => $nomStyle]);
            $styleID = $this->pdo->lastInsertId();
        }

        $query = "insert into Spectacle (titre, groupe, duree, description, extrait, image, styleID, annuler) values (:titre, :groupe, :duree, :desc, :extrait, :image, :styleid, false)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['titre' => $titre, 'groupe' => $groupe, 'duree' => $duree, 'desc' => $description, 'extrait' => $extrait, 'image' => $image, 'styleid' => $styleID]);
        $spectacle = new Spectacle($titre, $groupe, $duree, $styleID, $nomStyle, $description, $extrait, $image, false);
        $spectacle->setID($this->pdo->lastInsertId());

        if($soireeID !== null) {
            $this->saveSoireeToSpectacle($spectacle->getID(), $soireeID);
        }
        return $spectacle;
    }

    public function saveSoireeToSpectacle(int $spectacleID, int $soireeID): void {
        $query = "insert into SoireeToSpectacle (spectacleid, soireeid) values (:spectacleid, :soireeid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleid' => $spectacleID, 'soireeid' => $soireeID]);
    }
}



