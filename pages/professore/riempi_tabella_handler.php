<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Verifica se il modulo è stato inviato


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!') window.location.replace('/pages/login.php')</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = connectToDatabaseMYSQL();
    $nome_tabella = $_GET['nome_tabella'];
    // Prendi i valori inviati dal modulo
    echo "<script>console.log('VALORI INVIATI: " . json_encode($_POST) . "');</script>";

    $valori_inviati = $_POST;


    // Costruisci la query di inserimento
    $column_names = implode(', ', array_keys($valori_inviati));
    $column_placeholders = implode(', ', array_fill(0, count($valori_inviati), '?'));
    $sql = "INSERT INTO $nome_tabella ($column_names) VALUES ($column_placeholders)";

    // Esegui la query preparata
    $stmt = $db->prepare($sql);;

    // Verifica se l'inserimento è riuscito
    if ($stmt->execute(array_values($valori_inviati))) {
        echo "Riga inserita con successo!";
        header("Location: /pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella");
    } else {
        echo "Si è verificato un errore durante l'inserimento della riga.";
    }
}
