<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['test_associato'])) {
    $test_associato = $_POST['test_associato'];
    header("Location: crea_quesito.php?test_associato=" . $test_associato);
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">

</head>

<body>
    <h1>Schermata del professore</h1>
    <?php
    echo "<h2>La tua email è : " . $_SESSION['email'] . "</h2>";
    echo "Schermata del professore" . "<br>";
    echo "professore.php" . "<br>";
    ?>
    <h2>Crea un test</h2>
    <form id="uploadForm" method="post" action="crea_test.php" enctype="multipart/form-data">
        <input for="titolo_test_creato" name="titolo_test_creato" placeholder="Titolo" type="text" required>
        <input type="file" name="file_immagine" accept="image/*"><br><br>
        <label for="file_immagine">Seleziona un'immagine:</label><br>
        <input type="submit" value="Crea">
    </form>

    <h2>Aggiungi quesito</h2>
    <h3>Scegli un test</h3>
    <form id="aggiungi-quesito-form" method="post" action="">
        <select name="test_associato" for="test_associato">
            <?php
            require_once "./tendina_test.php";
            tendinaTest();
            ?>
        </select>
        <input type="submit" value="Aggiungi quesito">
    </form>


    <?php
    require_once "./tendina_test.php";
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetTestDelProfessore(:email_professore);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email_professore', $_SESSION['email']);
    try {
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tests as $test) {
            echo "<a href='modifica_test.php?test_associato=" . $test['titolo'] . "'>" . $test['titolo'] . "</a>" . "<br>";
        }
    } catch (\Throwable $th) {
        echo "<script>console.log('Errore: " . $th . "');</script>";
    }
    $stmt->closeCursor();
    ?>


    <h1>Vai ai messaggi</h1>
    <a href="/pages/messaggi.php">Messaggi</a>

    <h1>Vai a creazione tabella </h1>
    <a href="/pages/professore/crea_tabella_esercizio.php">Crea tabella</a>
</body>


<script>
    // Pulisci il form quando la pagina viene caricata o ricaricata
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('uploadForm').reset();
    });

    window.addEventListener('load', function() {
        document.getElementById('uploadForm').reset();
    });
</script>

</html>