-- Active: 1708708519055@@localhost@3306@ESQLDB
DROP DATABASE IF EXISTS `ESQLDB`;

CREATE DATABASE IF NOT EXISTS `ESQLDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `ESQLDB`;

-- Tabella Utente
CREATE TABLE IF NOT EXISTS
    Utente (
        email VARCHAR(100) PRIMARY KEY,
        nome VARCHAR(50) NOT NULL     ,
        cognome VARCHAR(50) NOT NULL  ,
        PASSWORD VARCHAR(100) NOT NULL,
        telefono VARCHAR(20)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

-- Tabella Studente
CREATE TABLE IF NOT EXISTS
    Studente (
        email_studente VARCHAR(100) PRIMARY KEY                                ,
        codice_alfanumerico VARCHAR(16) NOT NULL                               ,
        anno_immatricolazione INT NOT NULL                                     ,
        FOREIGN KEY (email_studente) REFERENCES Utente (email) ON DELETE CASCADE
    );

-- Tabella Professore
CREATE TABLE IF NOT EXISTS
    Professore (
        email_professore VARCHAR(100) PRIMARY KEY                                ,
        dipartimento VARCHAR(100) NOT NULL                                       ,
        corso VARCHAR(100) NOT NULL                                              ,
        FOREIGN KEY (email_professore) REFERENCES Utente (email) ON DELETE CASCADE
    );

-- Inserimento dati nella tabella Utente
INSERT INTO
    Utente (email, nome, cognome, PASSWORD, telefono)
VALUES
    (
        'studente1@example.com',
        'Mario'                ,
        'Rossi'                ,
        'password123'          ,
        '12324567'
    ),
    (
        'studente2@example.com',
        'Luca'                 ,
        'Bianchi'              ,
        'pass123'              ,
        '12324567'
    ),
    (
        'vincenzo.scollo@example.com',
        'Anna'                       ,
        'Verdi'                      ,
        'securepass'                 ,
        '12324567'
    ),
    (
        'mariagrazia.fabbri@example.com',
        'Carlo'                         ,
        'Neri'                          ,
        'supersecret'                   ,
        '12324567'
    ),
    (
        'simosamoggia@gmail.com',
        'Simone'                ,
        'Samoggia'              ,
        '1234'                  ,
        '12324567'
    );

-- Inserimento dati nella tabella Studente
INSERT INTO
    Studente (
        email_studente       ,
        anno_immatricolazione,
        codice_alfanumerico
    )
VALUES
    ('studente1@example.com', 2019, '0123456789') ,
    ('studente2@example.com', 2020, '0123456787') ,
    ('simosamoggia@gmail.com', 2020, '0123456787');

-- Inserimento dati nella tabella Professore
INSERT INTO
    Professore (email_professore, dipartimento, corso)
VALUES
    (
        'vincenzo.scollo@example.com',
        'Informatica'                ,
        'scienze applicate'
    ),
    (
        'mariagrazia.fabbri@example.com',
        'Matematica'                    ,
        'scienze applicate'
    );

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `authenticateUser` (
    IN p_email VARCHAR(100)  ,
    IN p_password VARCHAR(100)
) BEGIN
SELECT
    *
FROM
    Utente
WHERE
    email = p_email
    AND PASSWORD = p_password;

END $$ DELIMITER;

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoStudente` (
    IN p_email VARCHAR(100)             ,
    IN p_nome VARCHAR(50)               ,
    IN p_cognome VARCHAR(50)            ,
    IN p_password VARCHAR(100)          ,
    IN p_telefono VARCHAR(20)           ,
    IN p_codice_alfanumerico VARCHAR(16),
    IN p_anno_immatricolazione INT
) BEGIN DECLARE EXIT
HANDLER FOR SQLEXCEPTION BEGIN
ROLLBACK;

RESIGNAL;

END;

DECLARE EXIT
HANDLER FOR SQLWARNING BEGIN
ROLLBACK;

RESIGNAL;

END;

START TRANSACTION;

-- Inserisce l'utente nella tabella Utente
INSERT INTO
    Utente (email, nome, cognome, PASSWORD, telefono)
VALUES
    (
        p_email   ,
        p_nome    ,
        p_cognome ,
        p_password,
        p_telefono
    );

-- Inserisce lo studente nella tabella Studente
INSERT INTO
    Studente (
        email_studente      ,
        codice_alfanumerico ,
        anno_immatricolazione
    )
VALUES
    (
        p_email               ,
        p_codice_alfanumerico ,
        p_anno_immatricolazione
    );

