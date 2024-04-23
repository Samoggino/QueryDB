<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['test_associato'])) {
    $test_associato = $_GET['test_associato'];
    $db = connectToDatabaseMYSQL();
    $sql = "CALL MostraRisultati(:titolo);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo', $test_associato, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->closeCursor();

    // svuota il post per non aver problemi
    unset($_POST);
    echo "<script>alert('$test_associato ora è chiuso!');window.location.replace('professore.php')</script>";
}
