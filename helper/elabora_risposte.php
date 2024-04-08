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

            // TODO: modificare come sono fatte le sottorisposte, 
            // perchè non avranno più tutti i riferimenti di test, questito e utente, ma semplicemente,
            // il riferimento alla risposta padre ovvero un ID auto_increment 

            // Ciclare attraverso i dati inviati dal form per elaborare le risposte
            foreach ($_POST as $campo => $scelta) {
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

                    // stampa numero quesito
                    echo "<script>console.log('Numero quesito '  + $n_quesito)</script>";

                    $stmt->closeCursor();
                    $tipo_quesito = $row['tipo_quesito'];

                    // Inserisci la risposta nel database in base al tipo di quesito
                    if ($tipo_quesito == 'APERTO') {
                        try {
                            //code...
                            // Prendi la risposta a un quesito aperto
                            $sql = "CALL GetSoluzioneQuesitoAperto(:test_associato, :numero_quesito);";
                            // $sql = "SELECT * FROM QUESITO_APERTO_SOLUZIONE WHERE test_associato = :test_associato AND numero_quesito = :numero_quesito;";
                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':test_associato', $test_associato);
                            $stmt->bindParam(':numero_quesito', $n_quesito);
                            $stmt->execute();
                            $soluzione = $stmt->fetch(PDO::FETCH_ASSOC);
                            $stmt->closeCursor();

                            $esito_aperta = "SBAGLIATA";

                            echo "<script>console.log('Soluzione: " . $soluzione['soluzione_professore'] . "')</script>";
                            echo "<script>console.log('Scelta: " . $scelta . "')</script>";
                            if ($soluzione['soluzione_professore'] == $scelta) {
                                $esito_aperta = "GIUSTA";
                                echo "<script>console.log('Risposta giusta')</script>";
                            }

                            // Preparare la query per inserire la risposta a un quesito aperto
                            $sql_inserimento_aperto = "CALL InserisciRispostaQuesitoAperto(:test_associato, :numero_quesito, :email_studente, :risposta, :esito);";
                            // Preparare lo statement
                            $statement_aperto = $db->prepare($sql_inserimento_aperto);

                            // Associa i parametri e esegui l'inserimento
                            $statement_aperto->bindParam(':test_associato', $test_associato);
                            $statement_aperto->bindParam(':numero_quesito', $n_quesito);
                            $statement_aperto->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                            $statement_aperto->bindParam(':risposta', $scelta);
                            $statement_aperto->bindParam(':esito', $esito_aperta);
                            $statement_aperto->execute();
                        } catch (\Throwable $th) {
                            echo "Errore nella risposta aperta <br>"  . $th->getMessage();
                        }
                    } else if ($tipo_quesito == 'CHIUSO') {

                        try {
                            //code...

                            // prendi il quesito e verifica se la risposta è corretta
                            $sql = "CALL GetOpzioniCorrette(:test_associato, :numero_quesito);";
                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':test_associato', $test_associato);
                            $stmt->bindParam(':numero_quesito', $n_quesito);
                            $stmt->execute();
                            $opzioni_corrette = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $stmt->closeCursor();

                            $insert_q_chiuso = "CALL InserisciRispostaQuesitoChiuso(:test_associato, :numero_quesito, :email_studente, :opzione_scelta, :esito);";

                            // Preparare lo statement
                            $statement_chiuso = $db->prepare($insert_q_chiuso);

                            $esito_chiuso = 'SBAGLIATA';
                            // Associa i parametri e esegui l'inserimento
                            $statement_chiuso->bindParam(':test_associato', $test_associato);
                            $statement_chiuso->bindParam(':numero_quesito', $n_quesito);
                            $statement_chiuso->bindParam(':email_studente', $email_studente); // Assumi che l'email dello studente sia già disponibile nella sessione
                            $statement_chiuso->bindParam(':opzione_scelta', $scelta);
                            $statement_chiuso->bindParam(':esito', $esito_chiuso);

                            foreach ($opzioni_corrette as $opzione)
                                if ($opzione['numero_opzione'] == $scelta)
                                    $esito_chiuso = 'GIUSTA';

                            $statement_chiuso->execute();
                            $statement_chiuso->closeCursor();
                        } catch (\Throwable $th) {
                            echo "Errore nella risposta chiusa <br>" . $th->getMessage();
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
