DROP DATABASE IF EXISTS `ESQLDB`;

CREATE DATABASE IF NOT EXISTS `ESQLDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `ESQLDB`;

-- Tabella Utente
CREATE TABLE IF NOT EXISTS
    Utente (
        email VARCHAR(100) PRIMARY KEY,
        nome VARCHAR(50) NOT NULL,
        cognome VARCHAR(50) NOT NULL,
        password VARCHAR(100) NOT NULL,
        telefono VARCHAR(20)
    );

-- Tabella Studente
CREATE TABLE IF NOT EXISTS
    Studente (
        email_studente VARCHAR(100) PRIMARY KEY,
        FOREIGN KEY (email_studente) REFERENCES Utente (email) ON DELETE CASCADE,
        anno_immatricolazione INT NOT NULL
    );

-- Tabella Professore
CREATE TABLE IF NOT EXISTS
    Professore (
        email_professore VARCHAR(100) PRIMARY KEY,
        FOREIGN KEY (email_professore) REFERENCES Utente (email) ON DELETE CASCADE,
        dipartimento VARCHAR(100) NOT NULL
    );

-- Inserimento dati nella tabella Utente
INSERT INTO
    Utente (email, nome, cognome, password, telefono)
VALUES
    (
        'studente1@example.com',
        'Mario',
        'Rossi',
        'password123',
        '1234567890'
    ),
    (
        'studente2@example.com',
        'Luca',
        'Bianchi',
        'pass123',
        '0987654321'
    ),
    (
        'professore1@example.com',
        'Anna',
        'Verdi',
        'securepass',
        '555666777'
    ),
    (
        'professore2@example.com',
        'Carlo',
        'Neri',
        'supersecret',
        '999888777'
    );

-- Inserimento dati nella tabella Studente
INSERT INTO
    Studente (email_studente, anno_immatricolazione)
VALUES
    ('studente1@example.com', 2019),
    ('studente2@example.com', 2020);

-- Inserimento dati nella tabella Professore
INSERT INTO
    Professore (email_professore, dipartimento)
VALUES
    ('professore1@example.com', 'Informatica'),
    ('professore2@example.com', 'Matematica');

-- segnaposto

-- Procedura di registrazione studente
DELIMITER //

-- Procedura di login studente
CREATE PROCEDURE `authenticate_user`(IN p_email VARCHAR(100), IN p_password VARCHAR(100))
BEGIN
    SELECT * FROM Utente WHERE email = p_email AND password = p_password;
END //

DELIMITER ;

-- Procedura di registrazione studente
DELIMITER //
CREATE PROCEDURE `InserisciNuovoStudente`(
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(50),
    IN p_cognome VARCHAR(50),
    IN p_password VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_anno_immatricolazione INT
)
BEGIN
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    DECLARE exit handler for sqlwarning
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Inserisce l'utente nella tabella Utente
    INSERT INTO Utente (email, nome, cognome, password, telefono)
    VALUES (p_email, p_nome, p_cognome, p_password, p_telefono);

    -- Inserisce lo studente nella tabella Studente
    INSERT INTO Studente (email_studente, anno_immatricolazione)
    VALUES (p_email, p_anno_immatricolazione);

    COMMIT;
END //

DELIMITER ;

-- Procedura di registrazione professore
DELIMITER //
CREATE PROCEDURE `InserisciNuovoProfessore`(
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(50),
    IN p_cognome VARCHAR(50),
    IN p_password VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_dipartimento VARCHAR(100),
    IN p_corso VARCHAR(100)
)
BEGIN
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    DECLARE exit handler for sqlwarning
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Inserisce l'utente nella tabella Utente
    INSERT INTO Utente (email, nome, cognome, password, telefono)
    VALUES (p_email, p_nome, p_cognome, p_password, p_telefono);

    -- Inserisce il professore nella tabella Professore
    INSERT INTO Professore (email_professore, dipartimento, corso)
    VALUES (p_email, p_dipartimento, p_corso);

    COMMIT;
END //

DELIMITER ;
