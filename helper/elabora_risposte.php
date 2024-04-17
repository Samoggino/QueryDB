<?php
session_start();
require '../helper/connessione_mysql.php';
require '../helper/check_closed.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se il titolo del test è stato passato tramite POST
    if (isset($_POST['test_associato'])) {

        $test_associato = $_POST['test_associato'];
        $email_studente = $_SESSION['email'];

        if (check_svolgimento($test_associato, $email_studente)) {
            echo "<script>console.log('Test già svolto')</script>";
            header("Location: ../pages/studente/risultati_test.php");
            exit();
        }
        // Assicurati che la connessione al database sia stabilita correttamente
        try {
            $db = connectToDatabaseMYSQL();

            echo json_encode($_POST, JSON_PRETTY_PRINT);
            // Ciclare attraverso i dati inviati dal form per elaborare le risposte
            foreach ($_POST as $campo => $scelta) {
                // Verifica se il campo è una risposta a un quesito (i campi iniziano con "quesito")
                if (substr($campo, 0, 7) === "quesito") {
                    // Ottenere il numero del quesito dal nome del campo
                    $numero_quesito = substr($campo, 7);

                    // Verifica se il quesito è aperto o chiuso
                    $sql = "CALL GetQuesitoTest(:test_associato, :numero_quesito);";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':test_associato', $test_associato);
                    $stmt->bindParam(':numero_quesito', $numero_quesito);
                    $stmt->execute();
                    $quesito = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();

                    $tipo_quesito = $quesito['tipo_quesito'];
                    $scelta = str_replace('"', "'", $scelta);

                    // // rimuovi gli accapo da scelta
                    // $scelta = str_replace("\n", "", $scelta);


                    echo '<script>console.log("Risposta: ' . $scelta . '")</script>';
                    // Inserisci la risposta nel database in base al tipo di quesito
                    if ($tipo_quesito == 'APERTO') {
                        // se scelta ha meno di 6 caratteri, allora non è stata data risposta

                        try {
                            $sql = "CALL GetSoluzioneQuesitoAperto(:id_quesito);";
                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':id_quesito', $quesito['ID']);
                            $stmt->execute();
                            $soluzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $stmt->closeCursor();

                            $esito_aperta = "SBAGLIATA";



                            if (strlen($scelta) < 6) {
                                echo "<script>console.log('Risposta non data')</script>";
                            } else {

                                foreach ($soluzioni as $soluzione) {
                                    try {
                                        $stmt = $db->prepare($soluzione['soluzione_professore']);
                                        $stmt->execute();
                                        $sol = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        $stmt->closeCursor();

                                        $stmt = $db->prepare($scelta);
                                        $stmt->execute();
                                        $sce = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        $stmt->closeCursor();

                                        if ($sol === $sce) {
                                            $esito_aperta = "GIUSTA";
                                            echo "<script>console.log('Risposta giusta')</script>";
                                            echo "Risposta giusta <br>";
                                            echo  json_encode($sol, JSON_PRETTY_PRINT) . "<br>";
                                            echo json_encode($sce, JSON_PRETTY_PRINT);
                                            break;
                                        } else {
                                            echo "<script>console.log('Risposta sbagliata')</script>";
                                        }
                                    } catch (\Throwable $th) {
                                        continue;
                                    }
                                }
                            }
                            // Preparare la query per inserire la risposta a un quesito aperto
                            $sql_inserimento_aperto = "CALL InserisciRispostaQuesitoAperto(:id_quesito, :email_studente, :risposta, :esito);";
                            // Preparare lo statement
                            $statement_aperto = $db->prepare($sql_inserimento_aperto);

                            echo "<script>console.log('ID Quesito: " . $quesito['ID'] . "')</script>";
                            echo "<script>console.log('Email Studente: " . $email_studente . "')</script>";

                            // Associa i parametri e esegui l'inserimento
                            $statement_aperto->bindParam(':id_quesito', $quesito['ID']);
                            $statement_aperto->bindParam(':email_studente', $email_studente);
                            $statement_aperto->bindParam(':risposta', $scelta);
                            $statement_aperto->bindParam(':esito', $esito_aperta);
                            $statement_aperto->execute();
                        } catch (\Throwable $th) {
                            echo "Errore nella risposta aperta in inserimento <br>"  . $th->getMessage();
                        }
                    } else if ($tipo_quesito == 'CHIUSO') {

                        try {

                            // prendi il quesito e verifica se la risposta è corretta
                            $sql = "CALL GetOpzioniCorrette(:id_quesito);";
                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':id_quesito', $quesito['ID']);
                            $stmt->execute();
                            $opzioni_corrette = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $stmt->closeCursor();

                            $insert_q_chiuso = "CALL InserisciRispostaQuesitoChiuso(:id_quesito, :email_studente, :opzione_scelta, :esito);";

                            // Preparare lo statement
                            $statement_chiuso = $db->prepare($insert_q_chiuso);

                            $esito_chiuso = 'SBAGLIATA';
                            // Associa i parametri e esegui l'inserimento
                            $statement_chiuso->bindParam(':id_quesito', $quesito['ID']);
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
            // header("Location: ../pages/studente/risultati_test.php");
            // exit();
            // echo "<button onclick='window.location.href = \"../pages/studente/risultati_test.php?test_associato=" . $test_associato . "\";'>Visualizza Risultati</button>";
        } catch (PDOException $e) {
            // Gestisci eventuali errori di connessione al database
            echo "Errore di connessione al database: " . $e->getMessage();
        }
    } else {
        header("Location: ../pages/studente/studente.php");
        exit();
    }
} else {
    header("Location: ../pages/studente/studente.php");
    exit();
}
