<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se il titolo del test è stato passato tramite POST
    if (isset($_POST['titolo_test'])) {
        $titolo_test = $_POST['titolo_test'];

        echo "<script> console.log('utente: " . $_SESSION['email'] . "');</script>";
        $email_studente = $_SESSION['email'];

        // Assicurati che la connessione al database sia stabilita correttamente
        try {
            $db = connectToDatabaseMYSQL();

            // Ciclare attraverso i dati inviati dal form per elaborare le risposte
            foreach ($_POST as $campo => $valore) {
                // Verifica se il campo è una risposta a un quesito (i campi iniziano con "quesito")
                if (substr($campo, 0, 7) === "quesito") {
                    // Ottenere il numero del quesito dal nome del campo
                    $numero_quesito = substr($campo, 7);

                    // Verifica se il quesito è aperto o chiuso
                    $sql_tipo_quesito = "SELECT tipo_quesito FROM QUESITO WHERE numero_quesito = :numero_quesito AND test_associato = :titolo_test";
                    $statement_tipo_quesito = $db->prepare($sql_tipo_quesito);
                    $statement_tipo_quesito->bindParam(':numero_quesito', $numero_quesito);
                    $statement_tipo_quesito->bindParam(':titolo_test', $titolo_test);
                    $statement_tipo_quesito->execute();
                    $row = $statement_tipo_quesito->fetch(PDO::FETCH_ASSOC);
                    $tipo_quesito = $row['tipo_quesito'];

                    // Inserisci la risposta nel database in base al tipo di quesito
                    if ($tipo_quesito == 'APERTO') {
                        // Preparare la query per inserire la risposta a un quesito aperto
                        $sql_inserimento_aperto = "INSERT INTO RISPOSTA_QUESITO_APERTO (test_associato, numero_quesito, email_studente, risposta) VALUES (:titolo_test, :numero_quesito, :email_studente, :risposta)";

                        // Preparare lo statement
                        $statement_aperto = $db->prepare($sql_inserimento_aperto);

                        // Associa i parametri e esegui l'inserimento
                        $statement_aperto->bindParam(':titolo_test', $titolo_test);
                        $statement_aperto->bindParam(':numero_quesito', $numero_quesito);
                        $statement_aperto->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                        $statement_aperto->bindParam(':risposta', $valore);
                        $statement_aperto->execute();
                    } elseif ($tipo_quesito == 'CHIUSO') {
                        // Preparare la query per inserire la risposta a un quesito chiuso
                        $sql_inserimento_chiuso = "INSERT INTO RISPOSTA_QUESITO_CHIUSO (test_associato, numero_quesito, scelta, email_studente) VALUES (:titolo_test, :numero_quesito, :scelta, :email_studente)";

                        // Preparare lo statement
                        $statement_chiuso = $db->prepare($sql_inserimento_chiuso);

                        // Associa i parametri e esegui l'inserimento
                        $statement_chiuso->bindParam(':titolo_test', $titolo_test);
                        $statement_chiuso->bindParam(':numero_quesito', $numero_quesito);
                        $statement_chiuso->bindParam(':scelta', $valore);
                        $statement_chiuso->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                        $statement_chiuso->execute();
                    }
                }
            }

            // Chiudi la connessione al database
            $db = null;

            // Reindirizza alla pagina dei risultati
            header("Location: ../pages/risultati_test.php?titolo_test=" . $titolo_test);
            exit();
        } catch (PDOException $e) {
            // Gestisci eventuali errori di connessione al database
            echo "Errore di connessione al database: " . $e->getMessage();
        }
    } else {
        // Gestire il caso in cui il titolo del test non sia stato fornito
        echo "Errore: il titolo del test non è stato fornito.";
    }
} else {
    // Gestire il caso in cui la richiesta non sia di tipo POST
    echo "Errore: richiesta non valida.";
}
