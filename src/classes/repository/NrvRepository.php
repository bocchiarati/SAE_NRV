<?php

namespace iutnc\nrv\repository;


use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\User;
use iutnc\nrv\exception\AuthException;
use iutnc\nrv\exception\RepoException;
use iutnc\nrv\programme\Soiree;
use iutnc\nrv\programme\Spectacle;
use PDO;

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

    public function saveSoireePreferences(Soiree $s): Soiree {
        $query = "INSERT INTO userpreferences (userid,soireeid) VALUES (:userid,:soireeid)";
        $stmt = $this->pdo->prepare($query);
        $user = AuthnProvider::getSignedInUser();
        $stmt->execute(['userid' => $user->id,'soireeid' => $s->id]);
        return $s;
    } //////////////////////////////////

    public function findAllSpectableBySoiree(int $soireeid): array {
        $tab = [];
        $query = 'Select * from spectacle s 
         inner join spectacletosoiree sts on s.spectacleid = sts.spectacleid 
         where soireeid = '.$soireeid.'
         order by spectacleid';

        $resultat = $this->pdo->prepare($query);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $playlist = new Spectacle($fetch['nom']);
            $playlist->setID($fetch['id']);
            $tab[] = $playlist;
        }
        return $tab;
    } ///////////////////////////////

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
        return new User($donnee[0],$donnee[1],$donnee[2]);
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
        return new User($this->pdo->lastInsertId(),$mdpHash,User::STANDARD_USER);
    }
}