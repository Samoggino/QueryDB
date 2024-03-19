<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assicurati che il parametro titolo_test sia stato passato tramite GET
if (isset($_GET['titolo_test'])) {
    $test = $_GET['titolo_test'];

    echo "<script> console.log('test scelto: " . $test . "');</script>";

    try {
        $db = connectToDatabaseMYSQL();

        // Prepara la query per selezionare i quesiti associati al test
        $sql = "SELECT * FROM QUESITO WHERE test_associato = :titolo_test";

        $statement = $db->prepare($sql);
        $statement->bindParam(':titolo_test', $test);
        $statement->execute();
        $quesiti = $statement->fetchAll(PDO::FETCH_ASSOC);

        echo "<h1>" . strtoupper($test) . "</h1>";

        // Se non ci sono quesiti per questo test, mostra un messaggio
        if (count($quesiti) == 0) {
            echo "<div class='vuoto'><h1>Non ci sono quesiti per questo test</h1></div>";
        } else {
            // Mostra i quesiti nel form
            echo "<form method='post' action='../helper/elabora_risposte.php'>";
            echo "<input type='hidden' name='titolo_test' value='" . $test . "'>";

            foreach ($quesiti as $quesito) {
                if ($quesito['tipo_quesito'] == 'APERTO') {
                    echo "<div class='aperti'>";
                    echo "<h3>" . $quesito['descrizione'] . "</h3>";
                    echo "<input type='text' name='quesito" . $quesito['numero_quesito'] . "'><br>";
                    echo "</div>";
                } elseif ($quesito['tipo_quesito'] == 'CHIUSO') {
                    echo "<div class='chiusi'>";
                    echo "<h3>" . $quesito['descrizione'] . "</h3>";

                    // Seleziona le opzioni per questo quesito chiuso
                    $sql_opzioni = "SELECT * FROM OPZIONE_QUESITO_CHIUSO WHERE numero_quesito = :numero_quesito AND titolo_test = :titolo_test";
                    $statement_opzioni = $db->prepare($sql_opzioni);
                    $statement_opzioni->bindParam(':numero_quesito', $quesito['numero_quesito']);
                    $statement_opzioni->bindParam(':titolo_test', $test);
                    $statement_opzioni->execute();
                    $opzioni = $statement_opzioni->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($opzioni as $opzione) {
                        echo "<input type='radio' name='quesito" . $quesito['numero_quesito'] . "' value='" . $opzione['numero_opzione'] . "'>" . $opzione['numero_opzione'] . " " . $opzione['testo'] . "<br>";
                    }

                    echo "</div>";
                }
            }

            echo "<input type='submit' value='Invia risposte'>";
            echo "</form>";
        }
    } catch (PDOException $e) {
        // Gestisci eventuali eccezioni
        echo "Errore di connessione al database: " . $e->getMessage();
    }
} else {
    // Messaggio di errore se il parametro titolo_test non è stato passato
    echo "Errore: il parametro titolo_test non è stato fornito.";
}
