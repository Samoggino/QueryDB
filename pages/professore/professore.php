<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!') window.location.replace('/pages/login.php')</script>";
}
if (isset($_POST['test_associato'])) {
    $test_associato = $_POST['test_associato'];
    header("Location: crea_quesito.php?test_associato=" . $test_associato);
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <style>
        /* Stile generale */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1,
        h2,
        h3 {
            color: #333;
        }

        /* Stile per i form */
        form {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="file"],
        input[type="submit"],
        select {
            /* fade-in */
            transition: all 0.15s ease;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Stile per i link */
        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>

</head>

<body>
    <div class="container">

        <h1>Buongiorno professore!</h1>
        <?php
        echo "<h2>La tua email è : " . $_SESSION['email'] . "</h2>";
        ?>
        <h2>Crea un test</h2>
        <form id="uploadForm" method="post" action="crea_test.php" enctype="multipart/form-data">
            <input for="titolo_test_creato" name="titolo_test_creato" placeholder="Titolo" type="text" required>
            <div>
                <label for="file_immagine" name="file_immagine">Seleziona un'immagine:</label><br>
                <input type="file" name="file_immagine" accept="image/*"><br>
            </div>
            <input type="submit" value="Crea">
        </form>

        <h2>Aggiungi quesito</h2>
        <h3>Scegli un test</h3>
        <form id="aggiungi-quesito-form" method="post">
            <select name=" test_associato" for="test_associato" onchange="updateActionAggiungiQuesito(this)">
                <?php
                require_once "./tendina_test.php";
                tendinaTest();
                ?>
            </select>
            <input type="submit" value="Aggiungi quesito">
        </form>


        <h2>Concludi test</h2>
        <?php
        require_once "./tendina_test.php";
        $db = connectToDatabaseMYSQL();
        $sql = "CALL GetTestDelProfessore(:email_professore);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email_professore', $_SESSION['email']);
        try {
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tests as $test) {
                echo "<a href='modifica_test.php?test_associato=" . $test['titolo'] . "'>" . $test['titolo'] . "</a>" . "<br>";
            }
        } catch (\Throwable $th) {
            echo "<script>console.log('Errore: " . $th . "');</script>";
        }
        $stmt->closeCursor();
        ?>


        <h2>Vai ai messaggi</h2>
        <a href="/pages/messaggi.php">Messaggi</a>

        <h2>Vai a creazione tabella </h2>
        <a href="/pages/professore/crea_tabella_esercizio.php">Crea tabella</a>

        <!-- scegli la tabella in cui inserire nuovi valori -->
        <h2>Inserisci valori in tabella</h2>
        <form id="inserisci-valori-form" method="post">
            <select name="nome_tabella" id="nome_tabella" onchange="updateActionRiempiTabella(this)">
                <?php
                $sql = "CALL GetTabelleCreate();";
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute();
                    $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($tabelle as $tabella) {
                        echo "<option value='" . $tabella['nome_tabella'] . "'>" . $tabella['nome_tabella'] . "</option>";
                    }
                } catch (\Throwable $th) {
                    echo "<script>console.log('Errore: " . $th . "');</script>";
                }
                $stmt->closeCursor();
                ?>
            </select>
            <input type="submit" value="Inserisci valori">
        </form>
    </div>
</body>


<script>
    // Pulisci il form quando la pagina viene caricata o ricaricata
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('uploadForm').reset();
    });

    window.addEventListener('load', function() {
        document.getElementById('uploadForm').reset();
    });

    // Funzione per aggiornare l'URL dell'azione del form
    function updateActionRiempiTabella() {
        var select = document.getElementById("nome_tabella");
        var selectedValue = select.value;
        var form = document.getElementById("inserisci-valori-form");
        form.action = "/pages/professore/riempi_tabella.php?nome_tabella=" + selectedValue;
    }

    // Chiamata iniziale per generare l'URL quando la pagina viene caricata
    updateActionRiempiTabella();

    // Funzione per aggiornare l'URL dell'azione del form
    function updateActionAggiungiQuesito() {
        var select = document.getElementById("test_associato");
        var selectedValue = select.value;
        var form = document.getElementById("aggiungi-quesito-form");
        form.action = "/pages/professore/crea_quesito.php?test_associato=" + selectedValue;
    }

    // Chiamata iniziale per generare l'URL quando la pagina viene caricata
    // updateActionAggiungiQuesito();
</script>

</html>