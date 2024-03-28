DROP DATABASE IF EXISTS `ESQLDB`;

CREATE DATABASE IF NOT EXISTS `ESQLDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `ESQLDB`;

-- Tabella Utente
CREATE TABLE IF NOT EXISTS
    UTENTE (
        email VARCHAR(100) PRIMARY KEY                                        ,
        nome VARCHAR(50) NOT NULL                                             ,
        cognome VARCHAR(50) NOT NULL                                          ,
        PASSWORD VARCHAR(100) NOT NULL                                        ,
        tipo_utente ENUM('STUDENTE', 'PROFESSORE') DEFAULT 'STUDENTE' NOT NULL,
        telefono VARCHAR(20)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

-- Tabella Studente
CREATE TABLE IF NOT EXISTS
    STUDENTE (
        email_studente VARCHAR(100) PRIMARY KEY                                ,
        matricola VARCHAR(16) NOT NULL                                         ,
        anno_immatricolazione INT NOT NULL                                     ,
        FOREIGN KEY (email_studente) REFERENCES UTENTE (email) ON DELETE CASCADE
    );

-- Tabella Professore
CREATE TABLE IF NOT EXISTS
    PROFESSORE (
        email_professore VARCHAR(100) PRIMARY KEY                                ,
        dipartimento VARCHAR(100) NOT NULL                                       ,
        corso VARCHAR(100) NOT NULL                                              ,
        FOREIGN KEY (email_professore) REFERENCES UTENTE (email) ON DELETE CASCADE
    );

-- Inserimento dati nella tabella UTENTE
INSERT INTO
    UTENTE (
        email     ,
        nome      ,
        cognome   ,
        PASSWORD  ,
        telefono  ,
        tipo_utente
    )
VALUES
    (
        'studente1@example.com',
        'Mario'                ,
        'Rossi'                ,
        'password123'          ,
        '12324567'             ,
        'STUDENTE'
    ),
    (
        'studente2@example.com',
        'Luca'                 ,
        'Bianchi'              ,
        'pass123'              ,
        '12324567'             ,
        'STUDENTE'
    ),
    (
        'vincenzo.scollo@example.com',
        'Anna'                       ,
        'Verdi'                      ,
        'securepass'                 ,
        '12324567'                   ,
        'PROFESSORE'
    ),
    (
        'mariagrazia.fabbri@example.com',
        'Carlo'                         ,
        'Neri'                          ,
        'supersecret'                   ,
        '12324567'                      ,
        'PROFESSORE'
    ),
    (
        'simosamoggia@gmail.com',
        'Simone'                ,
        'Samoggia'              ,
        '1234'                  ,
        '12324567'              ,
        'STUDENTE'
    ),
    (
        'professore@unibo.it',
        'Professor'          ,
        'Oak'                ,
        '1234'               ,
        '12324567'           ,
        'PROFESSORE'
    ),
    (
        'studente@unibo.it',
        'Ash'              ,
        'Ketchum'          ,
        '1234'             ,
        '12324567'         ,
        'STUDENTE'
    );

-- Inserimento dati nella tabella Studente
INSERT INTO
    STUDENTE (email_studente, anno_immatricolazione, matricola)
VALUES
    ('studente1@example.com', 2019, '0123456789') ,
    ('studente2@example.com', 2020, '0123456787') ,
    ('simosamoggia@gmail.com', 2020, '0123456787'),
    ('studente@unibo.it', 2020, '0123456787');

-- Inserimento dati nella tabella Professore
INSERT INTO
    PROFESSORE (email_professore, dipartimento, corso)
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
    ),
    (
        'professore@unibo.it',
        'Matematica'         ,
        'Scienze applicate'
    );

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `authenticateUser` (
    IN p_email VARCHAR(100)  ,
    IN p_password VARCHAR(100)
) BEGIN
SELECT
    email ,
    nome  ,
    cognome
FROM
    UTENTE
WHERE
    email = p_email
    AND PASSWORD = p_password;

END $$ DELIMITER;

-- Procedura di registrazione studente
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoStudente` (
    IN p_email VARCHAR(100)      ,
    IN p_nome VARCHAR(50)        ,
    IN p_cognome VARCHAR(50)     ,
    IN p_password VARCHAR(100)   ,
    IN p_telefono VARCHAR(20)    ,
    IN p_matricola VARCHAR(16)   ,
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
    UTENTE (email, nome, cognome, PASSWORD, telefono)
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
    STUDENTE (email_studente, matricola, anno_immatricolazione)
VALUES
    (p_email, p_matricola, p_anno_immatricolazione);

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
    UTENTE (email, nome, cognome, PASSWORD, telefono)
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
    PROFESSORE (email_professore, dipartimento, corso)
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
    STUDENTE
WHERE
    email_studente = p_email;

-- Controlla se l'email è presente nella tabella Professore
SELECT
    COUNT(*) INTO is_professore
FROM
    PROFESSORE
WHERE
    email_professore = p_email;

-- Restituisce il tipo di utente in base ai risultati ottenuti
IF is_studente > 0 THEN
SELECT
    'STUDENTE' AS RUOLO;

ELSEIF is_professore > 0 THEN
SELECT
    'PROFESSORE' AS RUOLO;

ELSE
SELECT
    'Nessun utente trovato' AS RUOLO;

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
    TEST (
        titolo VARCHAR(100) PRIMARY KEY                                                      ,
        dataCreazione DATETIME DEFAULT NOW() NOT NULL                                        ,
        VisualizzaRisposte TINYINT(1) DEFAULT 0 NOT NULL                                     ,
        email_professore VARCHAR(100) NOT NULL                                               ,
        -- TINYINT viene utilizzato come BOOLEAN, dato che MySQL non supporta il tipo BOOLEAN 
        FOREIGN KEY (email_professore) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- crea tabella delle foto dei test
CREATE TABLE IF NOT EXISTS
    FOTO_TEST (
        foto LONGBLOB NOT NULL                                                ,
        test_associato VARCHAR(100) NOT NULL                                  ,
        PRIMARY KEY (test_associato)                                          ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE
    );

-- crea procedura per inserire un nuovo TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoTest` (
    IN p_titolo VARCHAR(100)         ,
    IN p_email_professore VARCHAR(100)
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
    TEST (titolo, email_professore)
VALUES
    (p_titolo, p_email_professore);

COMMIT;

END $$ DELIMITER;

-- crea procedura per inserire una nuova foto di un test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovaFotoTest` (
    IN p_foto LONGBLOB             ,
    IN p_test_associato VARCHAR(100)
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

-- Inserisce la foto del test nella tabella Foto_test
INSERT INTO
    FOTO_TEST (foto, test_associato)
VALUES
    (p_foto, p_test_associato);

COMMIT;

END $$ DELIMITER;

-- procedura per restituire l'immagine di un TEST
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `RecuperaFotoTest` (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    foto
FROM
    FOTO_TEST
WHERE
    test_associato = p_test_associato;

END $$ DELIMITER;

-- crea tabella dei QUESITI
CREATE TABLE IF NOT EXISTS
    QUESITO (
        numero_quesito INT NOT NULL, -- domanda 1, domanda 2 ... domanda n
        test_associato VARCHAR(100) NOT NULL                                      ,
        descrizione TEXT NOT NULL                                                 ,
        livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO') DEFAULT 'BASSO' NOT NULL,
        numero_risposte INT NOT NULL DEFAULT 0                                    ,
        tipo_quesito ENUM('APERTO', 'CHIUSO') DEFAULT 'APERTO' NOT NULL           ,
        PRIMARY KEY (numero_quesito, test_associato)                              ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE
    );

-- OPZIONE QUESITO CHIUSO
CREATE TABLE IF NOT EXISTS
    QUESITO_CHIUSO_OPZIONE (
        numero_opzione INT, -- opzione a, opzione b ... opzione z  
        numero_quesito INT NOT NULL, -- domanda 1, domanda 2 ... domanda n
        test_associato VARCHAR(100) NOT NULL                                             ,
        testo TEXT NOT NULL                                                              ,
        is_corretta ENUM('TRUE', 'FALSE') DEFAULT 'FALSE' NOT NULL                       ,
        PRIMARY KEY (numero_opzione, test_associato, numero_quesito)                     ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE          ,
        FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE
    );

-- crea tabella quesito aperto
CREATE TABLE IF NOT EXISTS
    QUESITO_APERTO_SOLUZIONE (
        id_soluzione INT AUTO_INCREMENT, -- soluzione 1, soluzione 2 ... soluzione n
        test_associato VARCHAR(100) NOT NULL                                             ,
        numero_quesito INT NOT NULL                                                      ,
        soluzione_professore TEXT NOT NULL                                               ,
        PRIMARY KEY (id_soluzione, test_associato, numero_quesito)                       ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE          ,
        FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE
    );

CREATE TABLE IF NOT EXISTS
    RISPOSTA (
        test_associato VARCHAR(100) NOT NULL                            ,
        numero_quesito INT NOT NULL                                     ,
        email_studente VARCHAR(100) NOT NULL                            ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL          ,
        tipo_risposta ENUM('APERTA', 'CHIUSA') DEFAULT 'APERTA' NOT NULL,
        esito ENUM('GIUSTA', 'SBAGLIATA') DEFAULT 'SBAGLIATA' NOT NULL  ,
        PRIMARY KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        )                                                                                 ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE           ,
        FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE
    );

-- crea tabelle delle risposte chiuse
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_CHIUSO (
        test_associato VARCHAR(100) NOT NULL                  ,
        numero_quesito INT NOT NULL                           ,
        email_studente VARCHAR(100) NOT NULL                  ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        scelta INT NOT NULL                                   ,
        PRIMARY KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        )           ,
        FOREIGN KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        ) REFERENCES RISPOSTA (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        ) ON DELETE CASCADE
    );

-- crea tabella delle risposte aperte
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_APERTO (
        test_associato VARCHAR(100) NOT NULL                  ,
        numero_quesito INT NOT NULL                           ,
        email_studente VARCHAR(100) NOT NULL                  ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        risposta TEXT NOT NULL                                ,
        PRIMARY KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        )           ,
        FOREIGN KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        ) REFERENCES RISPOSTA (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        ) ON DELETE CASCADE
    );

