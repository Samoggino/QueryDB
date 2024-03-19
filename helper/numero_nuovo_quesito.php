<?php
session_start();
require_once "../helper/connessione_mysql.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);


function getNumeroNuovoQuesito($titolo_test)
{
    $db = connectToDatabaseMYSQL();


    $sql = "CALL GetNumeroNuovoQuesito(:titolo_test)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':titolo_test', $titolo_test, PDO::PARAM_STR);
    $stmt->execute();
    $ultimo_quesito = $stmt->fetch(PDO::FETCH_ASSOC);

    if (count($ultimo_quesito) == 0) {
        return 1;
    } else {
        return $ultimo_quesito['numero_quesito'] + 1;
    }
}
