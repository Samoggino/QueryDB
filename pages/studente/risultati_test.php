<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$test_associato = $_GET['test_associato'];
$email_studente = $_SESSION['email'];

// Assicurati che la connessione al database sia stabilita correttamente
$db = connectToDatabaseMYSQL();

// Preparare la query per ottenere tutti i test
$sql = "SELECT * FROM TEST";

// Preparare lo statement
$statement = $db->prepare($sql);
$statement->execute();
$tests = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($tests as $key => $test) {
    $test_associato = $test['titolo'];


    $sql = "CALL GetLatestTestResponses(:test_associato, :email_studente);";

    $statement = $db->prepare($sql);
    $statement->bindParam(':test_associato', $test_associato);
    $statement->bindParam(':email_studente', $email_studente);
    $statement->execute();
    $risposte = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Stampare il titolo del test e le risposte
    echo "<table>";
    echo "<tr><th colspan='4'>" . $test_associato . "</th></tr>"; // Utilizzo colspan per estendere il titolo su 4 colonne
    echo "<tr>";
    echo "<th>Numero quesito</th>";
    echo "<th>Data</th>";
    echo "<th>Scelta</th>";
    echo "<th>Esito</th>";
    echo "</tr>";
    foreach ($risposte as $risposta) {
        echo "<tr>";
        echo "<td>" . $risposta['numero_quesito'] . "</td>";
        echo "<td>" . $risposta['in_data'] . "</td>";
        echo "<td>" . $risposta['scelta'] . "</td>";
        echo "<td>" . $risposta['esito'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br>";
}

// Preparare la stored procedure per ottenere le risposte

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esito <?php echo $test_associato ?></title>

    <style>
        table {
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

<body></body>

</html>