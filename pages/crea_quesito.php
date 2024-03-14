<?php
session_start();
require_once "../helper/connessione_mysql.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


try {

    $titolo_test = $_GET['titolo_test'];
    echo "Titolo test: " . $titolo_test;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $db = connectToDatabaseMYSQL();
        $descrizione_test = $_POST['descrizione_test'];
        $difficolta = $_POST['difficolta'];
        $tipo_quesito = $_POST['tipo_quesito'];

        if (isset($_POST['aperto'])) {
            $anno_immatricolazione = $_POST['anno_immatricolazione'];
            $codice_alfanumerico = $_POST['codice_alfanumerico'];
        } else {
            $anno_immatricolazione = null;
            $codice_alfanumerico = null;
        }

        if (isset($_POST['chiuso'])) {
            $dipartimento = $_POST['dipartimento'];
            $corso = $_POST['corso'];
        } else {
            $dipartimento = null;
            $corso = null;
        }

        $sql = "CALL InserisciNuovoQuesito(:test_associato, :descrizione_test)";
    }
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
                <input for="descrizione" name="descrizione" placeholder="Descrizione" type="text">
                <input for="codice_alfanumerico" name="codice_alfanumerico" placeholder="Codice" type="text">
            </div>
        </div>
        <div>
            <label for="quesito-chiuso-checkbox">Chiuso</label>
            <input type="checkbox" id="quesito-chiuso-checkbox" name="chiuso">
            <div id="chiuso" style="display: none;">
                <div>
                    <button type="button" id="aggiungi_quesito_chiuso">Aggiungi quesito</button><br>
                    <button type="button" id="rimuovi_quesito_chiuso">Rimuovi quesito</button><br>
                </div>
                <input type="text" name="opzione" placeholder="opzione">
                <input type="text" name="opzione" placeholder="opzione">
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

    document.addEventListener("DOMContentLoaded", function() {

        var quesito_chiuso_button = document.getElementById("aggiungi_quesito_chiuso");
        quesito_chiuso_button.addEventListener('click', function() {
            var quesito_chiuso = document.createElement('div');
            quesito_chiuso.className = 'quesito-chiuso';
            quesito_chiuso.innerHTML = `
                <input type="text" name="opzione" placeholder="opzione">`;
            document.getElementById('chiuso').appendChild(quesito_chiuso);
        });


        var rimuovi_quesito_chiuso_button = document.getElementById("rimuovi_quesito_chiuso");
        rimuovi_quesito_chiuso_button.addEventListener('click', function() {
            var quesito_chiuso = document.getElementsByClassName('quesito-chiuso');
            if (quesito_chiuso.length > 0) {
                quesito_chiuso[quesito_chiuso.length - 1].remove();
            }
        });



    });
</script>

</html>