COMMIT;

END $$ DELIMITER;

-- Procedura di registrazione professore
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoProfessore` (
    IN p_email VARCHAR(100)       ,
    IN p_nome VARCHAR(50)         ,
    IN p_cognome VARCHAR(50)      ,
    IN p_password VARCHAR(100)    ,
    IN p_telefono VARCHAR(20)     ,
    IN p_dipartimento VARCHAR(100),
    IN p_corso VARCHAR(100)
) BEGIN DECLARE EXIT
HANDLER FOR SQLEXCEPTION BEGIN
ROLLBACK;

RESIGNAL;

END;

DECLARE EXIT
HANDLER FOR SQLWARNING BEGIN
ROLLBACK;

RESIGNAL;

END;

START TRANSACTION;

-- Inserisce l'utente nella tabella Utente
INSERT INTO
    Utente (email, nome, cognome, PASSWORD, telefono)
VALUES
    (
        p_email   ,
        p_nome    ,
        p_cognome ,
        p_password,
        p_telefono
    );

-- Inserisce il professore nella tabella Professore
INSERT INTO
    Professore (email_professore, dipartimento, corso)
VALUES
    (p_email, p_dipartimento, p_corso);

COMMIT;

END $$ DELIMITER;

-- Procedura per verificare se l'email appartiene a uno studente o a un professore
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `VerificaTipoUtente` (IN p_email VARCHAR(100)) BEGIN DECLARE is_studente INT DEFAULT 0;

DECLARE is_professore INT DEFAULT 0;

-- Controlla se l'email è presente nella tabella Studente
SELECT
    COUNT(*) INTO is_studente
FROM
    Studente
WHERE
    email_studente = p_email;

-- Controlla se l'email è presente nella tabella Professore
SELECT
    COUNT(*) INTO is_professore
FROM
    Professore
WHERE
    email_professore = p_email;

-- Restituisce il tipo di utente in base ai risultati ottenuti
IF is_studente > 0 THEN
SELECT
    'Studente' AS Ruolo;

ELSEIF is_professore > 0 THEN
SELECT
    'Professore' AS Ruolo;

ELSE
SELECT
    'Nessun utente trovato' AS Ruolo;

END IF;

END $$ DELIMITER;

-- procedura per creare una nuova tabella di esercizio
-- trigger per aggiornare il numero di righe
-- CREATE TRIGGER update_num_righe
-- AFTER
-- INSERT ON esercizio_attributi FOR EACH ROW
-- UPDATE esercizio
-- SET num_righe = num_righe + 1
-- WHERE nome = NEW.esercizio;
-- crea tabella esercizio
-- DELIMITER $$
-- CREATE PROCEDURE IF NOT EXISTS crea_tabella_di_esercizio (
--     IN nome_tabella VARCHAR(255),
--     IN data_creazione DATE      ,
--     IN num_righe INT
-- ) BEGIN DECLARE CONTINUE
-- HANDLER FOR SQLEXCEPTION BEGIN -- Gestisci eventuali errori
-- ROLLBACK;
-- END;
-- START TRANSACTION;
-- CREATE TABLE IF NOT EXISTS
--     tabella_di_esercizio (
--         nome VARCHAR(255) NOT NULL      ,
--         data_creazione DATE NOT null    ,
--         num_righe INT DEFAULT 0 NOT NULL,
--         PRIMARY KEY (nome)
--     );
-- INSERT INTO
--     tabella_di_esercizio (nome, num_righe)
-- VALUES
--     (nome_tabella, num_righe);
-- COMMIT;
-- END $$ DELIMITER;
-- crea tabella per attributi
-- DELIMITER $$
-- CREATE PROCEDURE IF NOT EXISTS crea_tabella_attributi (
--     IN id_attributo INT               ,
--     IN nome_attributo VARCHAR(255)    ,
--     IN tipo_attributo VARCHAR(255)    ,
--     IN esercizio_associato VARCHAR(255)
-- ) BEGIN DECLARE CONTINUE
-- HANDLER FOR SQLEXCEPTION BEGIN -- Gestisci eventuali errori
-- ROLLBACK;
-- END;
-- START TRANSACTION;
-- CREATE TABLE IF NOT EXISTS
--     attributo (
--         id INT AUTO_INCREMENT NOT NULL                               ,
--         nome VARCHAR(255) NOT NULL                                   ,
--         tipo VARCHAR(255) NOT NULL                                   ,
--         esercizio VARCHAR(255) NOT NULL                              ,
--         PRIMARY KEY (id)                                             ,
--         FOREIGN KEY (esercizio) REFERENCES tabella_di_esercizio (nome)
--     );
-- INSERT INTO
--     attributo (id, nome, tipo, esercizio)
-- VALUES
--     (
--         id_attributo      ,
--         nome_attributo    ,
--         tipo_attributo    ,
--         esercizio_associato
--     );
-- COMMIT;
-- END $$ DELIMITER;
-- -- crea relazione tra attributi e esercizio
-- DELIMITER $$
-- CREATE PROCEDURE IF NOT EXISTS setAttributoComeChiavePrimaria (IN tabella_id INT, IN attributo_id INT) BEGIN DECLARE CONTINUE
-- HANDLER FOR SQLEXCEPTION BEGIN -- Gestisci eventuali errori
-- ROLLBACK;
-- END;
-- START TRANSACTION;
-- CREATE TABLE IF NOT EXISTS
--     AttributiChiavePrimaria (
--         tabella_id INT                                               ,
--         attributo_id INT                                             ,
--         PRIMARY KEY (tabella_id, attributo_id)                       ,
--         FOREIGN KEY (tabella_id) REFERENCES tabella_di_esercizio (id),
--         FOREIGN KEY (attributo_id) REFERENCES attributo (id)
--     );
-- INSERT INTO
--     AttributiChiavePrimaria (tabella_id, attributo_id)
-- VALUES
--     (tabella_id, attributo_id);
-- COMMIT;
-- END $$ DELIMITER;
-- trigger per aggiungere la data all'aggiunta di una riga nella tabella di esercizio
-- DELIMITER $$
-- CREATE TRIGGER IF NOT EXISTS update_tabella_di_esercizio BEFORE
-- INSERT
--     ON tabella_di_esercizio FOR EACH ROW BEGIN
-- SET
--     NEW.data_creazione = NOW();
-- SET
--     NEW.num_righe = (
--         SELECT
--             COUNT(*)
--         FROM
--             tabella_di_esercizio
--     );
-- END $$ DELIMITER
;

-- crea tabella dei TEST
CREATE TABLE IF NOT EXISTS
    Test (
        titolo VARCHAR(100) PRIMARY KEY                                                      ,
        dataCreazione DATETIME DEFAULT NOW() NOT NULL                                        ,
        -- TINYINT viene utilizzato come BOOLEAN, dato che MySQL non supporta il tipo BOOLEAN 
        VisualizzaRisposte TINYINT(1) DEFAULT 0 NOT NULL
    );

-- crea tabella delle foto dei test
CREATE TABLE IF NOT EXISTS
    Foto_test (
        foto LONGBLOB NOT NULL                                             ,
        titolo_test VARCHAR(100) NOT NULL                                  ,
        PRIMARY KEY (titolo_test)                                          ,
        FOREIGN KEY (titolo_test) REFERENCES Test (titolo) ON DELETE CASCADE
    );

-- crea procedura per inserire un nuovo TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoTest` (
    IN p_titolo VARCHAR(100)      ,
    IN p_VisualizzaRisposte BOOLEAN
) BEGIN DECLARE EXIT
HANDLER FOR SQLEXCEPTION BEGIN
ROLLBACK;

