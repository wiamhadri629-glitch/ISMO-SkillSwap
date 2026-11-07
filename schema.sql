CREATE DATABASE IF NOT EXISTS skillswap;
USE skillswap;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(45),
    prenom VARCHAR(45),
    email VARCHAR(100) UNIQUE,
    motdepasse VARCHAR(255),
    role ENUM('stagiaire','mentor','formateur','admin') DEFAULT 'stagiaire',
    score INT DEFAULT 0,
    disponible TINYINT(1) DEFAULT 1,
    photo VARCHAR(255),
    filiere VARCHAR(100),
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    valide TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS competences (
    id_competence INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    categorie VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS user_competences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    id_competence INT,
    niveau ENUM('debutant','intermediaire','avance'),
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_competence) REFERENCES competences(id_competence) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS validation_competence (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user_competence INT,
    id_formateur INT,
    status ENUM('valide','refuse'),
    date_validation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user_competence) REFERENCES user_competences(id) ON DELETE CASCADE,
    FOREIGN KEY (id_formateur) REFERENCES utilisateurs(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS demandes_aide (
    id_demande INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    titre VARCHAR(150),
    description TEXT,
    status ENUM('ouverte','en_cours','resolue') DEFAULT 'ouverte',
    date_pub DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS propositions_aide (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_demande INT,
    id_mentor INT,
    id_formateur INT,
    message TEXT,
    status ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    date_proposition DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_demande) REFERENCES demandes_aide(id_demande) ON DELETE CASCADE,
    FOREIGN KEY (id_mentor) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_formateur) REFERENCES utilisateurs(id_user) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS aide_effectuee (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_demande INT,
    id_mentor INT,
    date_intervention DATETIME DEFAULT CURRENT_TIMESTAMP,
    note_mentor INT,
    commentaire TEXT,
    FOREIGN KEY (id_demande) REFERENCES demandes_aide(id_demande) ON DELETE CASCADE,
    FOREIGN KEY (id_mentor) REFERENCES utilisateurs(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS badges (
    id_badge INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100),
    points_requis INT,
    image VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS obtention_badges (
    id_user INT NOT NULL,
    id_badge INT NOT NULL,
    date_obtention DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_badge),
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_badge) REFERENCES badges(id_badge) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS aides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_formateur INT,
    id_stagiaire_validateur INT,
    titre VARCHAR(255),
    description TEXT,
    explication TEXT,
    statut ENUM('proposee','validee') DEFAULT 'proposee',
    date_creation DATETIME,
    date_validation DATETIME,
    FOREIGN KEY (id_formateur) REFERENCES utilisateurs(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_stagiaire_validateur) REFERENCES utilisateurs(id_user) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS demandes_promotion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_stagiaire INT NOT NULL,
    nb_aides INT DEFAULT 0,
    note_moyenne DECIMAL(3,2) DEFAULT 0,
    statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_traitement DATETIME,
    id_formateur INT,
    FOREIGN KEY (id_stagiaire) REFERENCES utilisateurs(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_formateur) REFERENCES utilisateurs(id_user) ON DELETE SET NULL
);
