<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assicurati che il parametro titolo_test sia stato passato tramite GET
if (isset($_GET['titolo_test'])) {
    $test = $_GET['titolo_test'];
    echo "<script> console.log('test scelto: " . $test . "');</script>";
    echo "<script> console.log('utente: " . $_SESSION['email'] . "');</script>";
    try {

        // per ogni quesito, se è aperto, verifica che la risposta sia la stessa di quella nel database
        // se è chiuso, verifica che la scelta corrisponda alla risposta che ha esito TRUE
        $db = connectToDatabaseMYSQL();

        // Prepara la query per selezionare i quesiti associati al test
        $sql = "SELECT * FROM QUESITO WHERE test_associato = :titolo_test";
        $result = $db->query($sql);
        $quesiti = $result->fetchAll(PDO::FETCH_ASSOC);

        $risultati = array();
        $punteggio = 0;
        $punteggio_massimo = 0;

        foreach ($quesiti as $quesito) {
            if ($quesito['tipo_quesito'] == 'APERTO') {
                // Prepara la query per selezionare la risposta data dallo studente
                $sql_risposta = "SELECT risposta FROM RISPOSTA_QUESITO_APERTO WHERE test_associato = :titolo_test AND numero_quesito = :numero_quesito AND email_studente = :email_studente";
                $statement_risposta = $db->prepare($sql_risposta);
                $statement_risposta->bindParam(':titolo_test', $test);
                $statement_risposta->bindParam(':numero_quesito', $quesito['numero_quesito']);
                $statement_risposta->bindParam(':email_studente', $_SESSION['email']);
                $statement_risposta->execute();
                $risposta = $statement_risposta->fetch(PDO::FETCH_ASSOC);

                // Verifica se la risposta data dallo studente è corretta
                if ($risposta['risposta'] == $quesito['risposta_corretta']) {
                    $risultati[$quesito['numero_quesito']] = true;
                    $punteggio++;
                } else {
                    $risultati[$quesito['numero_quesito']] = false;
                }
                $punteggio_massimo++;
            } else if ($quesito['tipo_quesito'] == 'CHIUSO') {
                // Prepara la query per selezionare la scelta fatta dallo studente
                $sql_scelta = "SELECT scelta FROM RISPOSTA_QUESITO_CHIUSO WHERE test_associato = :titolo_test AND numero_quesito = :numero_quesito AND email_studente = :email_studente";
                $statement_scelta = $db->prepare($sql_scelta);
                $statement_scelta->bindParam(':titolo_test', $test);
                $statement_scelta->bindParam(':numero_quesito', $quesito['numero_quesito']);
                $statement_scelta->bindParam(':email_studente', $_SESSION['email']);
                $statement_scelta->execute();
                $scelta = $statement_scelta->fetch(PDO::FETCH_ASSOC);

                // Verifica se la scelta fatta dal lo studente è corretta
                if ($scelta['scelta'] == $quesito['risposta_corretta']) {
                    $risultati[$quesito['numero_quesito']] = true;
                    $punteggio++;
                } else {
                    $risultati[$quesito['numero_quesito']] = false;
                }
                $punteggio_massimo++;
            }
        }


        echo "<h1>Risultati del test " . strtoupper($test) . "</h1>";
        echo "<h2>Punteggio: " . $punteggio . " / " . $punteggio_massimo . "</h2>";
    } catch (PDOException $e) {
        echo "<script> console.log('Errore: " . $e->getMessage() . "');</script>";
    }
}
