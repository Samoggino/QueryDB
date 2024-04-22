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
            try {
                foreach ($tabelle as $tabella) {
                    $sql = "CALL InserisciQuesitoTabella(:id_quesito, :tabella_riferimento)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id_quesito', $id_quesito, PDO::PARAM_INT);
                    $stmt->bindParam(':tabella_riferimento', $tabella, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
                }
            } catch (\Throwable $th) {
                echo  "Errore nel creare il riferimento: <br> ";
                echo  "<br> SQL: " . $sql . "<br>" . $th->getMessage();
            }
        }
    }
    $stmt->closeCursor();
    $db = null;
    header("Location: crea_test.php?test_associato=" . $test_associato);
    exit();
} catch (\Throwable $th) {
    echo  "Errore: " . $th->getMessage();
}
