<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../helper/connessione_mysql.php";


try {
    // Verifica se il modulo di caricamento è stato inviato
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connessione al database
        $db = connectToDatabaseMYSQL();

        $titolo_test = $_POST['titolo_test'];

        echo "Titolo test: " . $titolo_test;
        $visualizza_risposte = isset($_POST['visualizzaRisposteCheckbox']) ? 1 : 0;

        $sql = "CALL InserisciNuovoTest(:titolo_test, :visualizza_risposte)";
        $statement = $db->prepare($sql);
        $statement->bindParam(':titolo_test', $titolo_test);
        $statement->bindParam(':visualizza_risposte', $visualizza_risposte);

        if ($statement->execute()) {
            echo "Test inserito con successo.";
        } else {
            echo "La query non è stata eseguita correttamente.";
        }

        // Verifica se è stata selezionata un'immagine
        if (isset($_FILES["file_immagine"]) && $_FILES["file_immagine"]["error"] == UPLOAD_ERR_OK) {
            // Leggi il file dell'immagine
            $dati_immagine = file_get_contents($_FILES["file_immagine"]["tmp_name"]);

            // Prepara la query per l'inserimento dell'immagine
            $sql = "CALL InserisciNuovaFotoTest(:dati_immagine, :titolo_test)";
            $statement = $db->prepare($sql);

            // Associa i dati dell'immagine e il titolo del test alla query
            $statement->bindParam(':dati_immagine', $dati_immagine, PDO::PARAM_LOB);
            $statement->bindParam(':titolo_test', $titolo_test);

            // Esegui la query
            if ($statement->execute()) {
                echo "Immagine caricata con successo.";
            } else {
                echo "L'immagine non è stata inserita nel db perchè c'è stato un errore.";
            }

            // Recupera l'immagine dal database
            $sql = "CALL RecuperaFotoTest(:titolo_test)";
            $statement = $db->prepare($sql);
            $statement->bindParam(':titolo_test', $titolo_test);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            // Se è stata trovata un'immagine, visualizzala
            if ($row) {
                $immagine = $row['foto'];
                echo "<h2>Immagine Caricata</h2>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($immagine) . "' alt='Immagine Caricata'>";
            } else {
                echo "Nessuna immagine trovata per questo T.";
            }
        }
        $new_url = "Location: crea_quesito.php?titolo_test=" . $titolo_test;
        header($new_url);
    }
} catch (\Throwable $th) {
    //throw $th;
    echo "" . $th->getMessage() . "";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
</head>

<body>
    <h1>Crea un test</h1>
    <form id="uploadForm" method="post" action="" enctype="multipart/form-data">
        <input for="titolo_test" name="titolo_test" placeholder="Titolo" type="text" required>

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