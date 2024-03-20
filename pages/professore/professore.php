<?php
session_start();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
</head>

<body>
    <?php
    echo "<h1>La tua email è : " . $_SESSION['email'] . "</h1>";
    echo "Schermata del professore" . "<br>";
    echo "professore.php" . "<br>";
    ?>
    <h1>Crea un test</h1>
    <form id="uploadForm" method="post" action="crea_test.php" enctype="multipart/form-data">
        <input for="test_associato" name="test_associato" placeholder="Titolo" type="text" required>

        <label for="">Visualizza risposte</label>
        <input type="checkbox" id="visualizzaRisposteCheckbox" name="visualizzaRisposteCheckbox">
        <input type="hidden" id="visualizzaRisposteHidden" name="visualizzaRisposteHidden" value="0">

        <input type="file" name="file_immagine" accept="image/*"><br><br>
        <label for="file_immagine">Seleziona un'immagine:</label><br>
        <input type="submit" value="Crea">

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