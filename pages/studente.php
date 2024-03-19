<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['email'])) {
    header('Location: /');
    exit;
}

// mostra i test all'utente 
$db = connectToDatabaseMYSQL();
$sql = "SELECT * FROM TEST";
$statement = $db->prepare($sql);
$statement->execute();
$tests = $statement->fetchAll(PDO::FETCH_ASSOC);

// foreach ($tests as $test) {

//     // metti in uppercase il titolo
//     echo "<h2>" . strtoupper($test['titolo']) . "</h2>";

//     // mostra il cognome del professore che ha creato il test
//     $sql = "SELECT cognome FROM UTENTE WHERE email = :email";
//     $statement = $db->prepare($sql);
//     $statement->bindParam(':email', $test['email_professore']);
//     $statement->execute();

//     $professore = $statement->fetch(PDO::FETCH_ASSOC);

//     echo "<p>Professore: " . $professore['cognome'] . "</p>";
// }







?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test per lo studente</title>
</head>

<body>
    <h1>Visualizza tutti i test che può sostenere lo studente</h1>
    <?php
    echo "Benvenuto " . $_SESSION['nome'] . " " . $_SESSION['cognome'];
    echo "<br>";
    echo "Email: " . $_SESSION['email'];

    // stampa tutti i test
    foreach ($tests as $test) {
        echo "<h2>" . strtoupper($test['titolo']) . "</h2>";
        echo "<a href='/pages/esegui_test.php?titolo_test=" . $test['titolo'] . "'>Svolgi il test</a>";
    }
    ?>




</body>

</html>