<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "../../helper/connessione_mysql.php";


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connessione al database
        $db = connectToDatabaseMYSQL();

        $test_associato = $_POST['titolo_test_creato'];
        if ($_SESSION['test_associato'] != $test_associato) {

            $sql = "CALL InserisciNuovoTest(:test_associato, :email_professore)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':test_associato', $test_associato);
            $stmt->bindParam(':email_professore', $_SESSION['email']);


            if ($stmt->execute()) {
                echo "<script> alert('Test inserito con successo!');</script>";
                $stmt->closeCursor();

                // Salva il titolo del test nella sessione
                $_SESSION['test_associato'] = $test_associato;
            } else {
                echo "<script> alert('Errore durante l'inserimento del test!');</script>";
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
                $tabelle_di_esercizio = $stmt->fetch(PDO::FETCH_ASSOC);

                // Se è stata trovata un'immagine, visualizzala
                if ($tabelle_di_esercizio) {
                    $immagine = $tabelle_di_esercizio['foto'];
                    echo "<h2>Immagine Caricata</h2>";
                    echo "<img src='data:image/jpeg;base64," . base64_encode($immagine) . "' alt='Immagine Caricata'>";
                } else {
                    echo "Nessuna immagine trovata per questo T.";
                }
            }
        }



        //header("Location: ../professore/crea_quesito.php?test_associato=" . $test_associato);
        echo "<input id = 'test_associato' value = '$test_associato'></input>";
        echo "<script>console.log(test_associato);</script>";
    }
} catch (\Throwable $th) {
    echo "ERRORE:<br>" . $th->getMessage() . "";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Document</title>
    <link rel="stylesheet" href="../../styles/global.css">
    <style>
        #quesiti.on {
            /* display: none; */
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }

        #quesiti.off {
            display: none;
        }
    </style>
</head>

<body>

    <form id="uploadForm" method="post" action="" enctype="multipart/form-data">

        <input for="titolo_test_creato" name="titolo_test_creato" placeholder="Titolo" type="text" required>
        <div id="select-image">
            <label for="file_immagine" name="file_immagine">Seleziona un'immagine:</label><br>
            <input type="file" name="file_immagine" accept="image/*"><br>
        </div>
        <button type="submit"> Crea </button>
    </form>

    <div id="quesiti" class="off">
        <!-- crea dei quesiti per il test, il quesito è fatto con un enum per la difficoltà e un campo per la descrizione -->
        <form method="POST" action="crea_quesito.php" id="form-quesito">
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
                        <button type="button" id="aggiungi_soluzione">Aggiungi soluzione</button><br>
                        <button type="button" id="rimuovi_soluzione">Rimuovi soluzione</button><br>
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
                        <button type="button" id="aggiungi_opzione">Aggiungi opzione</button><br>
                        <button type="button" id="rimuovi_opzione">Rimuovi opzione</button><br>
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
                $db = connectToDatabaseMYSQL();
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
    </div>

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

        var quesito_chiuso_button = document.getElementById("aggiungi_opzione");
        quesito_chiuso_button.addEventListener('click', function() {
            var quesito_chiuso = document.createElement('div');
            quesito_chiuso.className = 'quesito-chiuso';
            quesito_chiuso.innerHTML = `
                <input type="text" name="opzione[]" placeholder="Opzione" required>
                <input type="checkbox" name="opzione_vera[]"> Opzione Vera`;
            document.getElementById('opzioni_chiuso').appendChild(quesito_chiuso);
        });


        var rimuovi_opzione_button = document.getElementById("rimuovi_opzione");
        rimuovi_opzione_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('opzioni_chiuso');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });
    });

    // aggiunge righe per il quesito aperto
    document.addEventListener("DOMContentLoaded", function() {
        var quesito_aperto_button = document.getElementById("aggiungi_soluzione");
        quesito_aperto_button.addEventListener('click', function() {
            var quesito_aperto = document.createElement('div');
            quesito_aperto.className = 'quesito-aperto';
            quesito_aperto.innerHTML = `
                <input type="text" name="soluzione[]" placeholder="Soluzione" required>`;
            document.getElementById('soluzione_aperto').appendChild(quesito_aperto);
        });

        var rimuovi_opzione_button = document.getElementById("rimuovi_soluzione");
        rimuovi_opzione_button.addEventListener('click', function() {
            var opzioni_chiuso = document.getElementById('soluzione_aperto');
            if (opzioni_chiuso.children.length > 1) {
                opzioni_chiuso.removeChild(opzioni_chiuso.lastChild);
            }
        });

    });

    // se è presente il test mostra il form per i quesiti
    document.addEventListener("DOMContentLoaded", function() {
        var test_associato = document.getElementById("test_associato");

        if (test_associato != null) {
            console.log("value: " + test_associato.value);
            document.getElementById("uploadForm").style.display = "none";
            document.getElementById("quesiti").className = "on";
        } else {
            document.getElementById("quesiti").className = "off";
        }
    });
</script>

</html>