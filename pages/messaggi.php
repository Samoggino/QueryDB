<?php
session_start();
require_once '../helper/connessione_mysql.php';
require_once '../helper/check_messaggi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $mittente           = $_SESSION['email'];
    $titolo_messaggio   = $_POST['titolo_messaggio'];
    $testo_messaggio    = $_POST['testo_messaggio'];

    $db = connectToDatabaseMYSQL();

    if ($_SESSION['ruolo'] == 'STUDENTE') {

        $professore_destinatario = $_POST['professore_destinatario'];
        $test_associato = $_POST['test_associato'];

        $sql = "CALL InviaMessaggioDaStudente(:titolo, :testo, :test_associato, :mittente, :destinatario);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':mittente', $mittente);
        $stmt->bindParam(':titolo', $titolo_messaggio);
        $stmt->bindParam(':testo', $testo_messaggio);
        $stmt->bindParam(':test_associato', $test_associato);
        $stmt->bindParam(':destinatario', $professore_destinatario);
        $stmt->execute();
        $stmt->closeCursor();
    } elseif ($_SESSION['ruolo'] == 'PROFESSORE') {
        $sql = "CALL InviaMessaggioDaDocente(:mittente, :titolo, :testo, :test_associato);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':mittente', $mittente);
        $stmt->bindParam(':titolo', $titolo_messaggio);
        $stmt->bindParam(':testo', $testo_messaggio);
        $stmt->bindParam(':test_associato', $_POST['test_associato']);
        $stmt->execute();
        $stmt->closeCursor();
    }
    $db = null;
}

function tendinaProfessori()
{
    $db = connectToDatabaseMYSQL();
    $sql = "SELECT email_professore FROM PROFESSORE;";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $professori = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    echo "<select name='professore_destinatario'>";
    foreach ($professori as  $professore) {
        echo "<option value='" . $professore['email_professore'] . "'>" . $professore['email_professore'] . "</option>";
    }
    echo "</select>";
}

function tendinaTest()
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetAllTests();";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    echo "<select name='test_associato' id='scegli_test'>";
    foreach ($tests as  $test) {
        echo "<option value='" . $test['titolo'] . "'>" . $test['titolo'] . "</option>";
    }
    echo "</select>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Messaggi</title>
    <link rel="icon" href="../images/favicon/favicon.ico" type="image/x-icon">

    <style>
        table {
            max-width: 80%;
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

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <?php if ($_SESSION['ruolo'] == 'STUDENTE') {  ?>

        <h1>Invia un messaggio al prof!</h1>
        <form id='uploadForm' style="display: flex; flex-direction:column; max-width:fit-content; gap:10px" method='post' action=''> <input for='titolo_messaggio' name='titolo_messaggio' placeholder='Titolo' type='text' required>
            <input for='testo_messaggio' name='testo_messaggio' placeholder='Testo' type='text' required>
            <?php
            tendinaProfessori();
            tendinaTest()
            ?>
            <input type='submit' value='Invia'>
        </form>
    <?php visualizzaMessaggi();
    } elseif ($_SESSION['ruolo'] == 'PROFESSORE') { ?>

        <h1>Invia un messaggio agli studenti!</h1>

        <form id='uploadForm' style="display: flex; flex-direction:column; max-width:fit-content; gap:10px" method='post' action=''>
            <input for='titolo_messaggio' name='titolo_messaggio' placeholder='Titolo' type='text' required>
            <input for='testo_messaggio' name='testo_messaggio' placeholder='Testo' type='text' required>
            <?php tendinaTest() ?>
            <input type='submit' value='Invia'>
        </form>
    <?php visualizzaMessaggi();
    } ?>




</body>

</html>