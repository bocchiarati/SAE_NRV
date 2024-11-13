-- creation de la table lieu
CREATE TABLE Lieu(
	lieuID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL
);

-- creation de la table soiree
CREATE TABLE Soiree(
	soireeID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    thematique VARCHAR(255),
    tarif FLOAT,
    date DATETIME NOT NULL,
    lieuID INT,
    FOREIGN KEY (lieuID) REFERENCES Lieu(lieuID)
);

-- creation de la table stylemusic
CREATE TABLE StyleMusic(
    styleID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nomstyle VARCHAR(30) NOT NULL
);

-- creation de la table spectacle
CREATE TABLE Spectacle(
	spectacleID INT(3) AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    groupe VARCHAR(255) NOT NULL,
    duree INT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    extrait VARCHAR(255) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    styleID INT,
    annuler BOOLEAN,
    FOREIGN KEY (styleID) REFERENCES StyleMusic(styleID)
);

-- creation de la table soireespectacle
CREATE TABLE SoireeToSpectacle(
    soireeID INT(2),
    spectacleID INT(2),
    PRIMARY KEY (soireeID, spectacleID),
	FOREIGN KEY (soireeID) REFERENCES Soiree(soireeID),
    FOREIGN KEY (spectacleID) REFERENCES Spectacle(spectacleID)
);

-- creation de la table user
CREATE TABLE User(
    userid INT(3) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(256) UNIQUE NOT NULL,
    mdp VARCHAR(256) NOT NULL,
    roleid INT(3) NOT NULL
);

-- creation de la table userspreferences
CREATE TABLE UsersPreferences(
    userID INT(2),
    spectacleID INT(2),
    PRIMARY KEY (userID,spectacleID),
    FOREIGN KEY (userID) REFERENCES User(userID),
    FOREIGN KEY (spectacleID) REFERENCES Spectacle(spectacleID)
);

-- insertion des données

INSERT INTO Lieu (nom, adresse) VALUES
('La Bellevilloise', '19-21 Rue Boyer, 75020 Paris'),
('Le Trianon', '80 Boulevard de Rochechouart, 75018 Paris'),
('Le Bataclan', '50 Boulevard Voltaire, 75011 Paris');

INSERT INTO StyleMusic (nomstyle) VALUES
('Blues'),
('Rock'),
('Reggae');

INSERT INTO Soiree (nom, thematique, tarif, date, lieuID) VALUES
('Soiree blues','Concert de blues',5.99,'2023-11-25 20:00', 1),
('Soiree Rock','Concert de rock',5.99,'2023-11-26 21:00', 2),
('Reggae night','Démo de reggae',5.99,'2023-11-27 22:00', 3);

INSERT INTO Spectacle (titre, groupe, duree, description, extrait, image, styleID, annuler) VALUES
('Blues Night', 'The Blue Cats', 120, 'A night full of deep blues music.', NULL, 'bluesnight.jpg', 1, FALSE),
('Rock Fest', 'The Rocking Stones', 150, 'Experience the ultimate rock and roll showdown.', NULL, 'rockfest.jpg', 2, FALSE),
('Reggae Rhythms', 'The Reggae Beats', 130, 'Chill to the soothing sounds of reggae.', NULL, 'reggaerythms.jpg', 3, TRUE);

INSERT INTO SoireeToSpectacle (soireeID, spectacleID) VALUES
(1, 1),
(2, 2),
(3, 3);

INSERT INTO User (userid, email, mdp, roleid) VALUES
(1, 'user1@mail.com', '$2y$12$1DNY3EAleSmszEDaIc1Wde08ZWct.yL9zrC7miePmNV/2TMIiu/SG', 1),
(2, 'admin@mail.com', '$2y$12$YGXb1CjuNpDjls4PFVw3M.qgoi5ZNRzWiab/CW0yjfAH82Ya1f492', 99);

INSERT INTO UsersPreferences (userID, spectacleID) VALUES
(1, 1),
(1, 2);
