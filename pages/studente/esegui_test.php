<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assicurati che il parametro test_associato sia stato passato tramite GET
if (isset($_GET['test_associato'])) {
    $tests = $_GET['test_associato'];

    echo "<script> console.log('test scelto: " . $tests . "');</script>";


    try {
        $db = connectToDatabaseMYSQL();
        test_gia_svolto($tests, $db);

        // Prepara la query per selezionare i quesiti associati al test
        $sql = "CALL GetQuesitiTest(:test_associato);";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $tests);
        $stmt->execute();
        $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        echo "<h1>" . strtoupper($tests) . "</h1>";

        // Se non ci sono quesiti per questo test, mostra un messaggio
        if (count($quesiti) == 0) {
            echo "<div class='vuoto'><h1>Non ci sono quesiti per questo test</h1></div>";
        } else {
            // Mostra i quesiti nel form
            echo "<form method='post' action='../../helper/elabora_risposte.php'>";
            echo "<input type='hidden' name='test_associato' value='" . $tests . "'>";

            foreach ($quesiti as $quesito) {
                build_view_quesito($quesito, $tests, $db);
            }

            echo "<input type='submit' value='Invia risposte'>";
            echo "</form>";
        }

        // Chiudi la connessione al database
        $db = null;
    } catch (PDOException $e) {
        // Gestisci eventuali eccezioni
        echo "Errore di connessione al database: " . $e->getMessage();
    }
} else {
    // Messaggio di errore se il parametro test_associato non è stato passato
    echo "Errore: il parametro test_associato non è stato fornito.";
}


function build_view_quesito($quesito, $test, $db)
{
    if ($quesito['tipo_quesito'] == 'APERTO') {
        q_aperto($quesito);
    } elseif ($quesito['tipo_quesito'] == 'CHIUSO') {
        q_chiuso($quesito, $test, $db);
    }
}

function q_chiuso($quesito, $test, $db)
{
    echo "<div class='chiusi'>";
    echo "<h3>" . $quesito["numero_quesito"] . ". " . $quesito['descrizione'] . "</h3>";

    // Seleziona le opzioni per questo quesito chiuso
    $sql_opzioni = "CALL GetOpzioniQuesitoChiuso(:test_associato, :numero_quesito);";
    $statement_opzioni = $db->prepare($sql_opzioni);
    $statement_opzioni->bindParam(':numero_quesito', $quesito['numero_quesito']);
    $statement_opzioni->bindParam(':test_associato', $test);
    $statement_opzioni->execute();
    $opzioni = $statement_opzioni->fetchAll(PDO::FETCH_ASSOC);
    $statement_opzioni->closeCursor();

    foreach ($opzioni as $opzione) {
        echo "<input type='radio' name='quesito" . $quesito['numero_quesito'] . "' value='" . $opzione['numero_opzione'] . "'>" . $opzione['numero_opzione'] . " " . $opzione['testo'] . "<br>";
    }

    echo "</div>";
}

function q_aperto($quesito)
{
    echo "<div class='aperti'>";
    echo "<h3>"  . $quesito["numero_quesito"] . ". " . $quesito['descrizione'] . "</h3>";
    echo "<input type='text' name='quesito" . $quesito['numero_quesito'] . "'><br>";
    echo "</div>";
}


function test_gia_svolto($test, $db)
{
    $sql = "SELECT * FROM SVOLGIMENTO_TEST WHERE titolo_test = :test_associato AND email_studente = :email_studente";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email_studente', $_SESSION['email']);
    $stmt->bindParam(':test_associato', $test);
    $stmt->execute();
    $risultato = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // se il test è già stato svolto, reindirizza alla pagina dei risultati
    if ($risultato['stato'] == 'CONCLUSO') {
        header("Location: ../../pages/studente/risultati_test.php?test_associato=" . $test);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Test - <?php echo $_GET['test_associato']; ?></title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
</head>

<body>

</body>

</html>