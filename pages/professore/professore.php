<?php
session_start();

if (isset($_POST['test_associato'])) {
    $test_associato = $_POST['test_associato'];
    header("Location: crea_quesito.php?test_associato=" . $test_associato);
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
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
        <input for="titolo" name="titolo" placeholder="Titolo" type="text" required>

        <label for="">Visualizza risposte</label>
        <input type="checkbox" id="visualizzaRisposteCheckbox" name="visualizzaRisposteCheckbox">
        <input type="hidden" id="visualizzaRisposteHidden" name="visualizzaRisposteHidden" value="0">

        <input type="file" name="file_immagine" accept="image/*"><br><br>
        <label for="file_immagine">Seleziona un'immagine:</label><br>
        <input type="submit" value="Crea">
    </form>

    <h2>Aggiungi quesito</h2>
    <h3>Scegli un test</h3>
    <form method="post" action="">
        <select name="test_associato" for="test_associato">
            <?php
            require "../../helper/connessione_mysql.php";
            $db = connectToDatabaseMYSQL();
            $sql = "CALL GetTestDelProfessore(:email_professore);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email_professore', $_SESSION['email']);
            try {
                $stmt->execute();
                $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($tests as $test) {
                    echo "<option value='" . $test['titolo'] . "'>" . $test['titolo'] . "</option>";
                }
            } catch (\Throwable $th) {
                echo "<script>console.log('Errore: " . $th . "');</script>";
            }
            $stmt->closeCursor();
            ?>
        </select>
        <input type="submit" value="Aggiungi quesito">
    </form>
</body>


<script>
    // Pulisci il form quando la pagina viene caricata o ricaricata
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('uploadForm').reset();
    });

    window.addEventListener('load', function() {
        document.getElementById('uploadForm').reset();
    });
    // Ottieni il riferimento al form
    const visualizzaRisposteCheckbox = document.getElementById('visualizzaRisposteCheckbox');

    // Ascolta gli eventi di cambio
    visualizzaRisposteCheckbox.addEventListener('change', function() {
        // Se il checkbox è selezionato, imposta il valore dell'input nascosto a 1, altrimenti a 0
        const value = this.checked ? 1 : 0;
        document.getElementById('visualizzaRisposteHidden').value = value;
    });
</script>

</html>