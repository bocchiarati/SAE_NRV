-- creation de la table lieu
CREATE TABLE Lieu(
	lieuID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL
);

-- creation de la table soiree
CREATE TABLE Soiree(
	soireeID INT(3) AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
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
    roleid INT(2) NOT NULL
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

INSERT INTO Soiree (date, lieuID) VALUES
('2023-11-25', 1),
('2023-11-26', 2),
('2023-11-27', 3);

INSERT INTO Spectacle (titre, groupe, duree, description, extrait, image, styleID) VALUES
('Blues Night', 'The Blue Cats', 120, 'A night full of deep blues music.', NULL, 'images/bluesnight.jpg', 1),
('Rock Fest', 'The Rocking Stones', 150, 'Experience the ultimate rock and roll showdown.', NULL, 'images/rockfest.jpg', 2),
('Reggae Rhythms', 'The Reggae Beats', 130, 'Chill to the soothing sounds of reggae.', NULL, 'images/reggaerythms.jpg', 3);

INSERT INTO SoireeToSpectacle (soireeID, spectacleID) VALUES
(1, 1),
(2, 2),
(3, 3);

INSERT INTO User (userid, email, mdp, roleid) VALUES
(1, 'user1@mail.com', '$2y$12$ap6NX0Ps8QgsgFUg.W5R0Ow', 1);

INSERT INTO UsersPreferences (userID, spectacleID) VALUES
(1, 1),
(1, 2);
