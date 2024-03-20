<?php
session_start();
require_once "../../helper/connessione_mysql.php";
require_once "../../helper/numero_nuovo_quesito.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


try {

    $db = connectToDatabaseMYSQL();

    $test_associato = $_GET['test_associato'];
    $n_quesito = getNumeroNuovoQuesito($test_associato);
    echo "<h1>Titolo test: " . $test_associato . "</h1>";
    echo "<script>console.log('Numero quesito: " . $n_quesito . "');</script>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $descrizione = $_POST['descrizione'];
        $livello_difficolta = $_POST['difficolta'];
        $tipo_quesito = $_POST['tipo_quesito'];

        try {
            $sql = "CALL InserisciNuovoQuesito(:numero_quesito, :test_associato, :descrizione, :livello_difficolta, :tipo_quesito)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':numero_quesito', $n_quesito, PDO::PARAM_INT);
            $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
            $stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $stmt->bindParam(':livello_difficolta', $livello_difficolta, PDO::PARAM_STR);
            $stmt->bindParam(':tipo_quesito', $tipo_quesito, PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Throwable $th) {
            echo  "Errore nel creare il quesito: <br> ";
            echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
        }

        if ($tipo_quesito == "APERTO") {

            try {
                $soluzioni = $_POST['soluzione'];
                for ($i = 0; $i < count($soluzioni); $i++) {
                    $sql = "CALL InserisciNuovaSoluzioneQuesitoAperto(:numero_quesito, :test_associato, :soluzione)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':numero_quesito', $n_quesito, PDO::PARAM_INT);
                    $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
                    $stmt->bindParam(':soluzione', $soluzioni[$i], PDO::PARAM_STR);
                    $stmt->execute();
                }
            } catch (\Throwable $th) {
                echo  "Errore nel creare il quesito aperto: <br> ";
                echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
            }
        } elseif ($tipo_quesito == "CHIUSO") {

            $opzioni = $_POST['opzione'];
            $opzioni_vera = $_POST['opzione_vera'];
            try {
                $n_opzione = 1;
                for ($i = 0; $i < count($opzioni); $i++) {
                    if ($opzioni_vera[$i] == "on") {
                        $opzioni_vera[$i] = "TRUE";
                    }
                    $sql = "CALL InserisciNuovaOpzioneQuesitoChiuso(:numero_opzione, :numero_quesito, :test_associato, :opzioni, :opzioni_vera)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':numero_opzione', $n_opzione, PDO::PARAM_INT);
                    $stmt->bindParam(':numero_quesito', $n_quesito, PDO::PARAM_INT);
                    $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
                    $stmt->bindParam(':opzioni', $opzioni[$i], PDO::PARAM_STR);
                    $stmt->bindParam(':opzioni_vera', $opzioni_vera[$i], PDO::PARAM_STR);
                    $stmt->execute();
                    $n_opzione++;
                }
            } catch (\Throwable $th) {
                echo  "Errore nel creare il quesito chiuso: <br> ";
                echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
            }
        }
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
                <div>
                    <button type="button" id="aggiungi_quesito_aperto">Aggiungi soluzione</button><br>
                    <button type="button" id="rimuovi_quesito_aperto">Rimuovi soluzione</button><br>
                </div>
                <div id="soluzione_aperto">
                    <div class="quesito-aperto">
                    </div>
                </div>
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

                // <input name="soluzione[]" placeholder="Soluzione" type="text" required>
                var quesito_aperto = document.createElement('div');
                quesito_aperto.className = 'quesito-aperto';

                if (document.getElementById('soluzione_aperto').children.length == 1) {
                    quesito_aperto.innerHTML = `<input name="soluzione[]" placeholder="Soluzione" type="text" required>`;
                    document.getElementById('soluzione_aperto').appendChild(quesito_aperto);
                }

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



    // il quesito chiuso deve avere almeno una opzione vera
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


    // aggiunge righe per le opzioni del quesito chiuso
    document.addEventListener("DOMContentLoaded", function() {

        var quesito_chiuso_button = document.getElementById("aggiungi_quesito_chiuso");
        quesito_chiuso_button.addEventListener('click', function() {
            var quesito_chiuso = document.createElement('div');
            quesito_chiuso.className = 'quesito-chiuso';
            quesito_chiuso.innerHTML = `
                <input type="text" name="opzione[]" placeholder="Opzione" required>
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

    // aggiunge righe per il quesito aperto
    document.addEventListener("DOMContentLoaded", function() {
        var quesito_aperto_button = document.getElementById("aggiungi_quesito_aperto");
        quesito_aperto_button.addEventListener('click', function() {
            var quesito_aperto = document.createElement('div');
            quesito_aperto.className = 'quesito-aperto';
            quesito_aperto.innerHTML = `
                <input type="text" name="soluzione[]" placeholder="Soluzione" required>`;
            document.getElementById('soluzione_aperto').appendChild(quesito_aperto);
        });

        var rimuovi_quesito_chiuso_button = document.getElementById("rimuovi_quesito_aperto");
        rimuovi_quesito_chiuso_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('soluzione_aperto');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });

    });
</script>

</html>