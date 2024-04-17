<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['email']) == false) {
    header('Location: ../index.php');
}

$db = connectToDatabaseMYSQL();
$sql = "CALL GetClassificaRisposteGiuste();";

$stmt = $db->prepare($sql);
$stmt->execute();
$classificaPrecisione = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$sql = "CALL GetClassificaTestCompletati();";
$stmt = $db->prepare($sql);
$stmt->execute();
$classificaTestCompletati = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$sql = "CALL GetClassificaQuesitiPerNumeroRisposte()";
$stmt = $db->prepare($sql);
$stmt->execute();
$classificaQuesitiPerNumeroRisposte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// select matricola ed evidenzia i record di quello studente
$sql = "CALL GetMatricola(:email_studente);";
$stmt = $db->prepare($sql);
$stmt->bindParam(':email_studente', $_SESSION['email']);
$stmt->execute();
$matricola = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$db = null;

function checkMatricola($row)
{
    global $matricola;
    if ($row['matricola'] == $matricola['matricola']) {
        echo "<td style='color:green;'>";
        echo  $row['matricola'];
    } else {
        echo "<td>";
        echo $row['matricola'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classifiche</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/eseguiTest.css">
    <link rel="stylesheet" href="../styles/classifiche.css">
</head>

<body>

    <h1>Classifiche</h1>
    <div class="container-classifiche">

        <div class="blocco-classifica">
            <h2>Precisione delle risposte</h2>
            <table>
                <tr>
                    <th>Matricola</th>
                    <th>Percentuale risposte corrette</th>
                </tr>
                <?php
                foreach ($classificaPrecisione as $row) {
                    echo "<tr>";
                    checkMatricola($row);
                    echo "</td>";
                    echo "<td>" . $row['Rapporto'] * 100 . "%</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div class="blocco-classifica">
            <h2>Test completati</h2>
            <table>
                <tr>
                    <th>Matricola</th>
                    <th>Test completati</th>
                </tr>
                <?php
                foreach ($classificaTestCompletati as $row) {
                    echo "<tr>";
                    checkMatricola($row);
                    echo "<td>" . $row['Test_conclusi'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div class="blocco-classifica">
            <h2>Quesiti con maggior numero di interazioni</h2>
            <table>
                <tr>
                    <th>Test</th>
                    <th>Quesito</th>
                    <th>Numero di risposte</th>
                </tr>
                <?php
                foreach ($classificaQuesitiPerNumeroRisposte as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['test_associato'] . "</td>";
                    echo "<td>" . $row['numero_quesito'] . "</td>";
                    echo "<td>" . $row['numero_risposte'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </div>

</body>

</html>