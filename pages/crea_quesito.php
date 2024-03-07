<?php
require_once "../helper/connessione_mysql.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


try {
    // session_start();
    // echo "Titolo test: " . $_SESSION['titolo_test'] . "in crea quesito!";

    // stampa la lunghezza dell'array _Session

    // echo "Lunghezza array session: " . count($_SESSION);
    $titolo_test = $_GET['titolo_test'];
    echo "Titolo test: " . $titolo_test;

    // if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //     // echo 
    // }
} catch (\Throwable $th) {
    echo  "Errore: " . $th->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Crea test</title>
</head>

<body>
    <!-- crea dei quesiti per il test, il quesito è fatto con un enum per la difficoltà e un campo per la descrizione -->
    <form method="POST" action="" id="form-quesito">
        <label for="descrizione_test" name="descrizione_test">Descrizione:</label>
        <input for="descrizione_test" name="descrizione_test" placeholder="Descrizione" type="text" required>
        <select for="difficolta" name="difficolta" id="difficolta" required>
            <option value="Alto">Alto</option>
            <option value="Medio">Medio</option>
            <option value="Basso">Basso</option>
        </select>

        <div>
            <label for="quesito-aperto-checkbox">Aperto</label>
            <input type="checkbox" id="quesito-aperto-checkbox" name="aperto">
            <div id="aperto" style="display: none;">
                <input for="anno_immatricolazione" name="anno_immatricolazione" placeholder="Anno di immatricolazione" type="text">
                <input for="codice_alfanumerico" name="codice_alfanumerico" placeholder="Codice" type="text">
            </div>
        </div>
        <div>
            <label for="quesito-chiuso-checkbox">Chiuso</label>
            <input type="checkbox" id="quesito-chiuso-checkbox" name="chiuso">
            <div id="chiuso" style="display: none;">
                <input for="dipartimento" name="dipartimento" placeholder="Dipartimento" type="text">
                <input for="corso" name="corso" placeholder="Corso" type="text">
            </div>
        </div>
        <input type="hidden" for="tipo_quesito" name="tipo_quesito" id="tipo_quesito" value="">
        <button type="submit" value="crea il test">Crea il test</button>
    </form>


    <h1> </h1>

</body>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var quesito_aperto_checkbox = document.getElementById("quesito-aperto-checkbox");
        var quesito_chiuso_checkbox = document.getElementById("quesito-chiuso-checkbox");
        var aperto_div = document.getElementById("aperto");
        var chiuso_div = document.getElementById("chiuso");
        var tipoUtenteInput = document.getElementById("tipo_quesito"); // Definizione della variabile tipoUtenteInput

        quesito_aperto_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "aperto";
                aperto_div.style.display = "block";
                quesito_chiuso_checkbox.checked = false; // Disabilita la checkbox chiuso quando selezioni aperto
                chiuso_div.style.display = "none"; // Nasconde il campo dipartimento quando selezioni aperto
            } else {
                aperto_div.style.display = "none";
            }
        });

        quesito_chiuso_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "chiuso";
                chiuso_div.style.display = "block";
                quesito_aperto_checkbox.checked = false; // Disabilita la checkbox aperto quando selezioni chiuso
                aperto_div.style.display = "none"; // Nasconde il campo anno immatricolazione quando selezioni chiuso
            } else {
                chiuso_div.style.display = "none";
            }
        });

        var form = document.getElementById("form-quesito");

        form.addEventListener("submit", function(event) {
            if (!quesito_aperto_checkbox.checked && !quesito_chiuso_checkbox.checked) {
                event.preventDefault(); // Impedisce l'invio del modulo se nessuna checkbox è selezionata
                alert("Seleziona almeno una delle opzioni: aperto o chiuso.");
            }
        });
    });
</script>

</html>