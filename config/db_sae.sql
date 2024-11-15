-- creation de la table lieu
 DROP TABLE IF EXISTS `Lieu`;
CREATE TABLE Lieu(
	lieuID INT(3) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    capacite INT(5)
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

INSERT INTO Lieu (nom, adresse, capacite) VALUES
('La Bellevilloise', '19-21 Rue Boyer, 54000 Nancy', 1490),
('Le Trianon', '80 Boulevard de Rochechouart, 54000 Nancy', 1090),
('Le Bataclan', '50 Boulevard Voltaire, 54000 Nancy', 1500),
('Le Point Éphémère', '200 Quai de Valmy, 54000 Nancy', 800);

INSERT INTO StyleMusic (nomstyle) VALUES
('Blues'),
('Rock'),
('Reggae'),
('Jazz'),
('Électro'),
('Hip Hop');

INSERT INTO Soiree (nom, thematique, tarif, date, lieuID) VALUES
('Soirée Blues', 'Concert de blues', 5.99, '2025-06-01 20:00:00', 1),
('Soirée Rock', 'Concert de rock', 5.99, '2025-06-01 21:00:00', 2),
('Nuit Reggae', 'Démo de reggae', 5.99, '2025-06-02 20:00:00', 3),
('Nuit Jazz', 'Soirée de jazz doux', 10.00, '2025-06-02 19:30:00', 4),
('Rythme Électro', 'Fête de musique électronique', 15.00, '2025-06-03 21:30:00', 2),
('Batailles Hip Hop', 'Batailles de musique et de danse hip hop', 12.00, '2025-06-04 20:00:00', 1);

INSERT INTO Spectacle (titre, groupe, duree, description, extrait, image, styleID, annuler) VALUES
('Nuit de Blues', 'B.B. King', 120, 'Plongez dans une soirée de blues profond avec le légendaire B.B. King. Vivez une narration émouvante à travers sa musique.', 'https://youtu.be/SgXSomPE_FY?si=9OuFJBlaEqorfwKU', 'bluesnight.jpg', 1, FALSE),
('Fête Delta Blues', 'The Black Keys', 120, 'Profitez d''un mélange unique de blues Delta traditionnel enrichi de rythmes et de sons modernes, apportant une touche novatrice à ce genre classique.', 'https://youtu.be/M513zr-J5Cg?si=q4JVnkhUX3PPWvsu', 'deltabluesbash.png', 1, FALSE),
('Festival de Rock', 'The Rolling Stones', 150, 'Préparez-vous à rocker au Festival de Rock avec The Rolling Stones. Attendez-vous à des performances énergiques comprenant à la fois des classiques et de nouveaux hymnes.', 'https://youtu.be/O4irXQhgMqg?si=6JL904WDW6GqdFxY', 'rockfest.jpg', 2, FALSE),
('Héros de la Guitare', 'Guns N'' Roses', 110, 'Guns N'' Roses monte sur scène pour livrer des solos de guitare époustouflants et des hymnes rock intemporels.', 'https://youtu.be/1w7OgIMMRc4?si=dtLvggDcwsne7g5S', 'guitarheroes.png', 2, FALSE),
('Rythmes Reggae', 'Bob Marley and The Wailers', 130, 'Détendez-vous et relaxez-vous avec Bob Marley and The Wailers, qui apportent des airs de reggae doux qui résonnent avec les rythmes des Caraïbes.', 'https://youtu.be/1ti2YCFgCoI?si=MjdvE3X9mgGJKIpD', 'reggaerythms.jpg', 3, TRUE),
('Reggae au Coucher du Soleil', 'Sean Paul', 85, 'Immergez-vous dans les rythmes insulaires décontractés alors que Sean Paul interprète des hits reggae contemporains sur fond de coucher de soleil.', 'https://youtu.be/dW2MmuA1nI4?si=UOBJpIXpEeBvKBOh', 'sunsetreggae.jpg', 3, FALSE),
('Jazz à Paris', 'Miles Davis', 90, 'Passez une soirée avec Miles Davis alors qu''il mélange le jazz classique avec des influences modernes, créant une nuit de jazz parisienne parfaite.', 'https://youtu.be/grLYFVrV81I?si=QzRVxBXdU7VX7GD9', 'jazzinparis.png', 4, FALSE),
('Festival Fusion Jazz', 'Herbie Hancock', 150, 'Herbie Hancock présente une soirée de performances de fusion jazz innovantes, parfaites pour les amateurs souhaitant explorer au-delà du jazz traditionnel.', 'https://youtu.be/GHhD4PD75zY?si=J9RcCC7iA_quF5Lv', 'jazzfusionfest.jpg', 4, FALSE),
('Nuit Électro', 'David Guetta', 180, 'Rejoignez David Guetta pour une nuit de beats électro pulsants et un spectacle de lumières vif qui vous feront danser jusqu''à l''aube.', 'https://youtu.be/NUVCQXMUVnI?si=9iS3-spNcj0a5Kkr', 'electronight.png', 5, FALSE),
('Nuit Techno', 'Daft Punk', 125, 'Vivez un spectacle spectaculaire de musique techno de pointe avec Daft Punk, présentant des beats profonds et un affichage audio-visuel captivant.', 'https://youtu.be/a5uQMwRMHcs?si=xXJPBTFHzMa5CTQP', 'technonight.jpg', 5, FALSE),
('Affrontement Hip Hop', 'Kanye West', 150, 'Kanye West apporte son meilleur jeu avec des paroles puissantes, des beats dynamiques, et une représentation électrisante de danse de rue.', 'https://youtu.be/6CHs4x2uqcQ?si=yZmq_ROB6dlLeFgb', 'hiphopshowdown.png', 6, FALSE),
('Bataille de Rap', 'Eminem', 90, 'Assistez à des batailles de rap intenses organisées par Eminem, où les meilleurs MCs s''affrontent avec leurs paroles les plus féroces et leurs rimes les plus aiguës.', 'https://youtu.be/S9bCLPwzSC0?si=JZB9oXmTEGuS3SNH', 'rapbattle.jpg', 6, FALSE);

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