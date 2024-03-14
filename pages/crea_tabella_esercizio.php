<?php
session_start();
require_once '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserimento Dati Tabella</title>
</head>

<body>
    <h2>Inserimento Dati Tabella</h2>
    <form id="crea_tabella_form" action="../handler/crea_tabella.php" method="POST">
        <label for="nome_tabella">Nome Tabella:</label>
        <input type="text" id="nome_tabella" name="nome_tabella" required><br><br>

        <label for="numero_attributi">Numero di Attributi:</label>
        <input type="number" id="numero_attributi" name="numero_attributi" min="1" required><br><br>

        <div id="attributi_container"></div><br><br>

        <input type="submit" value="Inserisci">
    </form>

    <script>
        document.getElementById("numero_attributi").addEventListener("change", function() {
            var numeroAttributi = parseInt(this.value);
            var container = document.getElementById("attributi_container");
            container.innerHTML = '';

            for (var i = 0; i < numeroAttributi; i++) {
                var div = document.createElement("div");
                div.innerHTML = '<label for="nome_attributo_' + i + '">Nome Attributo ' + (i + 1) + ':</label>' +
                    '<input type="text" id="nome_attributo_' + i + '" name="nome_attributo[]" required>' +
                    '<label for="tipo_attributo_' + i + '">Tipo Attributo ' + (i + 1) + ':</label>' +
                    '<select id="tipo_attributo_' + i + '" name="tipo_attributo[]" required>' +
                    '<option value="INT">INT</option>' +
                    '<option value="VARCHAR">VARCHAR</option>' +
                    '<option value="DATE">DATE</option>' +
                    '</select>' +
                    '<input type="checkbox" id="primary_key_' + i + '" name="primary_key[]" value="' + i + '">' +
                    '<label for="primary_key_' + i + '">Primary Key</label>' +
                    '<br><br>';
                container.appendChild(div);
            }
        });

        // Aggiungi un gestore di eventi per il submit del modulo
        document.getElementById("crea_tabella_form").addEventListener("submit", function(event) {
            // Recupera tutte le checkbox delle chiavi primarie
            var checkboxes = document.getElementsByName("primary_key[]");
            var isChecked = false;

            // Verifica se almeno una checkbox è stata selezionata
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    isChecked = true;
                    break;
                }
            }

            // Se nessuna checkbox è ,selezionata impedisci l'invio del modulo
            if (!isChecked) {
                event.preventDefault();
                alert("È necessario selezionare almeno una chiave primaria.");
            }
        });
    </script>



</body>

</html>