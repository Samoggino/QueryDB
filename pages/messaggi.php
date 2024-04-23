<?php
session_start();
require_once '../helper/connessione_mysql.php';
require_once '../helper/check_messaggi.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['email']) || !isset($_SESSION['ruolo'])) {
    header('Location: /pages/login.php');
}

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
    unset($_POST);
}

function tendinaProfessori()
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetProfessori();";
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

    <style>
        .tendina {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            /* max-width: fit-content; */
            align-content: flex-start;
            justify-content: center;
            align-items: center;
        }

        .inner-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            align-content: center;
        }


        #messaggi .widget-professore {
            width: 500px;
            height: min-content;
        }

        #messaggi input[type='text'] {
            width: 300px;
            height: 30px;
            margin: 10px;
            border-radius: 5px;
            border: 2px solid black;
        }

        textarea {
            width: 300px;
            height: 80px;
            margin: 10px;
            border-radius: 5px;
            border: 2px solid black;
            background-color: white;
        }

        button {
            width: min-content;
            height: min-content;
            padding: 10px 60px;
        }

        #intestazione {
            margin-bottom: 20px;
            gap: 35dvw;
            margin-left: 3dvw;
        }

        #messaggi #body-messaggi {
            display: flex;
            gap: 70px;
            align-content: center;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            flex-direction: row;
        }

        #messaggi .widget-classifica {
            width: 60%;
            height: <?php echo $_SESSION['ruolo'] == 'STUDENTE' ? '600px' : '530px'; ?>;
        }
    </style>
</head>

<body id="messaggi">

    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
            <a class="home" href='/pages/<?php echo strtolower($_SESSION['ruolo']) . "/" . strtolower($_SESSION['ruolo']) . "php" ?>'></a>
        </div>
        <h1>
            <?php
            if ($_SESSION['ruolo'] == 'STUDENTE') {
                echo "Invia un messaggio al professore";
            } elseif ($_SESSION['ruolo'] == 'PROFESSORE') {
                echo "Invia un messaggio agli studenti";
            }
            ?>
        </h1>
    </div>

    <div id="body-messaggi">
        <div class="widget-professore">
            <h2 style="margin-bottom: 0;">Invia un messaggio</h2>
            <form method='post' action=''>

                <div class="inner-form">
                    <div class="tendina">
                        <h4 for="titolo_messaggio">Titolo:</h4>
                        <input for='titolo_messaggio' name='titolo_messaggio' placeholder='Inserisci il titolo del messaggio' type='text' required>
                    </div>
                    <div class="tendina">
                        <h4 for="testo_messaggio">Messaggio:</h4>
                        <textarea for='testo_messaggio' name='testo_messaggio' placeholder='Scrivi il tuo messaggio' type='text' required></textarea>
                    </div>
                    <?php
                    if ($_SESSION['ruolo'] == 'STUDENTE') {
                        echo "<div class='tendina'>Destinatario <br>";
                        tendinaProfessori();
                        echo "</div>";
                    }
                    echo "<div class='tendina'>Test associato <br>";
                    tendinaTest();
                    echo "</div>";
                    ?>
                    <button type='submit'> Invia </button>
                </div>
            </form>
        </div>
        <?php visualizzaMessaggi(); ?>

    </div>




</body>

</html>