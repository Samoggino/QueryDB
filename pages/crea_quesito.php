<?php
session_start();
require_once "../helper/connessione_mysql.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


try {

    $db = connectToDatabaseMYSQL();
    $titolo_test = $_GET['titolo_test'];
    echo "Titolo test: " . $titolo_test;


    // se esiste un test con questo nome, eliminalo
    $sql = "SELECT * FROM TEST WHERE titolo = :titolo_test";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
    $stmt->execute();
    $test = $stmt->fetch(PDO::FETCH_ASSOC);


    // FIXME: elimina il debug
    if ($test) {
        // echo "Il test esiste già, lo elimino";
        $sql = "DELETE FROM TEST WHERE titolo = :titolo_test";
    }

    // Eseguire la query per ottenere il numero del quesito più alto
    $sql = "SELECT numero_quesito FROM QUESITO WHERE test_associato = :titolo_test ORDER BY numero_quesito DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
    $stmt->execute();
    $ultimo_quesito = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se c'è un quesito presente nel database, incrementa il numero del quesito, altrimenti impostalo a 1
    if ($ultimo_quesito) {
        $numero_quesito = $ultimo_quesito['numero_quesito'] + 1;
    } else {
        $numero_quesito = 1;
    }


    echo "<script>console.log('numero_quesito: " . $numero_quesito . "');</script>";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $descrizione = $_POST['descrizione'];
        $livello_difficolta = $_POST['difficolta'];
        $tipo_quesito = $_POST['tipo_quesito'];

        echo    "<script>console.log('tipo_quesito: " . $tipo_quesito . "');</script>";

        $opzioni = $_POST['opzione'];
        $opzioni_vera = $_POST['opzione_vera'];

        echo "<script>console.log('titolo_test: " . $titolo_test . "');</script>";
        echo "<script>console.log('descrizione: " . $descrizione . "');</script>";
        echo "<script>console.log('difficolta: " . $livello_difficolta . "');</script>";
        echo "<script>console.log('tipo_quesito: " . $tipo_quesito . "');</script>";



        for ($i = 0; $i < count($opzioni); $i++) {
            if ($opzioni_vera[$i] == "on") {
                $opzioni_vera[$i] = "TRUE";
            }
            echo "<script>console.log('opzione: " . $opzioni[$i] . "');</script>";
            echo "<script>console.log('opzione_vera: " . $opzioni_vera[$i] . "');</script>";
        }

        try {
            $sql = "CALL InserisciNuovoQuesito(:numero_quesito, :test_associato, :descrizione, :livello_difficolta, :tipo_quesito)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':numero_quesito', $numero_quesito, PDO::PARAM_INT);
            $stmt->bindParam(':test_associato', $titolo_test, PDO::PARAM_STR);
            $stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $stmt->bindParam(':livello_difficolta', $livello_difficolta, PDO::PARAM_STR);
            $stmt->bindParam(':tipo_quesito', $tipo_quesito, PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Throwable $th) {
            echo  "Errore: " . $th->getMessage();
            echo  "<br> SQL: " . $sql;
        }

        if ($tipo_quesito == "APERTO") {
            // $sql = "CALL InserisciNuovoQuesitoAperto(:test_associato, :descrizione, :tipo_quesito, :difficolta, :descrizione";
        } elseif ($tipo_quesito == "CHIUSO") {
            // $sql = "CALL InserisciNuovoQuesitoChiuso(:test_associato, :descrizione, :tipo_quesito, :difficolta, :descrizione";
        }

        // $stmt = $db->prepare($sql);
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
        <label for="descrizione" name="descrizione">Descrizione:</label>
        <input for="descrizione" name="descrizione" placeholder="Descrizione" type="text" required>
        <select for="difficolta" name="difficolta" id="difficolta" required>
            <option value="BASSO">Basso</option>
            <option value="MEDIO">Medio</option>
            <option value="ALTO">Alto</option>
        </select>
        <div>
            <label for="quesito-aperto-checkbox">Aperto</label>
            <input type="checkbox" id="quesito-aperto-checkbox" name="APERTO">
            <div id="APERTO" style="display: none;">
                <input for="soluzione" name="soluzione[]" placeholder="Soluzione" type="text">
            </div>
        </div>
        <div>
            <label for="quesito-chiuso-checkbox">Chiuso</label>
            <input type="checkbox" id="quesito-chiuso-checkbox" name="CHIUSO">
            <div id="CHIUSO" style="display: none;">
                <div>
                    <button type="button" id="aggiungi_quesito_chiuso">Aggiungi quesito</button><br>
                    <button type="button" id="rimuovi_quesito_chiuso">Rimuovi quesito</button><br>
                </div>
                <div id="opzioni_chiuso">
                    <div class="quesito-chiuso">
                        <input name="opzione[]" placeholder="Opzione" type="text">
                        <input name="opzione_vera[]" type="checkbox"> Opzione Vera
                    </div>
                </div>
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
        var aperto_div = document.getElementById("APERTO");
        var chiuso_div = document.getElementById("CHIUSO");
        var tipoUtenteInput = document.getElementById("tipo_quesito"); // Definizione della variabile tipoUtenteInput

        quesito_aperto_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "APERTO";
                aperto_div.style.display = "block";
                quesito_chiuso_checkbox.checked = false; // Disabilita la checkbox chiuso quando selezioni aperto
                chiuso_div.style.display = "none";
            } else {
                aperto_div.style.display = "none";
            }
        });

        quesito_chiuso_checkbox.addEventListener("change", function() {
            if (this.checked) {
                tipoUtenteInput.value = "CHIUSO";
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
                <input type="text" name="opzione[]" placeholder="Opzione">
                <input type="checkbox" name="opzione_vera[]"> Opzione Vera`;
            document.getElementById('opzioni_chiuso').appendChild(quesito_chiuso);
        });


        var rimuovi_quesito_chiuso_button = document.getElementById("rimuovi_quesito_chiuso");
        rimuovi_quesito_chiuso_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('opzioni_chiuso');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });
    });

    var form = document.getElementById("form-quesito");

    form.addEventListener("submit", function(event) {
        var opzioni_vera = document.querySelectorAll('input[name="opzione_vera[]"]');
        opzioni_vera.forEach(function(opzione_vera) {
            if (!opzione_vera.checked) {
                var falsaInput = document.createElement('input');
                falsaInput.type = 'hidden';
                falsaInput.name = opzione_vera.name;
                falsaInput.value = 'FALSE';
                opzione_vera.parentNode.appendChild(falsaInput);
            }
        });
    });
</script>

</html>