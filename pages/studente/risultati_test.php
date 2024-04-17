<?php
session_start();
require "./query.php";
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

    // Stampare il titolo del test e le risposte
    echo "<table>";
    echo "<tr><th id='test' colspan='5'>" . $test_associato . "</th></tr>"; // Utilizzo colspan per estendere il titolo su 4 colonne
    echo "<tr>";
    echo "<th>Numero quesito</th>";
    echo "<th>Data</th>";
    echo "<th>Risposta dello studente</th>";
    echo "<th>Risposta del professore</th>";
    echo "<th>Esito</th>";
    echo "</tr>";
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

            if ($opzioni[0]['numero_opzione'] == $scelta['opzione_scelta']) {
                echo "<td>" . $opzioni[0]['numero_opzione'] . "</td>";
            } else {
                echo "<td> - </td>";
            }
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

        if ($risposta['esito'] == "GIUSTA") {
            echo "<td id = 'esito-giusta'>" . $risposta['esito'] . "</td>";
        } else if ($risposta['esito'] == "SBAGLIATA") {
            echo "<td id = 'esito-sbagliata'>" . $risposta['esito'] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<br>";
}


function mostraSoluzione($esito, $risposta_studente, $id_quesito)
{


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
    <p>
        <?php
        $sql = "CALL GetAllRisposteDellUtente(:email_studente);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email_studente', $_SESSION['email']);
        $stmt->execute();
        $risposte = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($risposte == 0) {
            echo "Non hai svolto alcun test";
            // header("Location: ../../pages/studente/studente.php");
            // exit();
        } else {
        ?>
    </p>
<?php } ?>
</body>


</html>