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
    public function SoireeByDate(?string $date): array{
        $listSoiree = [];

        $query = "SELECT * FROM soiree WHERE date = :date";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['date' => $date]);

        while ($fetch = $resultat->fetch()){
            $soiree = new Soiree($fetch['date'],$fetch['lieuID'],$this->nomLieuByID($fetch['lieuID']),$this->adresseLieuByID($fetch['lieuID']));
            $soiree->setID($fetch['soireeID']);
            $listSoiree[] = $soiree;
        }

        return $listSoiree;
    }


    public function SpectaclesByStyle(int $style): ListSpectacle
    {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT * FROM spectacle sp 
        WHERE sp.styleID = :style;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['style' => $style]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->nomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image']);
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

    public function programmeSpectacleBySoiree(int $soireeID): ListSpectacle {
        $spectacles = [];
        $query = 'SELECT s.spectacleID, s.titre, s.groupe, s.duree, s.description, s.extrait, s.image, s.styleID
              FROM Spectacle s
              INNER JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              WHERE sts.soireeID = :soireeID
              ORDER BY s.spectacleID';

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['soireeID' => $soireeID]);

        while ($fetch = $resultat->fetch()){
            $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->nomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image']);
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
    public function nomLieuByID(int $id):?string{
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
    public function nomStyleByID(int $id):?string{
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
    public function saveSoiree(?string $date, ?int $lieuID, ?string $nomLieu, ?string $adresse): Soiree {
        if($lieuID === null){
            $query = "insert into Lieu (nom, adresse) values (:nom, :adresse)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['nom' => $nomLieu,'adresse' => $adresse]);
            $lieuID = $this->pdo->lastInsertId();
        }

        $query = "insert into Soiree (date, lieuID) values (:date,:lieuid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['date' => $date,'lieuid' => $lieuID]);
        $soiree = new Soiree($date,$lieuID, $this->nomLieuByID($lieuID),$this->adresseLieuByID($lieuID));
        $soiree->setID($this->pdo->lastInsertId());
        return $soiree;
    }

    /**
     * @throws RepoException
     */
    public function findAllSpectacle():array {
        $spectacles = [];
        $query = 'Select * from spectacle';
        $resultat = $this->pdo->prepare($query);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $spec = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'], $this->nomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image']);
            $spec->setID($fetch['spectacleID']);
            $spectacles[] = $spec;
        }
        return $spectacles;
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
    public function SoireeByLocation(int $lieuID): array
    {
        $listSoiree = [];

        $query = "SELECT * FROM Soiree WHERE lieuID = :lieuID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['lieuID' => $lieuID]);

        while ($fetch = $stmt->fetch()) {
            $soiree = new Soiree($fetch['date'], $fetch['lieuID'], $this->nomLieuByID($fetch['lieuID']), $this->adresseLieuByID($fetch['lieuID']));
            $soiree->setID($fetch['soireeID']);
            $listSoiree[] = $soiree;
        }

        return $listSoiree;
    }

    // retourne le spectacle par son id
    public function SpectacleByID(int $id): Spectacle
    {
        $query = "SELECT * FROM spectacle WHERE spectacleID = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $fetch = $stmt->fetch();
        if($fetch === false){
            throw new RepoException("Spectacle introuvable");
        }
        $spectacle = new Spectacle($fetch['titre'],$fetch['groupe'],$fetch['duree'],$fetch['styleID'],$this->nomStyleByID($fetch['styleID']),$fetch['description'],$fetch['extrait'],$fetch['image']);
        $spectacle->setID($fetch['spectacleID']);
        return $spectacle;
    }
}