-- crea la stored procedure per inserire una nuova opzione per un quesito chiuso
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovaOpzioneQuesitoChiuso` (
    IN p_numero_opzione INT              ,
    IN p_numero_quesito INT              ,
    IN p_test_associato VARCHAR(100)     ,
    IN p_testo TEXT                      ,
    IN p_is_corretta ENUM('TRUE', 'FALSE')
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

-- Inserisce l'opzione nella tabella QUESITO_CHIUSO_OPZIONE
INSERT INTO
    QUESITO_CHIUSO_OPZIONE (
        numero_opzione,
        numero_quesito,
        test_associato,
        testo         ,
        is_corretta
    )
VALUES
    (
        p_numero_opzione,
        p_numero_quesito,
        p_test_associato,
        p_testo         ,
        p_is_corretta
    );

COMMIT;

END $$ DELIMITER;

-- crea la stored procedure per inserire una nuova soluzione per un quesito aperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovaSoluzioneQuesitoAperto` (
    IN p_numero_quesito INT         ,
    IN p_test_associato VARCHAR(100),
    IN p_soluzione_professore TEXT
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

-- Inserisce la soluzione nella tabella Quesito_aperto
INSERT INTO
    QUESITO_APERTO_SOLUZIONE (
        test_associato     ,
        numero_quesito     ,
        soluzione_professore
    )
VALUES
    (
        p_test_associato     ,
        p_numero_quesito     ,
        p_soluzione_professore
    );

COMMIT;

END $$ DELIMITER;

-- procedura per inserire un nuovo QUESITO
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoQuesito` (
    IN p_numero_quesito INT                               ,
    IN p_test_associato VARCHAR(100)                      ,
    IN p_descrizione TEXT                                 ,
    IN p_livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO'),
    IN p_tipo_quesito ENUM('APERTO', 'CHIUSO')
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

-- Inserisce il quesito nella tabella Quesito
INSERT INTO
    QUESITO (
        numero_quesito    ,
        test_associato    ,
        descrizione       ,
        livello_difficolta,
        tipo_quesito
    )
VALUES
    (
        p_numero_quesito    ,
        p_test_associato    ,
        p_descrizione       ,
        p_livello_difficolta,
        p_tipo_quesito
    );

COMMIT;

END $$ DELIMITER;

-- quesito chiuso
-- crea tabella risposteQuesitoChiuso
-- CREATE TABLE IF NOT EXISTS
--     RISPOSTA_QUESITO_CHIUSO (
--         numero INT AUTO_INCREMENT, -- risposta 1, risposta 2 ... risposta n 
--         test_associato VARCHAR(100) NOT NULL                                                           ,
--         numero_quesito INT NOT NULL                                                                    ,
--         numero_quesito_chiuso INT NOT NULL                                                             ,
--         testo TEXT NOT NULL                                                                            ,
--         esito BOOLEAN NOT NULL                                                                         ,
--         PRIMARY KEY (numero, test_associato, numero_quesito)                                           ,
--         FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE                        ,
--         FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE             ,
--         FOREIGN KEY (numero_quesito_chiuso) REFERENCES QUESITO_CHIUSO (numero_domanda) ON DELETE CASCADE
--     );
-- crea tabella delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TABELLA_DELLE_TABELLE (
        nome_tabella VARCHAR(20) PRIMARY KEY          ,
        data_creazione DATETIME DEFAULT NOW() NOT NULL,
        num_righe INT DEFAULT 0 NOT NULL
    );

-- -- crea tabella degli ATTRIBUTI delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TAB_ATT (
        nome_tabella VARCHAR(20) NOT NULL                                                          ,
        nome_attributo VARCHAR(100) NOT NULL                                                       ,
        tipo_attributo VARCHAR(15) NOT NULL                                                        ,
        PRIMARY KEY (nome_tabella, nome_attributo)                                                 ,
        FOREIGN KEY (nome_tabella) REFERENCES TABELLA_DELLE_TABELLE (nome_tabella) ON DELETE CASCADE
    );

-- -- crea tabella delle CHIAVI ESTERNE delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    CHIAVI_ESTERNE_DELLE_TABELLE (
        nome_tabella VARCHAR(20) NOT NULL        ,
        nome_attributo VARCHAR(100) NOT NULL     ,
        tabella_vincolata VARCHAR(20) NOT NULL   ,
        attributo_vincolato VARCHAR(100) NOT NULL,
        PRIMARY KEY (
            nome_tabella      ,
            nome_attributo    ,
            tabella_vincolata ,
            attributo_vincolato
        )                                                                                                                      ,
        FOREIGN KEY (nome_tabella, nome_attributo) REFERENCES TAB_ATT (nome_tabella, nome_attributo) ON DELETE CASCADE         ,
        FOREIGN KEY (tabella_vincolata, attributo_vincolato) REFERENCES TAB_ATT (nome_tabella, nome_attributo) ON DELETE CASCADE
    );

-- INSERT INTO
--     TABELLA_DELLE_TABELLE (nome_tabella)
-- VALUES
--     ('Tabella1')          ,
--     ('Tabella2')          ,
--     ('Tabella3')          ,
--     ('Tabella4')          ,
--     ('tabella_di_esempio');
-- -- Creazione della tabella Tabella1
-- CREATE TABLE IF NOT EXISTS
--     Tabella1 (
--         id INT AUTO_INCREMENT PRIMARY KEY                        ,
--         nome VARCHAR(50) NOT NULL                                ,
--         descrizione TEXT                                         ,
--         data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
--     ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;
-- -- Creazione della tabella Tabella2
-- CREATE TABLE IF NOT EXISTS
--     Tabella2 (
--         id INT AUTO_INCREMENT PRIMARY KEY                        ,
--         titolo VARCHAR(100) NOT NULL                             ,
--         autore VARCHAR(100)                                      ,
--         anno_pubblicazione INT                                   ,
--         data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
--     ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;
-- -- Creazione della tabella Tabella3
-- CREATE TABLE IF NOT EXISTS
--     Tabella3 (
--         id INT AUTO_INCREMENT PRIMARY KEY                        ,
--         nome VARCHAR(50) NOT NULL                                ,
--         cognome VARCHAR(50) NOT NULL                             ,
--         email VARCHAR(100) NOT NULL                              ,
--         data_nascita DATE                                        ,
--         data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
--     ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;
-- -- Creazione della tabella Tabella4
-- CREATE TABLE IF NOT EXISTS
--     Tabella4 (
--         id INT AUTO_INCREMENT PRIMARY KEY                        ,
--         nome_prodotto VARCHAR(100) NOT NULL                      ,
--         prezzo DECIMAL(10, 2) NOT NULL                           ,
--         descrizione TEXT                                         ,
--         data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
--     ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;
CREATE TABLE IF NOT EXISTS
    tabella_di_esempio (
        nome VARCHAR(100) NOT NULL   ,
        cognome VARCHAR(100) NOT NULL,
        eta INT NOT NULL             ,
        PRIMARY KEY (nome, cognome)
    );

-- CREATE TABLE IF NOT EXISTS
--     PRASSI (
--         matricola INT                                                                                           ,
--         cognome_prassi VARCHAR(100)                                                                             ,
--         nome_prassi VARCHAR(100)                                                                                ,
--         PRIMARY KEY (matricola)                                                                                 ,
--         FOREIGN KEY (cognome_prassi, nome_prassi) REFERENCES tabella_di_esempio (cognome, nome) ON DELETE CASCADE
--     );
INSERT INTO
    TEST (titolo, email_professore)
VALUES
    ('test1', 'professore@unibo.it');

-- procedura per prendere il numero del nuovo quesito
DELIMITER $$
CREATE PROCEDURE GetNumeroNuovoQuesito (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    numero_quesito
FROM
    QUESITO
WHERE
    test_associato = p_test_associato
ORDER BY
    numero_quesito DESC
LIMIT
    1;

END $$ DELIMITER;

-- inserisci risposta a quesito chiuso
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciRispostaQuesitoChiuso` (
    IN p_test_associato VARCHAR(100)     ,
    IN p_numero_quesito INT              ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_scelta INT                      ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
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

-- inserisce la risposta nella tabella Risposta
INSERT INTO
    RISPOSTA (
        test_associato,
        numero_quesito,
        email_studente,
        tipo_risposta ,
        esito
    )
VALUES
    (
        p_test_associato,
        p_numero_quesito,
        p_email_studente,
        'CHIUSA'        ,
        p_esito
    );

-- Inserisce la risposta nella tabella Risposta_quesito_chiuso
INSERT INTO
    RISPOSTA_QUESITO_CHIUSO (
        test_associato,
        numero_quesito,
        email_studente,
        scelta
    )
VALUES
    (
        p_test_associato,
        p_numero_quesito,
        p_email_studente,
        p_scelta
    );

COMMIT;

END $$ DELIMITER;

-- inserisci risposta a quesito aperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InserisciRispostaQuesitoAperto` (
    IN p_test_associato VARCHAR(100)     ,
    IN p_numero_quesito INT              ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_risposta TEXT                   ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
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

-- inserisce la risposta nella tabella Risposta
INSERT INTO
    RISPOSTA (
        test_associato,
        numero_quesito,
        email_studente,
        tipo_risposta ,
        esito
    )
VALUES
    (
        p_test_associato,
        p_numero_quesito,
        p_email_studente,
        'APERTA'        ,
        p_esito
    );

-- Inserisce la risposta nella tabella Risposta_quesito_aperto
INSERT INTO
    RISPOSTA_QUESITO_APERTO (
        test_associato,
        numero_quesito,
        email_studente,
        risposta
    )
VALUES
    (
        p_test_associato,
        p_numero_quesito,
        p_email_studente,
        p_risposta
    );

COMMIT;

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE GetRisposteQuesiti (
    IN p_test_associato VARCHAR(100),
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    r.numero_quesito            ,
    DATE(r.TIMESTAMP) AS in_data,
    r.esito                     ,
    r.tipo_risposta
FROM
    `RISPOSTA` AS r
WHERE
    r.test_associato = p_test_associato
    AND r.email_studente = p_email_studente
    AND (r.numero_quesito, r.TIMESTAMP) IN (
        SELECT
            numero_quesito,
            MAX(TIMESTAMP)
        FROM
            `RISPOSTA`
        WHERE
            test_associato = p_test_associato
            AND email_studente = p_email_studente
            AND TIMESTAMP
        GROUP BY
            numero_quesito
    )
GROUP BY
    r.numero_quesito,
    r.TIMESTAMP     ,
    r.esito         ,
    r.tipo_risposta
ORDER BY
    r.numero_quesito ASC;

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE GetTipoQuesito (
    IN p_numero_quesito INT        ,
    IN p_test_associato VARCHAR(255)
) BEGIN
SELECT
    tipo_quesito
FROM
    QUESITO
WHERE
    numero_quesito = p_numero_quesito
    AND test_associato = p_test_associato;

END $$ DELIMITER;

-- getAllTests
DELIMITER $$
CREATE PROCEDURE GetAllTests () BEGIN
SELECT
    *
FROM
    TEST;

END $$ DELIMITER;

-- get quesiti del test
DELIMITER $$
CREATE PROCEDURE GetQuesitiTest (IN p_test_associato VARCHAR(100)) BEGIN
SELECT
    *
FROM
    QUESITO
WHERE
    test_associato = p_test_associato;

END $$ DELIMITER;

-- get opzioni quesito chiuso del test
DELIMITER $$
CREATE PROCEDURE GetOpzioniQuesitoChiuso (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT
) BEGIN
SELECT
    *
FROM
    QUESITO_CHIUSO_OPZIONE
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito;

END $$ DELIMITER;

-- prendi opzioni quesito chiuso vere
DELIMITER $$
CREATE PROCEDURE GetOpzioniCorrette (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT
) BEGIN
SELECT
    numero_opzione
FROM
    QUESITO_CHIUSO_OPZIONE
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito
    AND is_corretta = 'TRUE';

END $$ DELIMITER;

-- prendi test del docente
DELIMITER $$
CREATE PROCEDURE GetTestDelProfessore (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    *
FROM
    TEST
WHERE
    email_professore = p_email_professore;

END $$ DELIMITER;

-- get risposta aperta from risposta
DELIMITER $$
CREATE PROCEDURE GetRispostaQuesitoAperto (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT         ,
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    risposta
FROM
    RISPOSTA_QUESITO_APERTO
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito
    AND email_studente = p_email_studente
    AND `TIMESTAMP` in (
        SELECT
            MAX(`TIMESTAMP`)
        FROM
            RISPOSTA_QUESITO_APERTO
        WHERE
            test_associato = p_test_associato
            AND numero_quesito = p_numero_quesito
            AND email_studente = p_email_studente
    );

END $$ DELIMITER;

-- get scelta quesito chiuso from risposta
DELIMITER $$
CREATE PROCEDURE GetSceltaQuesitoChiuso (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT         ,
    IN p_email_studente VARCHAR(100)
) BEGIN
SELECT
    scelta
FROM
    RISPOSTA_QUESITO_CHIUSO
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito
    AND email_studente = p_email_studente
    AND `TIMESTAMP` IN (
        SELECT
            MAX(`TIMESTAMP`)
        FROM
            RISPOSTA_QUESITO_CHIUSO
        WHERE
            test_associato = p_test_associato
            AND numero_quesito = p_numero_quesito
            AND email_studente = p_email_studente
    );

END $$ DELIMITER;

-- CREA tabella SVOLGIMENTO TEST
CREATE TABLE IF NOT EXISTS
    SVOLGIMENTO_TEST (
        titolo_test VARCHAR(100) NOT NULL                                                 ,
        email_studente VARCHAR(100) NOT NULL                                              ,
        data_inzio TIMESTAMP                                                              ,
        data_fine TIMESTAMP                                                               ,
        stato ENUM('APERTO', 'IN_COMPLETAMENTO', 'CONCLUSO') DEFAULT 'APERTO' NOT NULL    ,
        PRIMARY KEY (titolo_test, email_studente)                                         ,
        FOREIGN KEY (titolo_test) REFERENCES TEST (titolo) ON DELETE CASCADE              ,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE
    );

-- CREA PROCEDURA per inserire un nuovo SVOLGIMENTO TEST
DELIMITER $$
DROP PROCEDURE IF EXISTS `InserisciNuovoSvolgimentoTest` $$
CREATE PROCEDURE IF NOT EXISTS `InserisciNuovoSvolgimentoTest` (
    IN p_titolo_test VARCHAR(100)  ,
    IN p_email_studente VARCHAR(100)
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

-- Inserisce il svolgimento del test nella tabella Svolgimento_test
INSERT INTO
    SVOLGIMENTO_TEST (titolo_test, email_studente)
VALUES
    (p_titolo_test, p_email_studente);

COMMIT;

END $$ DELIMITER;

-- inserisci il nuovo test creato all'interno di svolgimento_test per ogni studente con lo stato APERTO
DELIMITER $$
DROP TRIGGER IF EXISTS after_test_creation $$
CREATE TRIGGER IF NOT EXISTS after_test_creation AFTER
INSERT
    ON TEST FOR EACH ROW BEGIN
    -- Inserire i record per tutti gli studenti nella tabella SVOLGIMENTO_TEST
INSERT INTO
    SVOLGIMENTO_TEST (titolo_test, email_studente)
SELECT
    NEW.titolo   ,
    email_studente
FROM
    STUDENTE;

END $$ DELIMITER;

-- TRIGGER che si aziona quando un utente inserisce una risposta
-- la tabella svolgimento_test viene aggiornata e viene settata la data di inzio e lo stato IN_COMPLETAMENTO
DELIMITER $$
DROP TRIGGER IF EXISTS update_svolgimento_test $$
CREATE TRIGGER IF NOT EXISTS update_svolgimento_test AFTER
INSERT
    ON RISPOSTA FOR EACH ROW BEGIN DECLARE is_prima_risposta INT;

SELECT
    COUNT(*) INTO is_prima_risposta
FROM
    RISPOSTA
WHERE
    test_associato = NEW.test_associato
    AND email_studente = NEW.email_studente;

IF is_prima_risposta = 1 THEN
UPDATE SVOLGIMENTO_TEST
SET
    data_inzio = NOW()       ,
    stato = 'IN_COMPLETAMENTO'
WHERE
    titolo_test = NEW.test_associato
    AND email_studente = NEW.email_studente;

END IF;

-- Se tutte le ultime risposte inserite sono giuste, setta lo stato a CONCLUSO e la data di fine del test
IF(
    SELECT
        COUNT(*)
    FROM
        RISPOSTA
    WHERE
        test_associato = NEW.test_associato
        AND email_studente = NEW.email_studente
        AND esito = 'GIUSTA'
        AND `TIMESTAMP` IN (
            SELECT
                MAX(`TIMESTAMP`)
            FROM
                RISPOSTA
            WHERE
                test_associato = NEW.test_associato
                AND email_studente = NEW.email_studente
        )
) = (
    SELECT
        COUNT(*)
    FROM
        `QUESITO`
    WHERE
        test_associato = NEW.test_associato
) THEN
UPDATE SVOLGIMENTO_TEST
SET
    stato = 'CONCLUSO'      ,
    data_fine = NEW.TIMESTAMP
WHERE
    titolo_test = NEW.test_associato
    AND email_studente = NEW.email_studente;

END IF;

END $$ DELIMITER;

-- show results 
DELIMITER $$
CREATE PROCEDURE MostraRisultati (IN p_test_associato VARCHAR(100)) BEGIN
UPDATE TEST
SET
    VisualizzaRisposte = 1
WHERE
    titolo = p_test_associato;

END $$ DELIMITER;

-- se il prof modifica il test impostanto visualizzatest come true, allora contrassegna tutti i test come conclusi
DELIMITER $$
DROP TRIGGER IF EXISTS update_test_conclusi $$
CREATE TRIGGER IF NOT EXISTS update_test_conclusi AFTER
UPDATE ON TEST FOR EACH ROW BEGIN
-- Se il professore ha impostato VisualizzaRisposte a 1, contrassegna tutti i test come CONCLUSI
IF NEW.VisualizzaRisposte = 1 THEN
UPDATE SVOLGIMENTO_TEST
SET
    stato = 'CONCLUSO',
    data_fine = NOW()
WHERE
    titolo_test = NEW.titolo;

END IF;

END $$ DELIMITER;

-- Inserimento valori di esempio per la tabella TEST
INSERT INTO
    TEST (titolo, dataCreazione, email_professore)
VALUES
    (
        "Test di Matematica" ,
        "2024-03-19 12:00:00",
        "professore@unibo.it"
    ),
    (
        "Test di Storia"     ,
        "2024-03-18 10:30:00",
        "professore@unibo.it"
    );

-- Inserimento valori di esempio per la tabella QUESITO
INSERT INTO
    QUESITO (
        numero_quesito    ,
        test_associato    ,
        descrizione       ,
        livello_difficolta,
        numero_risposte   ,
        tipo_quesito
    )
VALUES
    (
        1                            ,
        "Test di Matematica"         ,
        "Risolvi l'equazione x-2 = 0",
        "MEDIO"                      ,
        0                            ,
        "APERTO"
    ),
    (
        2                   ,
        "Test di Matematica",
        "Quanto fa 2+2?"    ,
        "BASSO"             ,
        0                   ,
        "CHIUSO"
    ),
    (
        1                                               ,
        "Test di Storia"                                ,
        "Chi era il primo presidente degli Stati Uniti?",
        "ALTO"                                          ,
        0                                               ,
        "APERTO"
    ),
    (
        3                   ,
        "Test di Matematica",
        "Quanto fa 5x5?"    ,
        "MEDIO"             ,
        0                   ,
        "CHIUSO"
    ),
    (
        2                                                  ,
        "Test di Storia"                                   ,
        "Chi è stato il primo uomo a camminare sulla Luna?",
        "MEDIO"                                            ,
        0                                                  ,
        "CHIUSO"
    ),
    (
        3                                             ,
        "Test di Storia"                              ,
        "Quando è scoppiata la prima guerra mondiale?",
        "ALTO"                                        ,
        0                                             ,
        "CHIUSO"
    );

-- Inserimento valori di esempio per la tabella QUESITO_CHIUSO_OPZIONE
INSERT INTO
    QUESITO_CHIUSO_OPZIONE (
        numero_opzione,
        numero_quesito,
        test_associato,
        testo         ,
        is_corretta
    )
VALUES
    (1, 2, "Test di Matematica", "4", "TRUE")         ,
    (2, 2, "Test di Matematica", "5", "FALSE")        ,
    (3, 2, "Test di Matematica", "6", "FALSE")        ,
    (1, 3, "Test di Matematica", "25", "TRUE")        ,
    (2, 3, "Test di Matematica", "30", "FALSE")       ,
    (3, 3, "Test di Matematica", "20", "FALSE")       ,
    (1, 2, "Test di Storia", "Neil Armstrong", "TRUE"),
    (2, 2, "Test di Storia", "Buzz Aldrin", "FALSE")  ,
    (3, 2, "Test di Storia", "Yuri Gagarin", "FALSE") ,
    (1, 3, "Test di Storia", "1914", "TRUE")          ,
    (2, 3, "Test di Storia", "1939", "FALSE")         ,
    (3, 3, "Test di Storia", "1945", "FALSE");

-- Inserimento valori di esempio per la tabella QUESITO_APERTO_SOLUZIONE
INSERT INTO
    QUESITO_APERTO_SOLUZIONE (
        test_associato     ,
        numero_quesito     ,
        soluzione_professore
    )
VALUES
    ("Test di Matematica", 1, "2")             ,
    ("Test di Storia", 1, "George Washington.");

-- tabella dei messaggi
CREATE TABLE IF NOT EXISTS
    MESSAGGIO (
        id INT AUTO_INCREMENT                                                 ,
        titolo VARCHAR(100) NOT NULL                                          ,
        testo TEXT NOT NULL                                                   ,
        data_inserimento DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL          ,
        test_associato VARCHAR(100) NOT NULL                                  ,
        PRIMARY KEY (id)                                                      ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE
    );

-- messaggio che invia uno studente al professore
CREATE TABLE IF NOT EXISTS
    MESSAGGIO_PRIVATO (
        id_messaggio INT NOT NULL                                                           ,
        mittente VARCHAR(100) NOT NULL                                                      ,
        destinatario VARCHAR(100) NOT NULL                                                  ,
        PRIMARY KEY (id_messaggio)                                                          ,
        FOREIGN KEY (id_messaggio) REFERENCES MESSAGGIO (id) ON DELETE CASCADE              ,
        FOREIGN KEY (mittente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE       ,
        FOREIGN KEY (destinatario) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
    );

-- messaggio che invia un professore a tutti gli studenti
CREATE TABLE IF NOT EXISTS
    BROADCAST (
        id_messaggio INT NOT NULL                                                        ,
        mittente VARCHAR(100) NOT NULL                                                   ,
        destinatario VARCHAR(100) NOT NULL                                               ,
        PRIMARY KEY (id_messaggio, destinatario)                                         ,
        FOREIGN KEY (id_messaggio) REFERENCES MESSAGGIO (id) ON DELETE CASCADE           ,
        FOREIGN KEY (mittente) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE,
        FOREIGN KEY (destinatario) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE
    );

-- studente invia messaggio
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS `InviaMessaggioDaStudente` (
    IN p_titolo VARCHAR(100)        ,
    IN p_testo TEXT                 ,
    IN p_test_associato VARCHAR(100),
    IN p_mittente VARCHAR(100)      ,
    IN p_destinatario VARCHAR(100)
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

-- Inserisce il messaggio nella tabella MESSAGGIO
INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (p_titolo, p_testo, p_test_associato);

-- Inserisce il messaggio nella tabella MESSAGGIO_PRIVATO
INSERT INTO
    MESSAGGIO_PRIVATO (id_messaggio, mittente, destinatario)
VALUES
    (LAST_INSERT_ID(), p_mittente, p_destinatario);

COMMIT;

END $$ DELIMITER;

-- docente invia messaggio a tutti gli studenti
DELIMITER $$
CREATE PROCEDURE `InviaMessaggioDaDocente` (
    IN p_mittente VARCHAR(100)     ,
    IN p_titolo VARCHAR(100)       ,
    IN p_testo TEXT                ,
    IN p_test_associato VARCHAR(100)
) BEGIN DECLARE last_id INT;

-- Inserisce il messaggio nella tabella MESSAGGIO
INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (p_titolo, p_testo, p_test_associato);

INSERT INTO
    BROADCAST (id_messaggio, mittente, destinatario)
SELECT
    LAST_INSERT_ID() AS id_messaggio,
    p_mittente                      ,
    email_studente
FROM
    STUDENTE;

END $$ DELIMITER;

-- get messaggi studente
DELIMITER $$
CREATE PROCEDURE GetMessaggiStudente (IN p_email_studente VARCHAR(100)) BEGIN
SELECT
    m.id              ,
    m.titolo          ,
    m.testo           ,
    m.data_inserimento,
    m.test_associato  ,
    b.mittente
FROM
    `MESSAGGIO` as m,
    `BROADCAST` as b
WHERE
    m.id = b.id_messaggio
    AND b.destinatario = p_email_studente;

END $$ DELIMITER;

-- get messaggi professore
DELIMITER $$
CREATE PROCEDURE GetMessaggiProfessore (IN p_email_professore VARCHAR(100)) BEGIN
SELECT
    m.id              ,
    m.titolo          ,
    m.testo           ,
    m.data_inserimento,
    m.test_associato  ,
    DM.mittente
FROM
    `MESSAGGIO` as m        ,
    `MESSAGGIO_PRIVATO` as DM
WHERE
    m.id = DM.id_messaggio
    AND DM.destinatario = p_email_professore;

END $$ DELIMITER;

-- Inserimento di 3 messaggi privati da studente@unibo.it a professore@unibo.it con il test associato "Test di Matematica"
INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (
        'Domanda su argomento trattato in classe'                                                                                                                  ,
        'Salve Professore, avrei bisogno di chiarimenti sull''argomento trattato durante l''ultima lezione di Matematica. Potrebbe fornirmi qualche delucidazione?',
        'Test di Matematica'
    );

INSERT INTO
    MESSAGGIO_PRIVATO (id_messaggio, mittente, destinatario)
VALUES
    (
        LAST_INSERT_ID()    ,
        'studente@unibo.it' ,
        'professore@unibo.it'
    );

INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (
        'Richiesta di proroga per consegna compito'                                                                                                                                  ,
        'Buongiorno Professore, mi trovo in difficoltà e vorrei chiederle gentilmente una proroga per la consegna del compito di Matematica. Spero possa concedermela. Grazie mille.',
        'Test di Matematica'
    );

INSERT INTO
    MESSAGGIO_PRIVATO (id_messaggio, mittente, destinatario)
VALUES
    (
        LAST_INSERT_ID()    ,
        'studente@unibo.it' ,
        'professore@unibo.it'
    );

INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (
        'Richiesta appuntamento per consulenza'                                                                                                                   ,
        'Salve Professore, vorrei fissare un appuntamento per discutere alcune questioni relative al progetto di Matematica. Quando potrebbe essermi disponibile?',
        'Test di Matematica'
    );

INSERT INTO
    MESSAGGIO_PRIVATO (id_messaggio, mittente, destinatario)
VALUES
    (
        LAST_INSERT_ID()    ,
        'studente@unibo.it' ,
        'professore@unibo.it'
    );

-- Inserimento di 2 messaggi di broadcast da professore@unibo.it a tutti gli studenti con il test associato "Test di Storia"
INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (
        "Comunicazione importante riguardante l'esame"                                                                                                                             ,
        'Buongiorno studenti, vi scrivo per comunicarvi un cambiamento nella data dell''esame di Storia. Si prega di fare riferimento al sito web del corso per maggiori dettagli.',
        'Test di Storia'
    );

INSERT INTO
    BROADCAST (id_messaggio, mittente, destinatario)
VALUES
    (
        LAST_INSERT_ID()     ,
        'professore@unibo.it',
        'studente@unibo.it'
    );

INSERT INTO
    MESSAGGIO (titolo, testo, test_associato)
VALUES
    (
        'Avviso: lezioni sospese'                                                                                                                                                                                              ,
        "Cari studenti, vi informo che le lezioni di domani saranno sospese a causa di un'imprevista emergenza riguardante il corso di Storia. Vi aggiornerò appena possibile riguardo alla ripresa delle attività didattiche.",
        'Test di Storia'
    );

INSERT INTO
    BROADCAST (id_messaggio, mittente, destinatario)
VALUES
    (
        LAST_INSERT_ID()     ,
        'professore@unibo.it',
        'studente@unibo.it'
    );