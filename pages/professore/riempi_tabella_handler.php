<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = connectToDatabaseMYSQL();
    $nome_tabella = $_GET['nome_tabella'];
    // Prendi i valori inviati dal modulo
    echo "<script>console.log('POST: " . json_encode($_POST) . "');</script>";

    $valori_inviati = $_POST;


    // Costruisci la query di inserimento
    $column_names = implode(', ', array_keys($valori_inviati));
    $column_placeholders = implode(', ', array_fill(0, count($valori_inviati), '?'));
    $sql = "INSERT INTO $nome_tabella ($column_names) VALUES ($column_placeholders)";

    // Esegui la query preparata
    try {
        $stmt = $db->prepare($sql);;

        // TODO: fai la stessa cosa per l'inserimento di una query dell'utente


        // Verifica se l'inserimento è riuscito
        if ($stmt->execute(array_values($valori_inviati))) {
            redirect("Riga inserita con successo.");
        }
    } catch (PDOException $e) {
        $errorCode = $e->errorInfo[1];
        if ($errorCode == 1062) {
            redirect("Errore: Chiave primaria duplicata.");
        } else if ($errorCode == 1451 || $errorCode == 1452) {
            redirect("Errore: Violazione vincolo di chiave esterna.");
        } else {
            redirect("Errore: " . $e->getMessage());
        }
    }
}

function redirect($messaggio)
{

    $nome_tabella = $_GET['nome_tabella'];
    unset($_POST);
    echo "<script>alert('$messaggio');
    window.location.replace('/pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella')</script>";
}
