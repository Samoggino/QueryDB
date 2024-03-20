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
    IN p_VisualizzaRisposte BOOLEAN  ,
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
    TEST (titolo, VisualizzaRisposte, email_professore)
VALUES
    (
        p_titolo            ,
        p_VisualizzaRisposte,
        p_email_professore
    );

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
    OPZIONE_QUESITO_CHIUSO (
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
    SOLUZIONE_QUESITO_APERTO (
        id_soluzione INT AUTO_INCREMENT, -- soluzione 1, soluzione 2 ... soluzione n
        test_associato VARCHAR(100) NOT NULL                                             ,
        numero_quesito INT NOT NULL                                                      ,
        soluzione_professore TEXT NOT NULL                                               ,
        PRIMARY KEY (id_soluzione, test_associato, numero_quesito)                       ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE          ,
        FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE
    );

-- crea tabelle delle risposte chiuse
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_CHIUSO (
        test_associato VARCHAR(100) NOT NULL                          ,
        numero_quesito INT NOT NULL                                   ,
        email_studente VARCHAR(100) NOT NULL                          ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL        ,
        scelta INT NOT NULL                                           ,
        esito ENUM('GIUSTA', 'SBAGLIATA') DEFAULT 'SBAGLIATA' NOT NULL,
        PRIMARY KEY (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        )                                                                                        ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE                  ,
        FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE       ,
        FOREIGN KEY (scelta) REFERENCES OPZIONE_QUESITO_CHIUSO (numero_opzione) ON DELETE CASCADE,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE
    );

-- crea tabella delle risposte aperte
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_APERTO (
        test_associato VARCHAR(100) NOT NULL                          ,
        numero_quesito INT NOT NULL                                   ,
        email_studente VARCHAR(100) NOT NULL                          ,
        risposta TEXT NOT NULL                                        ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL        ,
        esito ENUM('GIUSTA', 'SBAGLIATA') DEFAULT 'SBAGLIATA' NOT NULL,
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

-- Inserimento valori di esempio per la tabella TEST
INSERT INTO
    TEST (
        titolo            ,
        dataCreazione     ,
        VisualizzaRisposte,
        email_professore
    )
VALUES
    (
        "Test di Matematica" ,
        "2024-03-19 12:00:00",
        1                    ,
        "professore@unibo.it"
    ),
    (
        "Test di Storia"     ,
        "2024-03-18 10:30:00",
        0                    ,
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
        1                                ,
        "Test di Matematica"             ,
        "Risolvi l'equazione x^2 - 4 = 0",
        "MEDIO"                          ,
        0                                ,
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
        "APERTO"
    ),
    (
        3                                             ,
        "Test di Storia"                              ,
        "Quando è scoppiata la prima guerra mondiale?",
        "ALTO"                                        ,
        0                                             ,
        "APERTO"
    );

-- Inserimento valori di esempio per la tabella OPZIONE_QUESITO_CHIUSO
INSERT INTO
    OPZIONE_QUESITO_CHIUSO (
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
    (4, 2, "Test di Storia", "Neil Armstrong", "TRUE"),
    (5, 2, "Test di Storia", "Buzz Aldrin", "FALSE")  ,
    (6, 2, "Test di Storia", "Yuri Gagarin", "FALSE") ,
    (7, 3, "Test di Storia", "1914", "TRUE")          ,
    (8, 3, "Test di Storia", "1939", "FALSE")         ,
    (9, 3, "Test di Storia", "1945", "FALSE");

-- Inserimento valori di esempio per la tabella SOLUZIONE_QUESITO_APERTO
INSERT INTO
    SOLUZIONE_QUESITO_APERTO (
        test_associato     ,
        numero_quesito     ,
        soluzione_professore
    )
VALUES
    (
        "Test di Matematica"         ,
        1                            ,
        "Le soluzioni sono x=2 e x=-2"
    ),
    (
        "Test di Storia"                                             ,
        1                                                            ,
        "Il primo presidente degli Stati Uniti era George Washington."
    );

-- crea tabella dei QUESITI CHIUSI
-- CREATE TABLE IF NOT EXISTS
--     QUESITO_CHIUSO (
--         test_associato VARCHAR(100) NOT NULL                                ,
--         numero_quesito INT NOT NULL, -- domanda 1, domanda 2 ... domanda n
--         numero_domanda INT AUTO_INCREMENT, -- opzione a, opzione b ... opzione z 
--         PRIMARY KEY (numero_domanda, test_associato, numero_quesito)                        ,
--         FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE             ,
--         FOREIGN KEY (numero_quesito) REFERENCES QUESITO (numero_quesito) ON DELETE CASCADE
--     );
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

-- Inserisce l'opzione nella tabella Opzione_quesito_chiuso
INSERT INTO
    OPZIONE_QUESITO_CHIUSO (
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
    SOLUZIONE_QUESITO_APERTO (
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

-- Inserisce la risposta nella tabella Risposta_quesito_chiuso
INSERT INTO
    RISPOSTA_QUESITO_CHIUSO (
        test_associato,
        numero_quesito,
        email_studente,
        scelta        ,
        esito
    )
VALUES
    (
        p_test_associato,
        p_numero_quesito,
        p_email_studente,
        p_scelta        ,
        p_esito
    );

COMMIT;

END $$ DELIMITER;

-- query per prendere tutti i risultati di un test di uno studente scelto
DELIMITER $$
CREATE PROCEDURE GetLatestTestResponses (
    IN test_param VARCHAR(255)   ,
    IN studente_param VARCHAR(255)
) BEGIN DECLARE rollback_occurred BOOLEAN DEFAULT FALSE;

-- In caso di errore, imposta la variabile rollback_occurred a TRUE
DECLARE CONTINUE
HANDLER FOR SQLEXCEPTION BEGIN
SET
    rollback_occurred = TRUE;

END;

-- Avvia la transazione
START TRANSACTION;

-- Query per ottenere le risposte più recenti
SELECT
    r.numero_quesito            ,
    DATE(r.TIMESTAMP) as in_data,
    r.scelta                    ,
    r.esito
FROM
    RISPOSTA_QUESITO_CHIUSO AS r
WHERE
    r.test_associato = test_param
    AND r.email_studente = studente_param
    AND (r.numero_quesito, r.TIMESTAMP) IN (
        SELECT
            numero_quesito,
            MAX(TIMESTAMP)
        FROM
            RISPOSTA_QUESITO_CHIUSO
        WHERE
            test_associato = test_param
            AND email_studente = studente_param
        GROUP BY
            numero_quesito
    )
GROUP BY
    r.numero_quesito,
    r.TIMESTAMP     ,
    r.scelta        ,
    r.esito;

-- Controlla se si è verificato un errore
IF rollback_occurred THEN
-- Rollback della transazione in caso di errore
ROLLBACK;

ELSE
-- Commit della transazione se tutto è andato bene
COMMIT;

END IF;

END $$ DELIMITER;

-- SELECT tipo_quesito FROM QUESITO WHERE numero_quesito = :numero_quesito AND test_associato = :test_associato;
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