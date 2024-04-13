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

    $sql_opzioni = "CALL GetOpzioniQuesitoChiuso(:test_associato, :numero_quesito);";
    $statement_opzioni = $db->prepare($sql_opzioni);
    $statement_opzioni->bindParam(':numero_quesito', $quesito['numero_quesito']);
    $statement_opzioni->bindParam(':test_associato', $test);
    $statement_opzioni->execute();
    $opzioni = $statement_opzioni->fetchAll(PDO::FETCH_ASSOC);
    $statement_opzioni->closeCursor();

    foreach ($opzioni as $opzione) {
        echo "<input type='radio' name='quesito" . $quesito['numero_quesito'] . "' value='" . $opzione['numero_opzione'] . "'>" . " " . $opzione['testo'] . "<br>";
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
    $db = connectToDatabaseMYSQL();
    // $sql = "CALL  VerificaTestConcluso (:email_studente, :test_associato, @test_closed);";
    // Preparazione e esecuzione della stored procedure
    $stmt = $db->prepare("CALL VerificaTestConcluso(?, ?, @is_closed)");
    $stmt->bindParam(1, $_SESSION['email'], PDO::PARAM_STR);
    $stmt->bindParam(2, $test, PDO::PARAM_STR);
    $stmt->execute();

    // Recupero del valore del parametro di output
    $stmt = $db->query("SELECT @is_closed AS is_closed");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_closed = $result['is_closed'];

    if ($is_closed == 1) {
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
    <link rel="stylesheet" href="../../styles/eseguiTest.css">
    <link rel="stylesheet" href="../../styles/global.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Acme&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap');
    </style>
</head>

<body>
    <h1>Esegui: <span style="color: red;"><?php echo strtoupper($_GET['test_associato']); ?></span></h1>

    <div class="container">

        <div id="quesiti">
            <?php
            $db = connectToDatabaseMYSQL();

            // Se non ci sono quesiti per questo test, mostra un messaggio
            if (count($quesiti) == 0) {
                echo "<div class='vuoto'><h1>Non ci sono quesiti per questo test</h1></div>";
            } else {
            ?>
                <form method='post' action='../../helper/elabora_risposte.php'>
                    <input type='hidden' name='test_associato' value='" . $tests . "'>
                    <?php
                    // Mostra i quesiti nel form
                    foreach ($quesiti as $quesito) {
                        build_view_quesito($quesito, $tests, $db);
                    }
                    ?>
                    <input type='submit' value='Invia risposte'>
                </form>
            <?php
            }
            $db = null;
            ?>
        </div>

        <?php
        include '../../helper/print_table.php';
        // mostra le tabelle a cui fa riferimento questo quesito
        $test = $_GET['test_associato'];

        $db = connectToDatabaseMYSQL();
        $sql = "CALL GetTabelleQuesito(:test_associato);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test);
        $stmt->execute();
        $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        ?>

        <div id="tabelle-esterne">

            <div id="vincoli">
                <?php
                include '../../helper/print_vincoli.php';
                if (count($tabelle) > 0 && $tabelle != null) {
                ?>
                    <h2>Vincoli di integrità</h2>
                <?php }
                foreach ($tabelle as $tabella) {
                    stampaVincoli($tabella['nome_tabella']);
                }
                ?>
            </div>
            <div id="tables">
                <?php
                foreach ($tabelle as $tabella) {
                    generateTable($tabella['nome_tabella']);
                    echo "<br>";
                }
                ?>
            </div>
        </div>
    </div>

</body>

</html>