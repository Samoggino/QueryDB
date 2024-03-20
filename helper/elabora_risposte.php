<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se il titolo del test è stato passato tramite POST
    if (isset($_POST['test_associato'])) {
        $test_associato = $_POST['test_associato'];

        echo "<script> console.log('utente: " . $_SESSION['email'] . "');</script>";
        $email_studente = $_SESSION['email'];

        // Assicurati che la connessione al database sia stabilita correttamente
        try {
            $db = connectToDatabaseMYSQL();

            // Ciclare attraverso i dati inviati dal form per elaborare le risposte
            foreach ($_POST as $campo => $opzione_selezionata) {
                // Verifica se il campo è una risposta a un quesito (i campi iniziano con "quesito")
                if (substr($campo, 0, 7) === "quesito") {
                    // Ottenere il numero del quesito dal nome del campo
                    $n_quesito = substr($campo, 7);

                    // Verifica se il quesito è aperto o chiuso
                    $sql = "CALL GetTipoQuesito(:numero_quesito, :test_associato);";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':numero_quesito', $n_quesito);
                    $stmt->bindParam(':test_associato', $test_associato);
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tipo_quesito = $row['tipo_quesito'];

                    // Inserisci la risposta nel database in base al tipo di quesito
                    /*      if ($tipo_quesito == 'APERTO') {
                        // Preparare la query per inserire la risposta a un quesito aperto
                        $sql_inserimento_aperto = "INSERT INTO RISPOSTA_QUESITO_APERTO (test_associato, numero_quesito, email_studente, risposta) VALUES (:test_associato, :numero_quesito, :email_studente, :risposta)";

                        // Preparare lo statement
                        $statement_aperto = $db->prepare($sql_inserimento_aperto);

                        // Associa i parametri e esegui l'inserimento
                        $statement_aperto->bindParam(':test_associato', $test_associato);
                        $statement_aperto->bindParam(':numero_quesito', $numero_quesito);
                        $statement_aperto->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                        $statement_aperto->bindParam(':risposta', $opzione_selezionata);
                        $statement_aperto->execute();
                    } else */
                    if ($tipo_quesito == 'CHIUSO') {

                        // prendi il quesito e verifica se la risposta è corretta
                        $sql = "SELECT numero_opzione FROM OPZIONE_QUESITO_CHIUSO WHERE test_associato = :test_associato AND numero_quesito = :numero_quesito AND is_corretta = 'TRUE'";
                        $statement = $db->prepare($sql);
                        $statement->bindParam(':test_associato', $test_associato);
                        $statement->bindParam(':numero_quesito', $n_quesito);
                        $statement->execute();
                        $opzioni_corrette = $statement->fetchAll(PDO::FETCH_ASSOC);

                        $insert_q_chiuso = "CALL InserisciRispostaQuesitoChiuso(:test_associato, :numero_quesito, :email_studente, :scelta, :esito);";

                        // Preparare lo statement
                        $statement_chiuso = $db->prepare($insert_q_chiuso);

                        $esito = 'SBAGLIATA';
                        // Associa i parametri e esegui l'inserimento
                        $statement_chiuso->bindParam(':test_associato', $test_associato);
                        $statement_chiuso->bindParam(':numero_quesito', $n_quesito);
                        $statement_chiuso->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                        $statement_chiuso->bindParam(':scelta', $opzione_selezionata);
                        $statement_chiuso->bindParam(':esito', $esito);


                        echo "<script>console.log($opzione_selezionata)</script>";

                        foreach ($opzioni_corrette as $opzione) {
                            if ($opzione['numero_opzione'] == $opzione_selezionata) {
                                $esito = 'GIUSTA';
                                echo "<script> console.log('numero opzione: " . $opzione['numero_opzione'] . " risposta: " . $opzione_selezionata . " corretta');</script>";
                            }
                        }

                        echo "<script> console.log('test_associato: " . $test_associato . "');</script>";
                        echo "<script> console.log('email_studente: " . $email_studente . "');</script>  ";
                        echo "<script> console.log('numero_quesito: " . $n_quesito . "');</script>";
                        echo "<script> console.log('scelta: " . $opzione_selezionata . "');</script>";
                        echo "<script> console.log('esito: " . $esito . "');</script>";

                        try {
                            $statement_chiuso->execute();
                        } catch (\Throwable $th) {
                            echo "<script> console.log('errore: " . $th->getMessage() . "');</script>";
                        }
                    }
                }
            }

            // Chiudi la connessione al database
            $db = null;

            // Reindirizza alla pagina dei risultati
            header("Location: ../pages/studente/risultati_test.php?test_associato=" . $test_associato);
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
