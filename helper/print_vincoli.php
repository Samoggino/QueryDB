<?php
require_once 'connessione_mysql.php';
function stampaVincoli($nome_tabella)
{
    $db = connectToDatabaseMYSQL();
    $stmt = $db->prepare("CALL GetChiaviEsterne(:nome_tabella)");
    $stmt->bindParam(':nome_tabella', $nome_tabella);
    $stmt->execute();
    $chiavi_est = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach ($chiavi_est as $chiave) {
        echo strtoupper($chiave['nome_tabella']) . "." . $chiave['nome_attributo'] . " -> " .
            strtoupper($chiave['tabella_vincolata']) . "." . $chiave['attributo_vincolato'] . "<br>";
    }
}
