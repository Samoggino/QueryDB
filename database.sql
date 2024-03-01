-- Active: 1708708519055@@localhost@3306@ESQLDB
DROP DATABASE IF EXISTS `ESQLDB`;
CREATE DATABASE IF NOT EXISTS `ESQLDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ESQLDB`;
-- Tabella Utente
CREATE TABLE IF NOT EXISTS Utente (
    email VARCHAR(100) PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    PASSWORD VARCHAR(100) NOT NULL,
    telefono VARCHAR(20)
);
-- Tabella Studente
CREATE TABLE IF NOT EXISTS Studente (
    email_studente VARCHAR(100) PRIMARY KEY,
    codice_alfanumerico VARCHAR(16) NOT NULL,
    anno_immatricolazione INT NOT NULL,
    FOREIGN KEY (email_studente) REFERENCES Utente (email) ON DELETE CASCADE
);
-- Tabella Professore
CREATE TABLE IF NOT EXISTS Professore (
    email_professore VARCHAR(100) PRIMARY KEY,
    dipartimento VARCHAR(100) NOT NULL,
    corso VARCHAR(100) NOT NULL,
    FOREIGN KEY (email_professore) REFERENCES Utente (email) ON DELETE CASCADE
);
-- Inserimento dati nella tabella Utente
INSERT INTO Utente (email, nome, cognome, PASSWORD, telefono)
VALUES (
        'studente1@example.com',
        'Mario',
        'Rossi',
        'password123',
        '12324567'
    ),
    (
        'studente2@example.com',
        'Luca',
        'Bianchi',
        'pass123',
        '12324567'
    ),
    (
        'vincenzo.scollo@example.com',
        'Anna',
        'Verdi',
        'securepass',
        '12324567'
    ),
    (
        'mariagrazia.fabbri@example.com',
        'Carlo',
        'Neri',
        'supersecret',
        '12324567'
    ),
    (
        'simosamoggia@gmail.com',
        'Simone',
        'Samoggia',
        '1234',
        '12324567'
    );
-- Inserimento dati nella tabella Studente
INSERT INTO Studente (
        email_studente,
        anno_immatricolazione,
        codice_alfanumerico
    )
VALUES ('studente1@example.com', 2019, '0123456789'),
    ('studente2@example.com', 2020, '0123456787'),
    ('simosamoggia@gmail.com', 2020, '0123456787');
-- Inserimento dati nella tabella Professore
INSERT INTO Professore (email_professore, dipartimento, corso)
VALUES (
        'vincenzo.scollo@example.com',
        'Informatica',
        'scienze applicate'
    ),
    (
        'mariagrazia.fabbri@example.com',
        'Matematica',
        'scienze applicate'
    );
-- Procedura di registrazione studente
DELIMITER // CREATE PROCEDURE IF NOT EXISTS `authenticateUser` (
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(100)
) BEGIN
SELECT *
FROM Utente
WHERE email = p_email
    AND PASSWORD = p_password;
END // DELIMITER;
-- Procedura di registrazione studente
DELIMITER // CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoStudente` (
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(50),
    IN p_cognome VARCHAR(50),
    IN p_password VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_codice_alfanumerico VARCHAR(16),
    IN p_anno_immatricolazione INT
) BEGIN
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
RESIGNAL;
END;
DECLARE EXIT HANDLER FOR SQLWARNING BEGIN ROLLBACK;
RESIGNAL;
END;
START TRANSACTION;
-- Inserisce l'utente nella tabella Utente
INSERT INTO Utente (email, nome, cognome, PASSWORD, telefono)
VALUES (
        p_email,
        p_nome,
        p_cognome,
        p_password,
        p_telefono
    );
-- Inserisce lo studente nella tabella Studente
INSERT INTO Studente (
        email_studente,
        codice_alfanumerico,
        anno_immatricolazione
    )
VALUES (
        p_email,
        p_codice_alfanumerico,
        p_anno_immatricolazione
    );
COMMIT;
END // DELIMITER;
-- Procedura di registrazione professore
DELIMITER // CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoProfessore` (
    IN p_email VARCHAR(100),
    IN p_nome VARCHAR(50),
    IN p_cognome VARCHAR(50),
    IN p_password VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_dipartimento VARCHAR(100),
    IN p_corso VARCHAR(100)
) BEGIN
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
RESIGNAL;
END;
DECLARE EXIT HANDLER FOR SQLWARNING BEGIN ROLLBACK;
RESIGNAL;
END;
START TRANSACTION;
-- Inserisce l'utente nella tabella Utente
INSERT INTO Utente (email, nome, cognome, PASSWORD, telefono)
VALUES (
        p_email,
        p_nome,
        p_cognome,
        p_password,
        p_telefono
    );
-- Inserisce il professore nella tabella Professore
INSERT INTO Professore (email_professore, dipartimento, corso)
VALUES (p_email, p_dipartimento, p_corso);
COMMIT;
END // DELIMITER;
-- Procedura per verificare se l'email appartiene a uno studente o a un professore
DELIMITER // CREATE PROCEDURE IF NOT EXISTS `VerificaTipoUtente` (IN p_email VARCHAR(100)) BEGIN
DECLARE is_studente INT DEFAULT 0;
DECLARE is_professore INT DEFAULT 0;
-- Controlla se l'email è presente nella tabella Studente
SELECT COUNT(*) INTO is_studente
FROM Studente
WHERE email_studente = p_email;
-- Controlla se l'email è presente nella tabella Professore
SELECT COUNT(*) INTO is_professore
FROM Professore
WHERE email_professore = p_email;
-- Restituisce il tipo di utente in base ai risultati ottenuti
IF is_studente > 0 THEN
SELECT 'Studente' AS Ruolo;
ELSEIF is_professore > 0 THEN
SELECT 'Professore' AS Ruolo;
ELSE
SELECT 'Nessun utente trovato' AS Ruolo;
END IF;
END // DELIMITER;