RESIGNAL;

END;

DECLARE EXIT
HANDLER FOR SQLWARNING BEGIN
ROLLBACK;

RESIGNAL;

END;

START TRANSACTION;

-- Inserisce il test nella tabella Test
INSERT INTO
    Test (titolo, VisualizzaRisposte)
VALUES
    (p_titolo, p_VisualizzaRisposte);

COMMIT;

END $$ DELIMITER;

-- crea procedura per inserire una nuova foto di un test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovaFotoTest` (IN p_foto LONGBLOB, IN p_titolo_test VARCHAR(100)) BEGIN DECLARE EXIT
HANDLER FOR SQLEXCEPTION BEGIN
ROLLBACK;

RESIGNAL;

END;

DECLARE EXIT
HANDLER FOR SQLWARNING BEGIN
ROLLBACK;

RESIGNAL;

END;

START TRANSACTION;

-- Inserisce la foto del test nella tabella Foto_test
INSERT INTO
    Foto_test (foto, titolo_test)
VALUES
    (p_foto, p_titolo_test);

COMMIT;

END $$ DELIMITER;

-- procedura per restituire l'immagine di un TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `RecuperaFotoTest` (IN p_titolo_test VARCHAR(100)) BEGIN
SELECT
    foto
FROM
    Foto_test
WHERE
    titolo_test = p_titolo_test;

END $$ DELIMITER;