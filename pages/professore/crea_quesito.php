<?php
session_start();
require_once "../../helper/connessione_mysql.php";
require_once "../../helper/numero_nuovo_quesito.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}
try {

    $db = connectToDatabaseMYSQL();

    $test_associato = $_GET['test_associato'];
    $numero_quesito = getNumeroNuovoQuesito($test_associato);
    echo "<h1>Titolo test: " . $test_associato . "</h1>";
    echo "<script>console.log('Numero quesito: " . $numero_quesito . "');</script>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "<script>console.log('Ciao');</script>";
        echo "<script>console.log('VALORI INVIATI: " . json_encode($_POST) . "');</script>";
        $descrizione = $_POST['descrizione'];
        $livello_difficolta = $_POST['difficolta'];
        $tipo_quesito = $_POST['tipo_quesito'];

        try {
            $sql = "CALL InserisciNuovoQuesito(:numero_quesito, :test_associato, :descrizione, :livello_difficolta, :tipo_quesito, @id_nuovo_quesito)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':numero_quesito', $numero_quesito, PDO::PARAM_INT);
            $stmt->bindParam(':test_associato', $test_associato, PDO::PARAM_STR);
            $stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $stmt->bindParam(':livello_difficolta', $livello_difficolta, PDO::PARAM_STR);
            $stmt->bindParam(':tipo_quesito', $tipo_quesito, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();

            $stmt = $db->query("SELECT @id_nuovo_quesito AS id_quesito");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $id_quesito = $result['id_quesito'];
        } catch (\Throwable $th) {
            echo  "Errore nel creare il quesito: <br> ";
            echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
        }

        if ($tipo_quesito == "APERTO") {
            try {
                $soluzioni = $_POST['soluzione'];
                for ($i = 0; $i < count($soluzioni); $i++) {
                    $soluzioni[$i] = str_replace('"', "'", $soluzioni[$i]);
                    $sql = "CALL InserisciNuovaSoluzioneQuesitoAperto(:id_quesito, :soluzione)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito, PDO::PARAM_STR);
                    $stmt->bindParam(':soluzione', $soluzioni[$i], PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
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
                    $sql = "CALL InserisciNuovaOpzioneQuesitoChiuso(:numero_opzione, :id_quesito, :opzioni, :opzioni_vera)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':numero_opzione', $n_opzione, PDO::PARAM_INT);
                    $stmt->bindParam(':id_quesito', $id_quesito, PDO::PARAM_STR);
                    $stmt->bindParam(':opzioni', $opzioni[$i], PDO::PARAM_STR);
                    $stmt->bindParam(':opzioni_vera', $opzioni_vera[$i], PDO::PARAM_STR);
                    $stmt->execute();
                    $n_opzione++;
                    $stmt->closeCursor();
                }
            } catch (\Throwable $th) {
                echo  "Errore nel creare il quesito chiuso: <br> ";
                echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
            }
        }


        if (isset($_POST['tabelle'])) {
            $tabelle = $_POST['tabelle'];
            //FIXME: ho rotto qualcosa su questa stored procedure perchè la uso sia qua che in esegui_test.php
            echo "<script>console.log('Tabelle: " . json_encode($tabelle) . "');</script>";
            try {
                foreach ($tabelle as $tabella) {
                    $sql = "CALL InserisciQuesitoTabella(:id_quesito, :tabella_riferimento)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito, PDO::PARAM_INT);
                    $stmt->bindParam(':tabella_riferimento', $tabella, PDO::PARAM_STR);
                    // $stmt->execute();
                    $stmt->closeCursor();
                }
            } catch (\Throwable $th) {
                echo  "Errore nel creare il riferimento: <br> ";
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
        <!-- seleziona le tabelle di esercizio a cui fare riferimento -->
        <select id="tabelleRiferimento" name="tabelle[]" multiple>
            <?php
            $sql = "CALL GetTabelleCreate()";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                echo "<option value='" . $row['nome_tabella'] . "'>" . $row['nome_tabella'] . "</option>";
            }
            ?>
        </select>

        <input type="hidden" for="tipo_quesito" name="tipo_quesito" id="tipo_quesito" value="">
        <button type="submit" value="crea il test">Crea il test</button>

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