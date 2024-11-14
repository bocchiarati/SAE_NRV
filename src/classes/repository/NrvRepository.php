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
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'], $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']);
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);

        return $listSpectacles;
    }

    // Retourne la liste des spectacles par date
    public function getSpectaclesByDate(string $date): ListSpectacle
    {
        $listSpectacles = new ListSpectacle();
        $list = [];

        $query = "SELECT sp.* FROM Spectacle sp
              JOIN SoireeToSpectacle sts ON sts.spectacleID = sp.spectacleID
              JOIN Soiree s ON s.soireeID = sts.soireeID
              WHERE Date(s.date) = :date;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['date' => $date]);

        while ($fetch = $resultat->fetch()) {
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'],
                $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']
            );
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);
        return $listSpectacles;
    }

    // retourne la liste des spectacles par lieu
    public function getSpectaclesByLocation(int $locationId): ListSpectacle
    {
        $listSpectacles = new ListSpectacle();
        $list = [];

        $query = "SELECT sp.* FROM Spectacle sp
              JOIN SoireeToSpectacle sts ON sts.spectacleID = sp.spectacleID
              JOIN Soiree s ON s.soireeID = sts.soireeID
              WHERE s.lieuID = :locationId;";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['locationId' => $locationId]);

        while ($fetch = $resultat->fetch()) {
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'],
                $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']
            );
            $spectacle->setID($fetch['spectacleID']);
            $list[] = $spectacle;
        }

        $listSpectacles->setSpectacles($list);
        return $listSpectacles;
    }

    /**
     * @throws AuthException
     */
    public function saveSpectaclePreferences(Spectacle $s): Spectacle {
        $query = "INSERT INTO userspreferences (userid,spectacleid) VALUES (:userid,:spectacleid)";
        $stmt = $this->pdo->prepare($query);
        $user = AuthnProvider::getSignedInUser();
        $stmt->execute(['userid' => $user->id,'spectacleid' => $s->id]);
        return $s;
    }

    public function findPreferences(int $userid): array
    {
        $tab = [];
        $query = 'Select spectacleid from userspreferences where userid = '.$userid.';';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[] = $fetch['spectacleid'];
        }
        return $tab;
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
            $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'], $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']);
            $spectacle->setID($fetch['spectacleID']);
            $spectacles[] = $spectacle;
        }

        $listSpectacle = new ListSpectacle();
        $listSpectacle->setSpectacles($spectacles);

        return $listSpectacle;
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
            $lieuID = $this->saveLieu($nomLieu, $adresse);
        }

        $query = "insert into Soiree (tarif, nom, thematique, date, lieuID) values (:tarif, :nom, :thematique, :date,:lieuid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['tarif' => $tarif, 'nom' => $nom, 'thematique' => $thematique,'date' => $date.' '.$time,'lieuid' => $lieuID]);
        $soiree = new Soiree($date,$lieuID, $this->getNomLieuByID($lieuID),$this->adresseLieuByID($lieuID), $nom, $thematique, $tarif);
        $soiree->setID($this->pdo->lastInsertId());
        return $soiree;
    }

    public function saveLieu($nomLieu, $adress) : int {
        $query = "insert into Lieu (nom, adresse) values (:nom, :adresse)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $nomLieu,'adresse' => $adress]);
        return $this->pdo->lastInsertId();
    }

    /**
     * @throws RepoException
     */
    public function findAllSpectacle(): ListSpectacle {
        $spectacles = [];
        $query = 'Select * from soireetospectacle sts inner join spectacle s on sts.spectacleid = s.spectacleid';
        $resultat = $this->pdo->prepare($query);
        $resultat->execute();
        while ($fetch = $resultat->fetch()){
            $spec = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'], $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']);
            $spec->setID($fetch['spectacleID']);
            $spectacles[] = $spec;
        }
        $list = new ListSpectacle();
        $list->setSpectacles($spectacles);
        return $list;
    }

    public function getAllStyle(): array{
        $tab = [];
        $query = "Select * from stylemusic order by nomstyle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['styleID']] = $fetch['nomstyle'];
        }
        return $tab;
    }

    public function getAllLieu(): array{
        $tab = [];
        $query = "Select * from lieu order by nom;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['lieuID']] = $fetch['nom'];
        }
        return $tab;
    }

    public function getAllDate(): array{
        $tab = [];
        $query = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m-%d') FROM Soiree ORDER BY date";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch(\PDO::FETCH_NUM)){
            $tab[] = $fetch[0];
        }
        return $tab;
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
        $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'], $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']);
        $spectacle->setID($fetch['spectacleID']);
        return $spectacle;
    }

    public function getSpectacleBySoireeToSpectacle(int $soireeID, int $spectacleID) : Spectacle {
        $query = "SELECT * FROM soireetospectacle sts INNER JOIN spectacle s ON s.spectacleid = sts.spectacleid WHERE sts.spectacleID = :spectacleid AND sts.soireeID = :soireeID";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleid' => $spectacleID, 'soireeID' => $soireeID]);
        $fetch = $stmt->fetch();
        if($fetch === false){
            throw new RepoException("Spectacle introuvable");
        }
        $spectacle = new Spectacle($fetch['titre'], $fetch['groupe'], $fetch['duree'], $fetch['styleID'], $this->getNomStyleByID($fetch['styleID']), $fetch['description'], $fetch['extrait'], $fetch['image'], $fetch['annuler']);
        $spectacle->setID($fetch['spectacleID']);
        return $spectacle;
    }

    //  retourne la date du spectacle par son id
    public function getDateForSpectacle(int $spectacleID): string {
        $query = "SELECT date 
              FROM Soiree s inner join soireetospectacle st on s.soireeID = st.soireeID
              WHERE spectacleID = :spectacleID;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleID' => $spectacleID]);
        $result = $stmt->fetch();

        if(isset($result['date'])){
            return $result['date'];
        }else{
            return "NULL";
        }
    }

    // retourne le nom du lieu du spectacle
    public function getLieuNomForSpectacle(int $spectacleID): string {
        $query = "SELECT l.nom 
                  FROM Lieu l
                  INNER JOIN Soiree s ON l.lieuID = s.lieuID
                  INNER JOIN SoireetoSpectacle st on s.soireeID = st.soireeID
                  WHERE spectacleID = :spectacleID ;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleID' => $spectacleID]);
        $result = $stmt->fetch();

        if(isset($result['nom'])){
            return $result['nom'];
        }else{
            return "NULL";
        }
    }

    // retourne la liste des spectacles par style sans le spectacle actuel pour la page ActionShowSpectacleDetails

    /**
     * @throws RepoException
     */
    public function getSpectaclesByStyleSansActuel(int $style, int $currentSpectacleID, int $currentSoireeID): ListSpectacle {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT * FROM spectacle sp 
              INNER JOIN soireeToSpectacle  sts ON sp.spectacleID = sts.spectacleID
              WHERE sp.styleID = :style AND (sp.spectacleID != :currentSpectacleID OR soireeID != :currentSoireeID);";
        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['style' => $style, 'currentSpectacleID' => $currentSpectacleID, 'currentSoireeID' => $currentSoireeID]);

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
    public function getSpectaclesByLieuSansActuel(int $currentSpectacleID, int $currentSoireeID): ListSpectacle {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT *
              FROM Spectacle s
              JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              JOIN Soiree so ON sts.soireeID = so.soireeID
              WHERE so.lieuID IN (
                  SELECT DISTINCT so2.lieuID
                  FROM Soiree so2
                  JOIN SoireeToSpectacle sts2 ON so2.soireeID = sts2.soireeID
                  WHERE sts2.spectacleID = :currentSpectacleID
                  AND sts2.soireeID = :currentSoireeID
              ) AND (sts.spectacleID != :currentSpectacleID OR sts.soireeID != :currentSoireeID);";

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['currentSpectacleID' => $currentSpectacleID, 'currentSoireeID' => $currentSoireeID]);

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
    public function getSpectaclesByDateSansActuel(string $currentSpectacleID, $soireeID): ListSpectacle {
        $list = [];
        $listSpectacles = new ListSpectacle();

        $query = "SELECT *
              FROM Spectacle s
              JOIN SoireeToSpectacle sts ON s.spectacleID = sts.spectacleID
              JOIN Soiree so ON sts.soireeID = so.soireeID
              WHERE so.date IN (
                  SELECT DISTINCT so2.date
                  FROM Soiree so2
                  JOIN SoireeToSpectacle sts2 ON so2.soireeID = sts2.soireeID
                  WHERE sts2.spectacleID = :currentSpectacleID 
              ) AND (s.spectacleID != :currentSpectacleID 
              OR sts.soireeID != :soireeID);";

        $resultat = $this->pdo->prepare($query);
        $resultat->execute(['currentSpectacleID' => $currentSpectacleID, 'soireeID' => $soireeID]);

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

    public function findAllSoireeWithSpectacle(): array{
        $tab = [];
        $query = "Select * from soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $soiree = new Soiree($fetch['date'], $fetch['lieuID'], $this->getNomLieuByID($fetch['lieuID']), $this->adresseLieuByID($fetch['lieuID']), $fetch['nom'], $fetch['thematique'], $fetch['tarif']);
            $soiree->setId($fetch['soireeID']);
            $query2 = "Select * from soireetospectacle where soireeID = :idsoiree;";
            $stmt2 = $this->pdo->prepare($query2);
            $stmt2->execute(['idsoiree' => $fetch['soireeID']]);
            $spectacles = [];
            while($fetch2 = $stmt2->fetch()){
                $spectacle = $this->getSpectacleByID($fetch2['spectacleID']);
                $spectacles[] = $spectacle;
            }
            $soiree->setSpectacles($spectacles);
            $tab[] = $soiree;
        }
        return $tab;
    }

    public function getAllSpectacle(): array{
        $tab = [];
        $query = "Select * from spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        while($fetch = $stmt->fetch()){
            $tab[$fetch['spectacleID']] = [$fetch['titre'], $fetch['groupe']];
        }
        return $tab;
    }

    public function saveSpectacle(string $titre, string $groupe, int $duree, string $description, ?string $extrait, string $image, ?int $styleID, ?string $nomStyle, ?int $soireeID): Spectacle {
        if($styleID === null){
            $styleID = $this->saveStyle($nomStyle);
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

    public function saveStyle(string $nomStyle) : int {
        $query = "insert into stylemusic (nomstyle) values (:nomStyle)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nomStyle' => $nomStyle]);
        return $this->pdo->lastInsertId();
    }

    public function saveSoireeToSpectacle(int $spectacleID, int $soireeID) {
        $query = "insert into SoireeToSpectacle (spectacleid, soireeid) values (:spectacleid, :soireeid)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleid' => $spectacleID, 'soireeid' => $soireeID]);
        return "spectacle ajouter à la soirée";
    }

    public function getSpectacleAnnuler(int $spectacleID): bool {
        $query = "select annuler from spectacle where spectacleid = :idspectacle ;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idspectacle' => $spectacleID]);
        return $stmt->fetch()['annuler'];
    }

    public function setSpectacleAnnuler(int $spectacleID, bool $annuler): void {
        $query = "update spectacle set annuler = :annuler where spectacleid = :idspectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['idspectacle' => $spectacleID, 'annuler' => $annuler]);
    }

    public function modifierNom(int $soiree, string $nouveauNom) : string {
        $query = "update Soiree set nom = :nom where soireeid = :soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $nouveauNom, 'soiree' => $soiree]);
        return "<p>Nom de la soiree modifier</p>";
    }

    public function modifierThematique(int $soiree, string $nouvelleThematique) : string {
        $query = "update Soiree set thematique = :thematique where soireeid = :soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['thematique' => $nouvelleThematique, 'soiree' => $soiree]);
        return "<p>Thematique de la soiree modifier</p>";
    }

    public function modifierTarif(int $soiree, int $nouveauTarif) : string {
        $query = "update Soiree set tarif = :tarif where soireeid = :soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['tarif' => $nouveauTarif, 'soiree' => $soiree]);
        return "<p>Nom de la soiree modifier</p>";
    }

    public function modifierDate(int $soiree, string $nouvelleDate) : string {
        $query = "update Soiree set date = :date where soireeid = :soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['date' => $nouvelleDate, 'soiree' => $soiree]);
        return "<p>Nom de la soiree modifier</p>";
    }

    public function modifierLieuSoiree(int $soiree, ?int $nouveauLieu, ?string $nomLieu, ?string $address) : string {
        if($nouveauLieu === null){
            $nouveauLieu = $this->saveLieu($nomLieu, $address);
        }
        $query = "update Soiree set lieuID = :lieu where soireeid = :soiree;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['lieu' => $nouveauLieu, 'soiree' => $soiree]);
        return "<p>Lieu de la soiree modifier</p>";
    }

    public function modifierTitre(mixed $spectacle, mixed $nouveauTitre) : string {
        $query = "update Spectacle set titre = :titre where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['titre' => $nouveauTitre, 'spectacle' => $spectacle]);
        return "<p>Titre du spectacle modifier</p>";
    }

    public function modifierGroupe(mixed $spectacle, mixed $nouveauGroupe) : string {
        $query = "update Spectacle set groupe = :groupe where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['groupe' => $nouveauGroupe, 'spectacle' => $spectacle]);
        return "<p>Groupe du spectacle modifier</p>";
    }

    public function modifierDuree(mixed $spectacle, mixed $nouvelleDuree) : string {
        $query = "update Spectacle set duree = :duree where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['duree' => $nouvelleDuree, 'spectacle' => $spectacle]);
        return "<p>Duree du spectacle modifier</p>";
    }

    public function modifierDescription(mixed $spectacle, mixed $nouvelleDescription) : string {
        $query = "update Spectacle set description = :description where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['description' => $nouvelleDescription, 'spectacle' => $spectacle]);
        return "<p>Description du spectacle modifier</p>";
    }

    public function modifierStyle(int $spectacle, ?int $nouveauStyle, ?string $nomStyle) : string {
        if($nouveauStyle == null){
            $this->saveStyle($nomStyle);
        }
        $query = "update Spectacle set styleid = :style where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['style' => $nouveauStyle, 'spectacle' => $spectacle]);
        return "<p>Style du spectacle modifier</p>";
    }

    public function modifierExtrait(mixed $spectacle, mixed $nouvelExtrait) : string {
        $query = "update Spectacle set extrait = :extrait where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['extrait' => $nouvelExtrait, 'spectacle' => $spectacle]);
        return "<p>Extrait du spectacle modifier</p>";
    }

    public function modifierImage(mixed $spectacle, mixed $nouvelleImage) : string {
        $query = "update Spectacle set image = :image where spectacleid = :spectacle;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['image' => $nouvelleImage, 'spectacle' => $spectacle]);
        return "<p>Image du spectacle modifier</p>";
    }

    public function promoteOrga(int $userid) : string{
        $query = "update User set roleid = :roleid where userid = :userid;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['userid' => $userid, 'roleid' => User::ORGANISATOR_USER]);
        return "<p>Role de l'utilisateur '{$this->getEmailByID($userid)}' changer pour 'Organisateur'</p>";
    }

    public function getAllUsersNotOrga() : array {
        $tab = [];
        $query = "select userid, email from User where roleid < :roleid";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['roleid' => User::ORGANISATOR_USER]);
        while($fetch = $stmt->fetch()){
            $tab[$fetch['userid']] = $fetch['email'];
        }
        return $tab;
    }

    public function getEmailByID($userid) : string {
        $query = "select email from User where userid = :userid;";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['userid' => $userid]);
        return $stmt->fetch()['email'];
    }

    // retourne soiree par son id
    public function getSoireeById(int $soireeId): ?Soiree {
        $stmt = $this->pdo->prepare('SELECT s.*, l.nom AS nomLieu, l.adresse AS adresseLieu FROM Soiree s INNER JOIN Lieu l ON s.lieuID = l.lieuID WHERE s.soireeID = :soireeId');
        $stmt->execute(['soireeId' => $soireeId]);

        $soireeData = $stmt->fetch();
        if (!$soireeData) {
            return null;
        }

        $soiree = new Soiree($soireeData['date'], $soireeData['lieuID'], $soireeData['nomLieu'], $soireeData['adresseLieu'],
            $soireeData['nom'], $soireeData['thematique'], $soireeData['tarif']
        );
        $soiree->setId($soireeData['soireeID']);

        return $soiree;
    }

    public function getSoireeIDBySpectacleID(int $spectacleID): int{
        $query = 'Select s.soireeID from soiree s inner join soireetospectacle st on st.soireeID = s.soireeID where st.spectacleID = :spectacleid;';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['spectacleid' => $spectacleID]);

        return $stmt->fetch()['soireeID'];
    }


}



