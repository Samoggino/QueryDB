<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<script>console.log('Debugging...');</script>";

if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

if (isset($_GET['test_associato'])) {
    $test_associato = $_GET['test_associato'];

    // $test_associato = "Test di Matematica";

    $db = connectToDatabaseMYSQL();
    $sql = "SELECT * FROM TEST WHERE titolo = :titolo;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $test_associato, PDO::PARAM_STR);
    $stmt->execute();
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($test == null) {
        echo "<h1>Il test non esiste</h1>";
        throw new Exception("Il test non esiste");
    }

    $sql = "CALL GetQuesitiTest(:test_associato);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
    $stmt->execute();
    $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $sql = "CALL RecuperaFotoTest(:titolo);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $test_associato, PDO::PARAM_STR);
    $stmt->execute();
    $foto_test = ($stmt->fetch(PDO::FETCH_ASSOC));
    $stmt->closeCursor();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = connectToDatabaseMYSQL();
    $sql = "CALL MostraRisultati(:titolo);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $test_associato, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();
    header("Location: modifica_test.php?test_associato=$test_associato");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Modifica test</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <style>
        img {
            max-width: 250px;
            max-height: 250px;
        }
    </style>
</head>

<body>
    <?php
    $visualizza_risposte_checkbox = $test['VisualizzaRisposte'] == 1 ? 1 : 0;

    try {
        if ($quesiti == null || count($quesiti) == 0) {
            throw new Exception("Il test non ha quesiti");
        }
        // stampa il test come fosse una tabella
        if ($visualizza_risposte_checkbox == 0) {
            echo "<form method='post' action=''>";
        }

        echo "<table>";
        echo "<tr>";
        echo "<th>Titolo</th>";
        echo "<th>Visualizza risposte</th>";
        if (count($quesiti) > 0) {
            echo "<th>Quesiti</th>";
        }
        if ($foto_test != null) {
            echo "<th>Immagine</th>";
        }
        echo "</tr>";
        echo "<tr>";
        echo "<td>" . $test['titolo'] . "</td>";
        if ($visualizza_risposte_checkbox == 1) {
            echo "<td>Visibili</td>";
        } else {
            echo "<td><input type='checkbox' name='visualizza_risposte' value='1' " . ($test['VisualizzaRisposte'] ? 'checked' : '') . "></td>";
        }
        echo "<td>" . count($quesiti) . "</td>";
        if ($foto_test != null) {
            echo "<td><img src='data:image/jpeg/webp;base64," . base64_encode($foto_test['foto']) . "'></td>";
        }
        echo "</tr>";
        echo "</table>";
        if ($visualizza_risposte_checkbox == 0) {
            echo "<input type='submit' value='Salva'>";
            echo "</form>";
        }
    } catch (\Throwable $th) {
        echo "<h1>Il test non ha quesiti</h1>";
        echo "Vuoi aggiungere un quesito?";
        echo "<a href='crea_quesito.php?test_associato=$test_associato'>Aggiungi quesito</a>";
    }
    ?>
</body>

</html>