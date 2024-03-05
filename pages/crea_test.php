<?php
// Funzione per la connessione MySQL utilizzando PDO
require_once "../helper/connessione_mysql.php";

try {
    // Messaggi di errore
    $msg = "";

    // Verifica se il modulo di caricamento è stato inviato
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connessione al database
        $conn = connectToDatabaseMYSQL();

        $titolo_test = $_POST['titolo_test'];
        $visualizza_risposte = isset($_POST['visualizzaRisposteCheckbox']) ? 1 : 0;

        $sql = "CALL InserisciNuovoTest(:titolo_test, :visualizza_risposte)";
        $statement = $conn->prepare($sql);
        $statement->bindParam(':titolo_test', $titolo_test);
        $statement->bindParam(':visualizza_risposte', $visualizza_risposte);

        if ($statement->execute()) {
            echo "Test inserito con successo.";
        } else {
            $msg = "Errore durante l'inserimento del test nel database.";
        }

        // Verifica se è stata selezionata un'immagine
        if (isset($_FILES["file_immagine"]) && $_FILES["file_immagine"]["error"] == UPLOAD_ERR_OK) {
            // Leggi il file dell'immagine
            $dati_immagine = file_get_contents($_FILES["file_immagine"]["tmp_name"]);

            // Prepara la query per l'inserimento dell'immagine
            $sql = "CALL InserisciNuovaFotoTest(:dati_immagine, :titolo_test)";
            $statement = $conn->prepare($sql);

            // Associa i dati dell'immagine e l'ID del test alla query
            $statement->bindParam(':dati_immagine', $dati_immagine, PDO::PARAM_LOB);
            $statement->bindParam(':titolo_test', $titolo_test);

            // Esegui la query
            if ($statement->execute()) {
                echo "Immagine caricata con successo.";
            } else {
                $msg = "Errore durante l'inserimento dell'immagine nel database.";
            }
        }


        // Recupera l'immagine dal database
        $sql = "CALL RecuperaFotoTest(:titolo_test)";
        $statement = $conn->prepare($sql);
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
} catch (\Throwable $th) {
    //throw $th;
    echo "" . $th->getMessage() . "";
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Caricamento Immagine</title>
</head>

<body>
    <h2>Carica un'immagine</h2>
    <form id="uploadForm" method="post" action="" enctype="multipart/form-data">
        <input for="titolo_test" name="titolo_test" placeholder="Titolo" type="text" required>

        <label for="">Visualizza risposte</label>
        <input type="checkbox" id="visualizzaRisposteCheckbox" name="visualizzaRisposteCheckbox">
        <input type="hidden" id="visualizzaRisposteHidden" name="visualizzaRisposteHidden" value="0">

        <input type="file" name="file_immagine" accept="image/*"><br><br>
        <label for="file_immagine">Seleziona un'immagine:</label><br>
        <input type="submit" value="Carica Immagine">
    </form>
    <?php echo $msg; ?>
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