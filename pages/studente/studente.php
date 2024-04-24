<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (isset($_SESSION['email']) == false || $_SESSION['ruolo'] != "STUDENTE") {
    header('Location: ../index.php');
}

// mostra i test all'utente 
$db = connectToDatabaseMYSQL();
$sql = "CALL GetAllTests();";
$stmt = $db->prepare($sql);
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// controlla se l'utente ha concluso dei test
$sql = "CALL CheckRisultatiStudente(:email);";
$stmt = $db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
$stmt->execute();
$test_concluso_bool = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

echo "<script>console.log(" . $test_concluso_bool['check'] . ")</script>";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test per lo studente</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/studente.css">


    <style>
        .test-list {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            align-content: center;
        }

        .container-studente {
            display: grid;
            grid-template-columns: 1fr 1fr;
            justify-items: center;
            align-items: center;
            justify-content: center;
            align-content: center;
        }

        .links {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            align-content: center;
            flex-wrap: wrap;
        }

        .widget-professore {
            width: 12dvw;
            height: 31dvh;
            min-width: 200px;
        }
    </style>

</head>

<body>
    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
        </div>
        <h1>Buongiorno <?php echo   $_SESSION['nome'] . " " . $_SESSION['cognome'] ?></h1>
    </div>

    <div class="links">
        <div class="widget-professore">
            <h3>Vai ai messaggi</h3>
            <button onclick="location.href='/pages/messaggi.php'">Messaggi</button>
        </div>

        <div class="widget-professore">
            <h3>Vai alle classifiche</h3>
            <button onclick="location.href='/pages/classifiche.php'">Classifiche</button>
        </div>
        <div class="widget-professore">
            <h3>Visualizza i tuoi test</h3>
            <button onclick="location.href='/pages/studente/risultati_test.php' <?php echo $test_concluso_bool['check'] > 0  ?  '' :  'disable' ?>">Risultati</button>
        </div>
    </div>

    <div class="test-list">
        <?php
        require_once "../../helper/check_closed.php";
        // stampa tutti i test
        foreach ($tests as $test) {
            $is_closed = check_svolgimento($test['titolo'], $_SESSION['email']);
            echo "<div class='widget-professore'>";
            echo "<h3>" . strtoupper($test['titolo']) . "</h3>";
            if ($is_closed == 1) {
                echo "<p style='color: green;'>Il test è concluso</p>";
                echo "<button onclick='location.href=\"/pages/studente/esegui_test.php?test_associato=" . $test['titolo'] . "\"'>Risultati</button>";
            } else {
                echo "<button onclick='location.href=\"/pages/studente/esegui_test.php?test_associato=" . $test['titolo'] . "\"'>Svolgi</button>";
            }
            echo "</div>";
        }
        ?>
    </div>
</body>

</html>