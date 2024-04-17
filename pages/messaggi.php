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
    <link rel="stylesheet" href="../styles/global.css">
    <a href="../pages/professore/professore.php">Pagina del professore</a>

    <style>
        form {
            display: flex;
            flex-direction: column;
            max-width: fit-content;
            margin: 0 auto;
        }

        form .testo {
            min-width: 250px;
            height: 100px;
            display: grid;
            place-items: center;
        }

        form .titolo {
            font-size: larger;
            font-weight: bold;
        }

        td {
            font-size: 14px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-top: 30px;
        }

        div.invia-messaggio {
            max-width: min-content;
        }
    </style>
</head>

<body>

    <?php if ($_SESSION['ruolo'] == 'STUDENTE') {  ?>
        <div class="invia-messaggio">
            <h1>Invia un messaggio al prof!</h1>
            <form id='uploadForm' method='post' action=''> <input for='titolo_messaggio' name='titolo_messaggio' placeholder='Titolo' type='text' required>
                <input for='testo_messaggio' name='testo_messaggio' placeholder='Testo' type='text' required>
                <?php
                tendinaProfessori();
                tendinaTest()
                ?>
            </form>
        </div>
    <?php visualizzaMessaggi();
    } elseif ($_SESSION['ruolo'] == 'PROFESSORE') { ?>

        <div class="invia-messaggio">
            <h1>Invia un messaggio agli studenti!</h1>

            <form id='uploadForm' method='post' action=''>
                <input for='titolo_messaggio' class="titolo" name='titolo_messaggio' placeholder='Titolo' type='text' required>
                <input for='testo_messaggio' class="testo" name='testo_messaggio' placeholder='Testo' type='text' required>
                <?php tendinaTest() ?>
                <input type='submit' value='Invia'>
            </form>
        </div>
    <?php visualizzaMessaggi();
    } ?>




</body>

</html>