<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
            <h2>Classifica precisione</h2>
            <table>
                <tr>
                    <th>Matricola</th>
                    <th>Rapporto risposte corrette</th>
                </tr>
                <?php
                foreach ($classificaPrecisione as $row) {
                    echo "<tr>";

                    checkMatricola($row);

                    echo "</td>";
                    echo "<td>" . $row['Rapporto'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div class="blocco-classifica">
            <h2>Classifica test completati</h2>
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
        <!-- TODO: PLACEHOLDER -->
        <div class="blocco-classifica">
            <h2>Classifica test completati</h2>
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
    </div>

</body>

</html>