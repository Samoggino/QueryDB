<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "../../helper/connessione_mysql.php";



try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connessione al database
        $db = connectToDatabaseMYSQL();

        $test_associato = $_POST['test_associato'];

        $visualizza_risposte = isset($_POST['visualizzaRisposteCheckbox']) ? 1 : 0;

        $sql = "CALL InserisciNuovoTest(:test_associato, :visualizza_risposte, :email_professore)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':test_associato', $test_associato);
        $stmt->bindParam(':visualizza_risposte', $visualizza_risposte);
        $stmt->bindParam(':email_professore', $_SESSION['email']);


        if ($_SESSION['ruolo'] == 'PROFESSORE') {
            echo "Sei un professore!";
            if ($stmt->execute()) {
                echo "<script> alert('Test inserito con successo!');</script>";
                $stmt->closeCursor();

                // Salva il titolo del test nella sessione
                $_SESSION['test_associato'] = $test_associato;
            } else {
                echo "<script> alert('Errore durante l'inserimento del test!');</script>";
            }
        } else {
            echo "<script> alert('Non sei un professore!')</script>";
        }



        // Verifica se è stata selezionata un'immagine
        if (isset($_FILES["file_immagine"]) && $_FILES["file_immagine"]["error"] == UPLOAD_ERR_OK) {
            // Leggi il file dell'immagine
            $dati_immagine = file_get_contents($_FILES["file_immagine"]["tmp_name"]);

            // Prepara la query per l'inserimento dell'immagine
            $sql = "CALL InserisciNuovaFotoTest(:dati_immagine, :test_associato)";
            $stmt = $db->prepare($sql);

            // Associa i dati dell'immagine e il titolo del test alla query
            $stmt->bindParam(':dati_immagine', $dati_immagine, PDO::PARAM_LOB);
            $stmt->bindParam(':test_associato', $test_associato);

            // Esegui la query
            if ($stmt->execute()) {
                echo "Immagine caricata con successo.";
            } else {
                echo "L'immagine non è stata inserita nel db perchè c'è stato un errore.";
            }

            // Recupera l'immagine dal database
            $sql = "CALL RecuperaFotoTest(:test_associato)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':test_associato', $test_associato);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Se è stata trovata un'immagine, visualizzala
            if ($row) {
                $immagine = $row['foto'];
                echo "<h2>Immagine Caricata</h2>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($immagine) . "' alt='Immagine Caricata'>";
            } else {
                echo "Nessuna immagine trovata per questo T.";
            }
        }

        // echo "ciao";
        // $new_url = "Location: crea_quesito.php?test_associato=" . $test_associato;
        // header($new_url);
        header("Location: ../professore/crea_quesito.php?test_associato=" . $test_associato);
    }
} catch (\Throwable $th) {
    //throw $th;
    echo "" . $th->getMessage() . "";
}
