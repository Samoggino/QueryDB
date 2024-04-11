DROP DATABASE IF EXISTS ESQLDB;

CREATE DATABASE IF NOT EXISTS ESQLDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE ESQLDB;

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

-- crea tabella delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TABELLA_DELLE_TABELLE (
        nome_tabella VARCHAR(20) PRIMARY KEY                                            ,
        data_creazione DATETIME DEFAULT NOW() NOT NULL                                  ,
        num_righe INT DEFAULT 0 NOT NULL                                                ,
        creatore VARCHAR(100) NOT NULL                                                  ,
        FOREIGN KEY (creatore) REFERENCES PROFESSORE (email_professore) ON DELETE CASCADE
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
    ('studente1@example.com', 2019, '00123456') ,
    ('studente2@example.com', 2020, '00987654') ,
    ('simosamoggia@gmail.com', 2020, '00970758'),
    ('studente@unibo.it', 2020, '00567890');

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
CREATE PROCEDURE IF NOT EXISTS authenticateUser (
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
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoStudente (
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
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoProfessore (
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
CREATE PROCEDURE IF NOT EXISTS VerificaTipoUtente (IN p_email VARCHAR(100)) BEGIN DECLARE is_studente INT DEFAULT 0;

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
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoTest (
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
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaFotoTest (
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
CREATE PROCEDURE IF NOT EXISTS RecuperaFotoTest (IN p_test_associato VARCHAR(100)) BEGIN
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
        ID INT AUTO_INCREMENT                                                     ,
        numero_quesito INT NOT NULL                                               ,
        test_associato VARCHAR(100) NOT NULL                                      ,
        descrizione TEXT NOT NULL                                                 ,
        livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO') DEFAULT 'BASSO' NOT NULL,
        numero_risposte INT NOT NULL DEFAULT 0                                    ,
        tipo_quesito ENUM('APERTO', 'CHIUSO') DEFAULT 'APERTO' NOT NULL           ,
        PRIMARY KEY (ID)                                                          ,
        FOREIGN KEY (test_associato) REFERENCES TEST (titolo) ON DELETE CASCADE   ,
        CONSTRAINT quesito_test UNIQUE (test_associato, numero_quesito)
    );

-- OPZIONE QUESITO CHIUSO
CREATE TABLE IF NOT EXISTS
    QUESITO_CHIUSO_OPZIONE (
        numero_opzione INT, -- opzione a, opzione b ... opzione z  
        id_quesito INT NOT NULL                                          ,
        testo TEXT NOT NULL                                              ,
        is_corretta ENUM('TRUE', 'FALSE') DEFAULT 'FALSE' NOT NULL       ,
        PRIMARY KEY (numero_opzione, id_quesito)                         ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE
    );

-- crea tabella quesito aperto
CREATE TABLE IF NOT EXISTS
    QUESITO_APERTO_SOLUZIONE (
        id_quesito INT NOT NULL                                                    ,
        id_soluzione INT AUTO_INCREMENT, -- soluzione 1, soluzione 2 ... soluzione n
        soluzione_professore TEXT NOT NULL                               ,
        PRIMARY KEY (id_soluzione, id_quesito)                           ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE
    );

CREATE TABLE IF NOT EXISTS
    RISPOSTA (
        ID INT AUTO_INCREMENT UNIQUE                                                                                      ,
        test_associato VARCHAR(100) NOT NULL                                                                              ,
        numero_quesito INT NOT NULL                                                                                       ,
        email_studente VARCHAR(100) NOT NULL                                                                              ,
        TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL                                                            ,
        tipo_risposta ENUM('APERTA', 'CHIUSA') DEFAULT 'APERTA' NOT NULL                                                  ,
        esito ENUM('GIUSTA', 'SBAGLIATA') DEFAULT 'SBAGLIATA' NOT NULL                                                    ,
        PRIMARY KEY (ID)                                                                                                  ,
        FOREIGN KEY (test_associato, numero_quesito) REFERENCES QUESITO (test_associato, numero_quesito) ON DELETE CASCADE,
        FOREIGN KEY (email_studente) REFERENCES STUDENTE (email_studente) ON DELETE CASCADE                               ,
        CONSTRAINT risposta_quesito UNIQUE (
            test_associato,
            numero_quesito,
            TIMESTAMP     ,
            email_studente
        )
    );

-- crea tabelle delle risposte chiuse
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_CHIUSO (
        id_risposta INT NOT NULL                                                      ,
        opzione_scelta INT NOT NULL                                                   ,
        PRIMARY KEY (id_risposta)                                                     ,
        FOREIGN KEY (id_risposta) REFERENCES RISPOSTA (ID) ON DELETE CASCADE          ,
        FOREIGN KEY (opzione_scelta) REFERENCES QUESITO_CHIUSO_OPZIONE (numero_opzione)
    );

-- crea tabella delle risposte aperte
CREATE TABLE IF NOT EXISTS
    RISPOSTA_QUESITO_APERTO (
        id_risposta INT NOT NULL                                           ,
        risposta TEXT NOT NULL                                             ,
        PRIMARY KEY (id_risposta)                                          ,
        FOREIGN KEY (id_risposta) REFERENCES RISPOSTA (ID) ON DELETE CASCADE
    );

-- crea la stored procedure per inserire una nuova opzione per un quesito chiuso
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaOpzioneQuesitoChiuso (
    IN p_numero_opzione INT              ,
    IN p_num_quesito INT                 ,
    IN p_test_associato VARCHAR(100)     ,
    IN p_testo TEXT                      ,
    IN p_is_corretta ENUM('TRUE', 'FALSE')
) BEGIN DECLARE ID_tmp INT;

SELECT
    ID INTO ID_tmp
FROM
    `QUESITO`
WHERE
    numero_quesito = p_num_quesito
    AND test_associato = p_test_associato;

-- Inserisce l'opzione nella tabella QUESITO_CHIUSO_OPZIONE
INSERT INTO
    QUESITO_CHIUSO_OPZIONE (id_quesito, numero_opzione, testo, is_corretta)
VALUES
    (ID_tmp, p_numero_opzione, p_testo, p_is_corretta);

COMMIT;

END $$ DELIMITER;

-- aggiorna il numero_risposte di un quesito quando viene aggiunto una nuova opzione
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_numero_risposte_al_quesito AFTER
INSERT
    ON RISPOSTA FOR EACH ROW
UPDATE QUESITO
SET
    numero_risposte = numero_risposte + 1
WHERE
    test_associato = NEW.test_associato
    AND numero_quesito = NEW.numero_quesito $$ DELIMITER;

-- crea la stored procedure per inserire una nuova soluzione per un quesito aperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovaSoluzioneQuesitoAperto (
    IN p_num_quesito INT            ,
    IN p_test_associato VARCHAR(100),
    IN p_soluzione_professore TEXT
) BEGIN DECLARE ID_tmp INT;

START TRANSACTION;

SELECT
    ID INTO ID_tmp
FROM
    `QUESITO`
WHERE
    numero_quesito = p_num_quesito
    AND test_associato = p_test_associato;

-- Inserisce la soluzione nella tabella Quesito_aperto
INSERT INTO
    QUESITO_APERTO_SOLUZIONE (id_quesito, soluzione_professore)
VALUES
    (ID_tmp, p_soluzione_professore);

COMMIT;

END $$ DELIMITER;

-- procedura per inserire un nuovo QUESITO
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoQuesito (
    IN p_numero_quesito INT                               ,
    IN p_test_associato VARCHAR(100)                      ,
    IN p_descrizione TEXT                                 ,
    IN p_livello_difficolta ENUM('BASSO', 'MEDIO', 'ALTO'),
    IN p_tipo_quesito ENUM('APERTO', 'CHIUSO')
) BEGIN
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

-- -- crea tabella degli ATTRIBUTI delle TABELLE create dai PROFESSORI
CREATE TABLE IF NOT EXISTS
    TAB_ATT (
        ID INT AUTO_INCREMENT                                                                      ,
        nome_tabella VARCHAR(20) NOT NULL                                                          ,
        nome_attributo VARCHAR(100) NOT NULL                                                       ,
        tipo_attributo VARCHAR(15) NOT NULL                                                        ,
        PRIMARY KEY (ID)                                                                           ,
        CONSTRAINT UNIQUE_TAB_ATT UNIQUE (nome_tabella, nome_attributo)                            ,
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

-- Creazione della tabella Tabella1
CREATE TABLE IF NOT EXISTS
    Tabella1 (
        id INT AUTO_INCREMENT PRIMARY KEY                        ,
        nome VARCHAR(50) NOT NULL                                ,
        descrizione TEXT                                         ,
        data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;

-- Creazione della tabella Tabella2
CREATE TABLE IF NOT EXISTS
    Tabella2 (
        id INT AUTO_INCREMENT PRIMARY KEY                        ,
        titolo VARCHAR(100) NOT NULL                             ,
        autore VARCHAR(100)                                      ,
        anno_pubblicazione INT                                   ,
        data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;

-- Creazione della tabella Tabella3
CREATE TABLE IF NOT EXISTS
    Tabella3 (
        id INT AUTO_INCREMENT PRIMARY KEY                        ,
        nome VARCHAR(50) NOT NULL                                ,
        cognome VARCHAR(50) NOT NULL                             ,
        email VARCHAR(100) NOT NULL                              ,
        data_nascita DATE                                        ,
        data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;

-- Creazione della tabella Tabella4
CREATE TABLE IF NOT EXISTS
    Tabella4 (
        id INT AUTO_INCREMENT PRIMARY KEY                        ,
        nome_prodotto VARCHAR(100) NOT NULL                      ,
        prezzo DECIMAL(10, 2) NOT NULL                           ,
        descrizione TEXT                                         ,
        data_creazione DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3;

CREATE TABLE IF NOT EXISTS
    tabella_di_esempio (
        nome VARCHAR(100) NOT NULL   ,
        cognome VARCHAR(100) NOT NULL,
        eta INT NOT NULL             ,
        PRIMARY KEY (nome, cognome)
    );

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

CREATE TABLE IF NOT EXISTS
    CHIAVI (
        ID INT AUTO_INCREMENT                                                                                      ,
        nome_tabella VARCHAR(20) NOT NULL                                                                          ,
        pezzo_chiave VARCHAR(100) NOT NULL                                                                         ,
        PRIMARY KEY (ID)                                                                                           ,
        CONSTRAINT UNIQUE_tab_chiave UNIQUE (nome_tabella, pezzo_chiave)                                           ,
        FOREIGN KEY (nome_tabella, pezzo_chiave) REFERENCES TAB_ATT (nome_tabella, nome_attributo) ON DELETE CASCADE
    );

CREATE TABLE IF NOT EXISTS
    provolone (
        NomeR VARCHAR(100) NOT NULL                                                                 ,
        CognomeR VARCHAR(100) NOT NULL                                                              ,
        das INT NOT NULL                                                                            ,
        PRIMARY KEY (NomeR, CognomeR)                                                               ,
        FOREIGN KEY (NomeR, CognomeR) REFERENCES tabella_di_esempio (nome, cognome) ON DELETE CASCADE
    );

-- crea tabella dei quesiti-tabella
CREATE TABLE IF NOT EXISTS
    QUESITI_TABELLA (
        ID INT AUTO_INCREMENT                                                                       ,
        id_quesito INT NOT NULL                                                                     ,
        nome_tabella VARCHAR(20) NOT NULL                                                           ,
        PRIMARY KEY (ID)                                                                            ,
        FOREIGN KEY (id_quesito) REFERENCES QUESITO (ID) ON DELETE CASCADE                          ,
        FOREIGN KEY (nome_tabella) REFERENCES TABELLA_DELLE_TABELLE (nome_tabella) ON DELETE CASCADE,
        CONSTRAINT UNIQUE_quesito_tabella UNIQUE (id_quesito, nome_tabella)
    );

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

-- riparti da qui
-- crea procedure che verifica se lo studente ha già concluso quel test
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS VerificaTestConcluso (
    IN p_email_studente VARCHAR(100),
    IN p_titolo_test VARCHAR(100)   ,
    OUT is_closed INT
) BEGIN
SELECT
    COUNT(*) INTO is_closed
FROM
    SVOLGIMENTO_TEST
WHERE
    email_studente = p_email_studente
    AND titolo_test = p_titolo_test
    AND stato = 'CONCLUSO';

END $$ DELIMITER;

-- inserisci risposta a quesito chiuso 
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciRispostaQuesitoChiuso (
    IN p_test_associato VARCHAR(100)     ,
    IN p_numero_quesito INT              ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_opzione_scelta INT              ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
) BEGIN DECLARE id_risposta INT;

DECLARE test_closed INT;

START TRANSACTION;

-- Controlla se il test è già concluso
CALL VerificaTestConcluso (p_email_studente, p_test_associato, test_closed);

IF test_closed < 1 THEN
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

-- Ottiene l'id della risposta appena inserita
SELECT
    LAST_INSERT_ID() INTO id_risposta;

-- Inserisce la risposta nella tabella Risposta_quesito_chiuso
INSERT INTO
    RISPOSTA_QUESITO_CHIUSO (id_risposta, opzione_scelta)
VALUES
    (id_risposta, p_opzione_scelta);

END IF;

-- Esegue il commit della transazione
COMMIT;

END $$ DELIMITER;

-- inserisci risposta a quesito aperto
CREATE PROCEDURE IF NOT EXISTS InserisciRispostaQuesitoAperto (
    IN p_test_associato VARCHAR(100)     ,
    IN p_numero_quesito INT              ,
    IN p_email_studente VARCHAR(100)     ,
    IN p_risposta TEXT                   ,
    IN p_esito ENUM('GIUSTA', 'SBAGLIATA')
) BEGIN DECLARE id_risposta INT;

DECLARE test_closed INT;

START TRANSACTION;

CALL VerificaTestConcluso (p_email_studente, p_test_associato, test_closed);

IF test_closed < 1 THEN
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

-- Ottiene l'id della risposta appena inserita
SELECT
    LAST_INSERT_ID() INTO id_risposta;

-- Inserisce la risposta nella tabella Risposta_quesito_aperto
INSERT INTO
    RISPOSTA_QUESITO_APERTO (id_risposta, risposta)
VALUES
    (id_risposta, p_risposta);

END IF;

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
    RISPOSTA AS r
WHERE
    r.test_associato = p_test_associato
    AND r.email_studente = p_email_studente
    AND (r.numero_quesito, r.TIMESTAMP) IN (
        SELECT
            numero_quesito,
            MAX(TIMESTAMP)
        FROM
            RISPOSTA
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
    JOIN QUESITO on QUESITO.ID = QUESITO_CHIUSO_OPZIONE.id_quesito
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
    *
FROM
    QUESITO_CHIUSO_OPZIONE
    JOIN QUESITO on QUESITO.ID = QUESITO_CHIUSO_OPZIONE.id_quesito
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
    *
FROM
    RISPOSTA_QUESITO_APERTO as ra
WHERE
    id_risposta IN (
        SELECT
            RISPOSTA.ID
        FROM
            RISPOSTA
        WHERE
            test_associato = p_test_associato
            AND numero_quesito = p_numero_quesito
            AND email_studente = p_email_studente
        GROUP BY
            RISPOSTA.ID
        HAVING
            MAX(TIMESTAMP)
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
    opzione_scelta
FROM
    RISPOSTA_QUESITO_CHIUSO
WHERE
    id_risposta IN (
        SELECT
            RISPOSTA.ID
        FROM
            RISPOSTA
        WHERE
            test_associato = p_test_associato
            AND numero_quesito = p_numero_quesito
            AND email_studente = p_email_studente
        GROUP BY
            RISPOSTA.ID
        HAVING
            MAX(TIMESTAMP)
    );

END $$ DELIMITER;

-- CREA PROCEDURA per inserire un nuovo SVOLGIMENTO TEST
DELIMITER $$
DROP PROCEDURE IF EXISTS InserisciNuovoSvolgimentoTest $$
CREATE PROCEDURE IF NOT EXISTS InserisciNuovoSvolgimentoTest (
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

-- SONO QUI
-- TRIGGER che si aziona quando un utente inserisce una risposta
-- la tabella svolgimento_test viene aggiornata e viene settata la data di inzio e lo stato IN_COMPLETAMENTO
DELIMITER $$
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
        AND RISPOSTA.TIMESTAMP IN (
            SELECT
                MAX(RISPOSTA.TIMESTAMP)
            FROM
                RISPOSTA
            WHERE
                RISPOSTA.test_associato = NEW.test_associato
                AND RISPOSTA.email_studente = NEW.email_studente
        )
) = (
    SELECT
        COUNT(*)
    FROM
        QUESITO
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
    QUESITO_CHIUSO_OPZIONE (numero_opzione, id_quesito, testo, is_corretta)
VALUES
    (1, 2, "4", "TRUE")             ,
    (2, 2, "5", "FALSE")            ,
    (3, 2, "6", "FALSE")            ,
    (1, 4, "25", "TRUE")            ,
    (2, 4, "30", "FALSE")           ,
    (3, 4, "20", "FALSE")           ,
    (1, 5, "Neil Armstrong", "TRUE"),
    (2, 5, "Buzz Aldrin", "FALSE")  ,
    (3, 5, "Yuri Gagarin", "FALSE") ,
    (1, 6, "1914", "TRUE")          ,
    (2, 6, "1939", "FALSE")         ,
    (3, 6, "1945", "FALSE");

-- Inserimento valori di esempio per la tabella QUESITO_APERTO_SOLUZIONE
INSERT INTO
    QUESITO_APERTO_SOLUZIONE (id_quesito, soluzione_professore)
VALUES
    (1, "2")                ,
    (3, "George Washington");

-- studente invia messaggio
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InviaMessaggioDaStudente (
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
CREATE PROCEDURE InviaMessaggioDaDocente (
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
    MESSAGGIO as m,
    BROADCAST as b
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
    MESSAGGIO as m        ,
    MESSAGGIO_PRIVATO as DM
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

-- classifica test conclusi dagli studenti
CREATE VIEW
    Classifica_test_completati AS
SELECT
    s.matricola,
    (
        SELECT
            COUNT(*)
        FROM
            SVOLGIMENTO_TEST st
        WHERE
            st.email_studente = s.email_studente
            AND st.stato = 'CONCLUSO'
    ) AS Test_conclusi
FROM
    STUDENTE s
GROUP BY
    s.email_studente,
    Test_conclusi
ORDER BY
    Test_conclusi DESC;

# Visualizzare	la	classifica	degli	studenti,	sulla	base	del	numero	di	risposte	corrette	inserite	
# rispetto	al	numero	 totale	di	risposte	inserite.	Nella	classifica	NON	devono	apparire	i	dati	
# sensibili	dello	studente	(nome,	cognome,	email)	ma	solo	il	codice	alfanumerico.
DROP VIEW IF EXISTS Classifica_risposte_giusta;

CREATE VIEW
    Classifica_risposte_giusta AS
SELECT
    s.matricola,
    (
        CASE
            WHEN (
                SELECT
                    count(*) as tot_risposte
                FROM
                    RISPOSTA r1
                WHERE
                    r1.email_studente = s.email_studente
                    AND r1.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            ) > 0 THEN (
                SELECT
                    COUNT(*) as risp_giuste
                FROM
                    RISPOSTA r
                WHERE
                    r.email_studente = s.email_studente
                    AND r.esito = 'GIUSTA'
                    AND r.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            ) / (
                SELECT
                    count(*) as tot_risposte
                FROM
                    RISPOSTA r1
                WHERE
                    r1.email_studente = s.email_studente
                    AND r1.TIMESTAMP IN (
                        SELECT
                            MAX(TIMESTAMP)
                        FROM
                            RISPOSTA r2
                        WHERE
                            r2.email_studente = s.email_studente
                    )
            )
            ELSE 0
        END
    ) AS Risposte_corrette
FROM
    STUDENTE s
GROUP BY
    s.matricola     ,
    Risposte_corrette
ORDER BY
    Risposte_corrette DESC;

DELIMITER $$
CREATE PROCEDURE GetClassificaRisposteGiuste () BEGIN
SELECT
    rg.matricola                   ,
    rg.Risposte_corrette as Rapporto
FROM
    Classifica_risposte_giusta as rg;

END $$ DELIMITER;

-- stored procedure per avere l'ordine delle chiavi primarie di una tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetPrimaryKey (IN p_table_name VARCHAR(100)) BEGIN
SELECT
    ID           as INDICE       ,
    pezzo_chiave as NOME_ATTRIBUTO
FROM
    CHIAVI
WHERE
    nome_tabella = p_table_name
ORDER BY
    ID ASC;

END $$ DELIMITER;

-- get tabelle delle tabelle
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleCreate () BEGIN
SELECT
    *
FROM
    TABELLA_DELLE_TABELLE;

END $$ DELIMITER;

-- crea GetSoluzioneQuesitoAperto
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetSoluzioneQuesitoAperto (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT
) BEGIN
SELECT
    soluzione_professore
FROM
    QUESITO_APERTO_SOLUZIONE
    JOIN QUESITO on QUESITO.ID = QUESITO_APERTO_SOLUZIONE.id_quesito
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito;

END $$ DELIMITER;

-- crea GetAllRisposte
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetAllRisposteDellUtente (
    IN p_email_studente VARCHAR(100),
    IN p_test_associato VARCHAR(100)
) BEGIN
SELECT
    *
FROM
    RISPOSTA
WHERE
    email_studente = p_email_studente
    AND test_associato = p_test_associato
GROUP BY
    ID
HAVING
    MAX(TIMESTAMP);

END $$ DELIMITER;

-- insert into TAB_ATT
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciAttributo (
    IN p_nome_tabella VARCHAR(20)   ,
    IN p_nome_attributo VARCHAR(100),
    IN p_tipo_attributo VARCHAR(15)
) BEGIN
INSERT INTO
    TAB_ATT (nome_tabella, nome_attributo, tipo_attributo)
VALUES
    (
        p_nome_tabella  ,
        p_nome_attributo,
        p_tipo_attributo
    );

END $$ DELIMITER;

-- insert into TABELLA_DELLE_TABELLE
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciTabellaDiEsercizio (
    IN p_nome_tabella VARCHAR(20),
    IN p_creatore VARCHAR(100)
) BEGIN
INSERT INTO
    TABELLA_DELLE_TABELLE (nome_tabella, creatore)
VALUES
    (p_nome_tabella, p_creatore);

END $$ DELIMITER;

-- insert into CHIAVI_ESTERNE_DELLE_TABELLE
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciChiaveEsterna (
    IN p_nome_tabella VARCHAR(20)       ,
    IN p_nome_attributo VARCHAR(100)    ,
    IN p_tabella_vincolata VARCHAR(20)  ,
    IN p_attributo_vincolato VARCHAR(100)
) BEGIN
-- Inserisce la chiave esterna nella tabella CHIAVI_ESTERNE_DELLE_TABELLE
INSERT INTO
    CHIAVI_ESTERNE_DELLE_TABELLE (
        nome_tabella      ,
        nome_attributo    ,
        tabella_vincolata ,
        attributo_vincolato
    )
VALUES
    (
        p_nome_tabella      ,
        p_nome_attributo    ,
        p_tabella_vincolata ,
        p_attributo_vincolato
    );

END $$ DELIMITER;

-- tabella delle chiavi
-- aggiungi chiave
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS AggiungiChiavePrimaria (
    IN p_nome_tabella VARCHAR(20),
    IN p_pezzo_chiave VARCHAR(100)
) BEGIN
INSERT INTO
    CHIAVI (nome_tabella, pezzo_chiave)
VALUES
    (p_nome_tabella, p_pezzo_chiave);

END $$ DELIMITER;

INSERT INTO
    TABELLA_DELLE_TABELLE (nome_tabella, creatore)
VALUES
    ('Tabella1', "professore@unibo.it")          ,
    ('Tabella2', "professore@unibo.it")          ,
    ('Tabella3', "professore@unibo.it")          ,
    ('Tabella4', "professore@unibo.it")          ,
    ('tabella_di_esempio', "professore@unibo.it"),
    ('provolone', "professore@unibo.it");

INSERT INTO
    `TAB_ATT` (
        `nome_tabella`  ,
        `nome_attributo`,
        `tipo_attributo`
    )
VALUES
    ('tabella_di_esempio', 'nome', 'VARCHAR')   ,
    ('tabella_di_esempio', 'cognome', 'VARCHAR'),
    ('tabella_di_esempio', 'eta', 'INT')        ,
    ('provolone', 'NomeR', 'VARCHAR')           ,
    ('provolone', 'CognomeR', 'VARCHAR')        ,
    ('provolone', 'das', 'INT');

-- get attributi tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetAttributiTabella (IN p_nome_tabella VARCHAR(20)) BEGIN
SELECT
    nome_attributo,
    IFNULL(
        (
            SELECT
                1
            FROM
                CHIAVI
            WHERE
                nome_tabella = p_nome_tabella
                AND pezzo_chiave = nome_attributo
            LIMIT
                1
        ),
        0
    ) AS is_key  ,
    tipo_attributo
FROM
    TAB_ATT
WHERE
    nome_tabella = p_nome_tabella
ORDER BY
    TAB_ATT.ID;

END $$ DELIMITER;

-- get chiavi esterne
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetChiaviEsterne (IN p_nome_tabella VARCHAR(20)) BEGIN
SELECT
    *
FROM
    CHIAVI_ESTERNE_DELLE_TABELLE
WHERE
    nome_tabella = p_nome_tabella;

END $$ DELIMITER;

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_insert_provolone AFTER
INSERT
    ON provolone FOR EACH ROW BEGIN
    -- Incrementa il numero di righe nella tabella
UPDATE TABELLA_DELLE_TABELLE
SET
    num_righe = num_righe + 1
WHERE
    nome_tabella = 'provolone';

END $$ DELIMITER;

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_insert_tabella_di_esempio AFTER
INSERT
    ON tabella_di_esempio FOR EACH ROW BEGIN
    -- Incrementa il numero di righe nella tabella
UPDATE TABELLA_DELLE_TABELLE
SET
    num_righe = num_righe + 1
WHERE
    nome_tabella = 'tabella_di_esempio';

END $$ DELIMITER;

INSERT INTO
    CHIAVI (nome_tabella, pezzo_chiave)
VALUES
    ('tabella_di_esempio', 'nome')   ,
    ('tabella_di_esempio', 'cognome');

INSERT INTO
    tabella_di_esempio (nome, cognome, eta)
VALUES
    ('Mario', 'Rossi', 30)   ,
    ('Luigi', 'Bianchi', 25) ,
    ('Giovanna', 'Verdi', 40);

INSERT INTO
    provolone (CognomeR, NomeR, das)
VALUES
    ('Verdi', 'Giovanna', 5000),
    ('Bianchi', 'Luigi', 255)  ,
    ('Rossi', 'Mario', 100);

INSERT INTO
    CHIAVI (nome_tabella, pezzo_chiave)
VALUES
    ('provolone', 'NomeR')   ,
    ('provolone', 'CognomeR');

INSERT INTO
    CHIAVI_ESTERNE_DELLE_TABELLE (
        nome_tabella      ,
        nome_attributo    ,
        tabella_vincolata ,
        attributo_vincolato
    )
VALUES
    (
        'provolone'         ,
        'NomeR'             ,
        'tabella_di_esempio',
        'nome'
    ),
    (
        'provolone'         ,
        'CognomeR'          ,
        'tabella_di_esempio',
        'cognome'
    );

-- get ID quesito
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetIDQuesitoTest (
    IN p_test_associato VARCHAR(100),
    IN p_numero_quesito INT
) BEGIN
SELECT
    ID
FROM
    QUESITO
WHERE
    test_associato = p_test_associato
    AND numero_quesito = p_numero_quesito;

END $$ DELIMITER;

-- inserisci quesito-tabella
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS InserisciQuesitoTabella (
    IN p_id_quesito INT         ,
    IN p_nome_tabella VARCHAR(20)
) BEGIN
INSERT INTO
    QUESITI_TABELLA (id_quesito, nome_tabella)
VALUES
    (p_id_quesito, p_nome_tabella);

END $$ DELIMITER;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS GetTabelleQuesito (
    IN p_numero_quesito INT        ,
    IN p_test_associato VARCHAR(100)
) BEGIN
SELECT DISTINCT
    (nome_tabella) ,
    id_quesito AS ID
FROM
    QUESITO
    JOIN `QUESITI_TABELLA` ON QUESITO.ID = `QUESITI_TABELLA`.id_quesito
WHERE
    QUESITO.test_associato = p_test_associato;

END $$ DELIMITER;

-- INSERT INTO
--     QUESITI_TABELLA (id_quesito, nome_tabella)
-- VALUES
--     (1, 'tabella_di_esempio'),
--     (1, 'provolone')         ,
--     (2, 'tabella_di_esempio'),
--     (3, 'tabella_di_esempio'),
--     (4, 'tabella_di_esempio'),
--     (5, 'tabella_di_esempio'),
--     (6, 'tabella_di_esempio');