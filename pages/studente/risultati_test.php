<?php
session_start();
require "./query.php";
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['email']) == false || $_SESSION['ruolo'] != "STUDENTE") {
    header('Location: ../index.php');
}

$db = connectToDatabaseMYSQL();
$sql = "CALL CheckRisultatiStudente(:email);";
$stmt = $db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
$stmt->execute();
$test_concluso_bool = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if ($test_concluso_bool['check'] == 0) {
    echo "<script>alert('Non hai concluso alcun test')
    window.location.href = '/pages/studente/studente.php';</script>";
}
function costruisciTabellaRisultati()
{
    $email_studente = $_SESSION['email'];
    // Assicurati che la connessione al database sia stabilita correttamente
    $db = connectToDatabaseMYSQL();

    $sql = "CALL GetTestDelloStudente(:email);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email_studente, PDO::PARAM_STR);
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach ($tests as $key => $test) {

        $test_associato = $test['titolo_test'];

        $risposte = getRisposte($test_associato, $email_studente);


        if ($test['stato'] == "CONCLUSO") {
            echo  '<div class="widget-classifica">';
            // Stampare il titolo del test e le risposte
            echo "<table>";
            echo "<tr><th id='test' class='titolo-tabella' colspan='5'>" . $test_associato;

            echo "</th></tr>"; // Utilizzo colspan per estendere il titolo su 4 colonne
            echo "<tr>";
            echo "<th>Numero quesito</th>";
            echo "<th>Data</th>";
            echo "<th>Risposta dello studente</th>";
            echo "<th>Risposta del professore</th>";
            echo "<th>Esito</th>";
            echo "</tr>";

            if (count($risposte) == 0) {
                echo "<tr><td colspan='5'>Non hai inserito risposte, ma il test ora è concluso</td></tr>";
                echo "</table>";
                echo "</div>";
                echo "<br>";
                continue;
            }
            foreach ($risposte as $risposta) {
                $id_quesito = $risposta['id_quesito'];
                echo "<tr>";
                echo "<td>" . $risposta['numero_quesito'] . "</td>";
                echo "<td id='col-data'>" . $risposta['in_data'] . "</td>";

                if ($risposta['tipo_risposta'] == 'CHIUSA') {
                    $sql = "CALL GetSceltaQuesitoChiuso(:id_quesito, :email_studente);";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito);
                    $stmt->bindParam(':email_studente', $email_studente);
                    $stmt->execute();
                    $scelta = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    echo "<td>" . $scelta['opzione_scelta'] . "</td>";

                    $sql = "CALL GetOpzioniCorrette(:id_quesito)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito);
                    $stmt->execute();
                    $opzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();

                    echo "<td>" . $opzioni[0]['numero_opzione'] . "</td>";
                } elseif ($risposta['tipo_risposta'] == 'APERTA') {

                    $sql = "CALL GetRispostaQuesitoAperto(:id_quesito, :email_studente);";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito);
                    $stmt->bindParam(':email_studente', $email_studente);
                    $stmt->execute();
                    $scelta = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    echo "<td>" . $scelta['risposta'] . "</td>";
                    echo "<td>" . mostraSoluzione($scelta['esito'], $scelta['risposta'], $id_quesito) . "</td>";
                }

                echo "<td>" . $risposta['esito'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            echo "<br>";
        }
    }
}


function mostraSoluzione($esito, $risposta_studente, $id_quesito)
{

    // TODO: mostra la soluzione se il test è concluso, a prescindere dall'esito

    if ($esito == "GIUSTA") {
        $db = connectToDatabaseMYSQL();
        $sql = "CALL GetSoluzioneQuesitoAperto(:id_quesito);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_quesito', $id_quesito);
        $stmt->execute();
        $soluzioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach ($soluzioni as $soluzione) {
            try {
                $stmt = $db->prepare($soluzione['soluzione_professore']);
                $stmt->execute();
                $sol = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                $stmt = $db->prepare($risposta_studente);
                $stmt->execute();
                $sce = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if ($sol == $sce) {
                    return $soluzione['soluzione_professore'];
                }
            } catch (\Throwable $th) {
                echo "Errore nella risposta aperta <br>"  . $th->getMessage();
            }
        }
    } else {
        return "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/risultatiTest.css">

    <title>Risultati</title>

</head>

<body>
    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
            <a class="home" href='/pages/studente/studente.php'></a>
        </div>
        <h1>Test conclusi</h1>
    </div>

    <div class="container-risultati">
        <?php
        costruisciTabellaRisultati();
        ?>
    </div>

</body>


</html>