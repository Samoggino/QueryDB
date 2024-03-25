<?php
session_start();
require "./query.php";
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// FIXME: la parte che mostra la risposta non funziona

// FIXME: problema sul set dello stato del test

$test_associato = $_GET['test_associato'];
echo "<script> console.log('utente: " . $test_associato . "');</script>";
$email_studente = $_SESSION['email'];

// Assicurati che la connessione al database sia stabilita correttamente
$db = connectToDatabaseMYSQL();

// Preparare la query per ottenere tutti i test
$sql = "CALL GetAllTests();";

// Preparare lo statement
$stmt = $db->prepare($sql);
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();


if ($tests == null || count($tests) == 0) {
    echo "<h1>Non hai svolto alcun test</h1>";
    throw new Exception("Non hai svolto alcun test");
}

foreach ($tests as $key => $test) {
    $test_associato = $test['titolo'];

    $risposte = getRisposte($test_associato, $email_studente);

    if (count($risposte) == 0) {
        continue;
    }

    if ($test['VisualizzaRisposte'] == 0) {
        if (isset($_POST['visualizzaRisposteHidden']) && $_POST['visualizzaRisposteHidden'] == 0)
            continue;
    }

    // Stampare il titolo del test e le risposte
    echo "<table>";
    echo "<tr><th colspan='5'>" . $test_associato . "</th></tr>"; // Utilizzo colspan per estendere il titolo su 4 colonne
    echo "<tr>";
    echo "<th>Numero quesito</th>";
    echo "<th>Data</th>";
    echo "<th>Risposta dello studente</th>";
    echo "<th>Risposta del professore</th>";
    echo "<th>Esito</th>";
    echo "</tr>";
    foreach ($risposte as $risposta) {
        echo "<tr>";
        echo "<td>" . $risposta['numero_quesito'] . "</td>";
        echo "<td>" . $risposta['in_data'] . "</td>";


        if ($risposta['tipo_risposta'] == 'CHIUSA') {
            $sql = "CALL GetSceltaQuesitoChiuso(:test_associato, :numero_quesito, :email_studente);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':test_associato', $test_associato);
            $stmt->bindParam(':numero_quesito', $risposta['numero_quesito']);
            $stmt->bindParam(':email_studente', $email_studente);
            $stmt->execute();
            $scelta = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo "<td>" . $scelta['scelta'] . "</td>";

            $sql = "CALL GetOpzioniCorrette(:test_associato, :numero_quesito)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':test_associato', $test_associato);
            $stmt->bindParam(':numero_quesito', $risposta['numero_quesito']);
            $stmt->execute();
            $opzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo "<td>" . $opzioni[0]['numero_opzione'] . "</td>";
        } elseif ($risposta['tipo_risposta'] == 'APERTA') {

            $sql = "CALL GetRispostaQuesitoAperto(:test_associato, :numero_quesito, :email_studente);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':test_associato', $test_associato);
            $stmt->bindParam(':numero_quesito', $risposta['numero_quesito']);
            $stmt->bindParam(':email_studente', $email_studente);
            $stmt->execute();
            $scelta = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo "<td>" . $scelta['risposta'] . "</td>";
            echo "<td> - </td>";
        }




        echo "<td>" . $risposta['esito'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">

    <title>Esito <?php echo $_GET['test_associato'] ?></title>

    <style>
        table {
            max-width: 50%;
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <p>
        <?php
        $sql = "SELECT * FROM RISPOSTA WHERE test_associato = :test_associato AND email_studente = :email_studente;";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $_GET['test_associato']);
        $stmt->bindParam(':email_studente', $_SESSION['email']);
        $stmt->execute();
        $risposte = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (count($risposte) == 0) {
            echo "Non hai svolto alcun test";
        } else {
        ?>
    </p>
    <form method="post" action="risultati_test.php?test_associato=<?php echo $_GET['test_associato'] ?>">
        <input type="hidden" id="visualizzaRisposteHidden" name="visualizzaRisposteHidden" value="1">
        <input type="checkbox" id="visualizzaRisposteCheckbox" name="visualizzaRisposteCheckbox" onclick="toggleVisualizzaRisposte()">
        <label for="visualizzaRisposteCheckbox">Attiva il campo visualizza_risposte</label>
        <input type="submit" value="Aggiorna">
    </form>

    <script>
        function toggleVisualizzaRisposte() {
            var checkbox = document.getElementById("visualizzaRisposteCheckbox");
            var hiddenInput = document.getElementById("visualizzaRisposteHidden");

            // Inverti il valore del campo nascosto quando il checkbox viene selezionato
            if (checkbox.checked) {
                hiddenInput.value = "0";
            } else {
                hiddenInput.value = "1";
            }
        }
    </script>
<?php } ?>
</body>


</html>