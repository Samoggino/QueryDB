<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);



// se concludi o test associato sono 0 disabilita i due bottoni
if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}
if (isset($_POST['test_associato'])) {
    $test_associato = $_POST['test_associato'];
    if ($test_associato == 0) {
        unset($_POST['test_associato']);
        echo "<script>alert('Seleziona un test!'); window.location.replace('professore.php')</script>";
    } else {
        $_POST['titolo_test_creato'] = $test_associato;
        header("Location: crea_test.php?test_associato=" . $test_associato);
    }
}
if (isset($_POST['logout'])) {
    // Controlla se è stato fatto clic sul pulsante di logout
    if (isset($_POST['logout'])) {
        // Svuota e distruggi la sessione
        session_unset(); // Rimuove tutte le variabili di sessione
        session_destroy(); // Elimina completamente la sessione
        header('Location: /pages/login.php');
    }
}

?>


<!DOCTYPE html>
<html>

<head>
    <title>Creazione test</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
    <link rel="stylesheet" href="../../styles/professore.css">
    <script>
        // Definizione della funzione updateActionConcludiTest
        function updateActionConcludiTest(concludi) {

            if (concludi.value != 0 && concludi.value != null) {
                console.log(concludi.value); // Verifica che il valore sia corretto

                var selectedValue = concludi.value; // Usare l'elemento passato come parametro
                var form = document.getElementById("concludi-quesito-form");
                form.action = "/pages/professore/modifica_test.php?test_associato=" + selectedValue;
            }
        }

        // Chiamata iniziale per generare l'URL quando la pagina viene caricata
        updateActionConcludiTest(document.getElementById("concludi"));
    </script>

    <style>

    </style>
</head>

<body>

    <div id="intestazione">
        <div class="icons-container">
            <a class="logout" href='/pages/logout.php'></a>
        </div>
        <h1>Buongiorno professore</h1>
    </div>

    <div class="container-professore">
        <div class="widget-professore">
            <h3>Crea un test</h3>
            <button onclick="window.location.href='/pages/professore/crea_test.php';">Crea</button>
        </div>

        <div class="widget-professore">
            <h3>Aggiungi quesito ad un test</h3>
            <!-- <h4>Scegli un test</h4> -->
            <form id="aggiungi-quesito-form" method="post">
                <div>
                    <select name="test_associato" for="test_associato" onchange="updateActionAggiungiQuesito(this)">
                        <?php
                        require_once "./tendina_test.php";
                        tendinaTest();
                        ?>
                    </select>
                </div>
                <button type="submit" style="width:fit-content"> Aggiungi quesito </button>
            </form>
        </div>

        <div class="widget-professore">
            <h3>Concludi test</h3>
            <form id="concludi-quesito-form" method="post">
                <div>
                    <select name="concludi" for="concludi" onchange="updateActionConcludiTest(this)">
                        <?php
                        require_once "./tendina_test.php";
                        tendinaTest();
                        ?>
                    </select>
                </div>
                <button type="submit" style="width:fit-content"> Concludi test </button>
            </form>
        </div>

        <div class="widget-professore" style="cursor: pointer;">
            <h3>Vai ai messaggi</h3>
            <button onclick="window.location.href='/pages/messaggi.php';">Messaggi</button>
        </div>


        <div class="widget-professore">
            <h3>Vai a creazione tabella </h3>
            <button onclick="window.location.href='/pages/professore/crea_tabella_esercizio.php';">Crea tabella</button>
        </div>

        <div class="widget-professore">
            <h3>Vai a classifiche </h3>
            <button onclick="window.location.href='/pages/classifiche.php';">Classifiche</button>
        </div>

        <div class="widget-professore">
            <!-- scegli la tabella in cui inserire nuovi valori -->
            <h3>Inserisci valori in tabella</h3>
            <form id="inserisci-valori-form" method="post">
                <div>
                    <select name=" nome_tabella" id="nome_tabella" onchange="updateActionRiempiTabella(this)">
                        <?php
                        require_once '../../helper/connessione_mysql.php';
                        try {
                            $db = connectToDatabaseMYSQL();
                            $sql = "CALL GetTabelleCreate();";
                            $stmt = $db->prepare($sql);
                            $stmt->execute();
                            $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tabelle as $tabella) {
                                echo "<option value='" . $tabella['nome_tabella'] . "'>" . $tabella['nome_tabella'] . "</option>";
                            }
                        } catch (\Throwable $th) {
                            echo "<script>console.log('Errore: " . $th . "');</script>";
                        }
                        $stmt->closeCursor();
                        $db = null;
                        ?>
                    </select>
                </div>
                <button type="submit"> Vai </button>
            </form>
        </div>
    </div>
</body>


<script>
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
        if (selectedValue === 0) {
            alert("Seleziona un test!");
            return;
        }
        var form = document.getElementById("aggiungi-quesito-form");
        form.action = "/pages/professore/crea_test.php?test_associato=" + selectedValue;
    }
    updateActionAggiungiQuesito();
</script>

</html>