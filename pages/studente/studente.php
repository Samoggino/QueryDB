<?php
session_start();
require '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['email'])) {
    header('Location: /');
    exit;
}

// mostra i test all'utente 
$db = connectToDatabaseMYSQL();
$sql = "CALL GetAllTests();";
$stmt = $db->prepare($sql);
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// controlla se l'utente ha svolto dei test
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
    
</head>

<body>
    <div class="container">
        <h1>Visualizza tutti i test che può sostenere lo studente</h1>
        <div class="welcome">
            <?php
            echo "Benvenuto " . $_SESSION['nome'] . " " . $_SESSION['cognome'] . "<br>";
            echo "Email: " . $_SESSION['email'];
            ?>
        </div>

        <div class="test-list">
            <?php
            require_once "../../helper/check_closed.php";
            // stampa tutti i test
            foreach ($tests as $test) {
                $is_closed = check_svolgimento($test['titolo'], $_SESSION['email']);
                echo "<div class='test-item'>";
                echo "<h3>" . strtoupper($test['titolo']) . "</h3>";
                if ($is_closed == 1) {
                    echo "<p style='color: green;'>Hai già svolto questo test</p>";
                    // manca il link per visualizzare i risultati
                    echo "<a href='/pages/studente/risultati_test.php?test_associato=" . $test['titolo'] . "'>Visualizza i risultati</a>";
                } else {
                    echo "<a href='/pages/studente/esegui_test.php?test_associato=" . $test['titolo'] . "'>Svolgi il test</a>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <div class="links">
            <h1>Vai ai messaggi</h1>
            <a href="/pages/messaggi.php">Messaggi</a>

            <h1>Vai alle classifiche</h1>
            <a href="../classifiche.php">Classifiche</a>
        </div>
    </div>
</body>

</html>