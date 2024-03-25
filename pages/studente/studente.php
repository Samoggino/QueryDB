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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Test per lo studente</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
</head>

<body>
    <h1>Visualizza tutti i test che può sostenere lo studente</h1>
    <?php
    echo "Benvenuto " . $_SESSION['nome'] . " " . $_SESSION['cognome'];
    echo "<br>";
    echo "Email: " . $_SESSION['email'];

    // stampa tutti i test
    foreach ($tests as $tests) {
        echo "<h2>" . strtoupper($tests['titolo']) . "</h2>";
        echo "<a href='/pages/studente/esegui_test.php?test_associato=" . $tests['titolo'] . "'>Svolgi il test</a>";
    }
    ?>




</body>

</html>