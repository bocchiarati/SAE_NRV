-- creation de la table lieu
 DROP TABLE IF EXISTS `Lieu`;
CREATE TABLE Lieu(
	lieuID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL
);

-- creation de la table soiree
 DROP TABLE IF EXISTS `Soiree`;

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
 DROP TABLE IF EXISTS `StyleMusic`;

CREATE TABLE StyleMusic(
    styleID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nomstyle VARCHAR(30) NOT NULL
);

-- creation de la table spectacle
 DROP TABLE IF EXISTS `Spectacle`;

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
 DROP TABLE IF EXISTS `SoireeToSpectacle`;

CREATE TABLE SoireeToSpectacle(
    soireeID INT(2),
    spectacleID INT(2),
    PRIMARY KEY (soireeID, spectacleID),
	FOREIGN KEY (soireeID) REFERENCES Soiree(soireeID),
    FOREIGN KEY (spectacleID) REFERENCES Spectacle(spectacleID)
);

-- creation de la table user
 DROP TABLE IF EXISTS `User`;

CREATE TABLE User(
    userid INT(3) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(256) UNIQUE NOT NULL,
    mdp VARCHAR(256) NOT NULL,
    roleid INT(3) NOT NULL
);

-- creation de la table userspreferences
 DROP TABLE IF EXISTS `UsersPreferences`;

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
('Le Bataclan', '50 Boulevard Voltaire, 75011 Paris'),
('Le Point Éphémère', '200 Quai de Valmy, 75010 Paris');

INSERT INTO StyleMusic (nomstyle) VALUES
('Blues'),
('Rock'),
('Reggae'),
('Jazz'),
('Electro'),
('Hip Hop');

INSERT INTO Soiree (nom, thematique, tarif, date, lieuID) VALUES
('Soiree blues','Concert de blues',5.99,'2025-06-01 20:00:00', 1),
('Soiree Rock','Concert de rock',5.99,'2025-06-01 21:00:00', 2),
('Reggae night','Démo de reggae',5.99,'2025-06-02 20:00:00', 3),
('Jazz Night', 'Night of smooth jazz', 10.00, '2025-06-02 19:30:00', 4),
('Electro Beat', 'Electronic music party', 15.00, '2025-06-03 21:30:00', 2),
('Hip Hop Battles', 'Hip hop music and dance battles', 12.00, '2023-12-03 20:00', 1);

INSERT INTO Spectacle (titre, groupe, duree, description, extrait, image, styleID, annuler) VALUES
('Blues Night', 'The Blue Cats', 120, 'Dive into an evening of soul-stirring blues performed by the legendary The Blue Cats. Experience heartfelt storytelling through music.', NULL, 'bluesnight.jpg', 1, FALSE),
('Delta Blues Bash', 'Mississippi Group', 120, 'Enjoy a unique blend of traditional Delta blues infused with modern rhythms and sounds, bringing a fresh twist to this classic genre.', NULL, 'deltabluesbash.png', 1, FALSE),
('Rock Fest', 'The Rocking Stones', 150, 'Get ready to rock at the Rock Fest with The Rocking Stones. Expect high-energy performances featuring both classic hits and new anthems.', NULL, 'rockfest.jpg', 2, FALSE),
('Guitar Heroes', 'Axe Legends', 110, 'Axe Legends takes the stage to deliver breathtaking guitar solos and rock anthems that have stood the test of time.', NULL, 'guitarheroes.png', 2, FALSE),
('Reggae Rhythms', 'The Reggae Beats', 130, 'Relax and unwind with The Reggae Beats as they bring smooth reggae tunes that echo the rhythms of the Caribbean.', NULL, 'reggaerythms.jpg', 3, TRUE),
('Sunset Reggae', 'Island Beats', 85, 'Immerse yourself in the laid-back island rhythms as Island Beats performs contemporary reggae hits against a backdrop of a sunset.', NULL, 'sunsetreggae.jpg', 3, FALSE),
('Jazz in Paris', 'Jazz Masters', 90, 'Spend an evening with Jazz Masters as they blend classic jazz with modern influences, creating a perfect Parisian jazz night.', NULL, 'jazzinparis.png', 4, FALSE),
('Jazz Fusion Fest', 'Fusion Band', 150, 'Fusion Band presents an evening of experimental and boundary-pushing jazz fusion, perfect for enthusiasts looking to explore beyond traditional jazz.', NULL, 'jazzfusionfest.jpg', 4, FALSE),
('Electro Night', 'DJ Electro', 180, 'Join DJ Electro for a night of pulsating electronic beats and a vibrant light show that will keep you dancing till dawn.', NULL, 'electronight.png', 5, FALSE),
('Techno Night', 'Bass Masters', 125, 'Experience a spectacular showcase of cutting-edge techno music with Bass Masters, featuring deep beats and a mesmerizing audio-visual display.', NULL, 'technonight.jpg', 5, FALSE),
('Hip Hop Showdown', 'The Street Beats', 150, 'The Street Beats bring their A-game with powerful lyrics, dynamic beats, and an electrifying display of street dance.', NULL, 'hiphopshowdown.png', 6, FALSE),
('Rap Battle', 'Mic Warriors', 90, 'Witness intense rap battles hosted by Mic Warriors, where the best MCs clash with their fiercest lyrics and sharpest rhymes.', NULL, 'rapbattle.jpg', 6, FALSE);

INSERT INTO SoireeToSpectacle (soireeID, spectacleID) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 6),
(4, 7),
(4, 8),
(5, 9),
(5, 10),
(6, 11),
(6, 12);

INSERT INTO User (userid, email, mdp, roleid) VALUES
(1, 'user1@mail.com', '$2y$12$1DNY3EAleSmszEDaIc1Wde08ZWct.yL9zrC7miePmNV/2TMIiu/SG', 1),
(2, 'orga@mail.com', '$2y$12$lu3U6.BaK0GqjWh16gvo4u9iy5H0JDph/MKEVJEC5.6F5mOb.srDi', 98),
(3, 'admin@mail.com', '$2y$12$LnrhSzP.U.HuIE/M5qC.yOOjYzpHx.GnoBloV8uT1PLlQgp.wZgRi', 99);

INSERT INTO UsersPreferences (userID, spectacleID) VALUES
(1, 1),
(1, 2),
(3, 5);