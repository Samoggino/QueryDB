<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

function aggiungiTabellaInDatabase($nome_tabella, $tipo_attributo, $nome_attributo, $foreign_key)
{
    $numero_attributi = count($nome_attributo) == count($tipo_attributo) ? count($nome_attributo) : 0;
    // Inserimento della tabella nel database delle tabelle create
    inserisciInTabellaDelleTabelle($nome_tabella);
    inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella);
    inserisciForeignKey($foreign_key, $nome_tabella);
}

function inserisciInTabellaDelleTabelle($nome_tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("CALL InserisciTabellaDiEsercizio(:nome_tabella, :creatore)");
        $stmt->bindParam(':nome_tabella', $nome_tabella);
        $stmt->bindParam(':creatore', $_SESSION['email']);
        $stmt->execute();
        $stmt->closeCursor();
        $db = null;
    } catch (\Throwable $th) {
        echo "TABLE^2 PROBLEM <br>" . $th->getMessage();
    }
}

function inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        for ($i = 0; $i < $numero_attributi; $i++) {
            $stmt = $db->prepare("CALL InserisciAttributo(:nome_tabella, :nome_attributo, :tipo_attributo)");
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $nome_attributo[$i]);
            $stmt->bindParam(':tipo_attributo', $tipo_attributo[$i]);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "TAB_ATT PROBLEM <br>" . $th->getMessage();
    }
}

function inserisciForeignKey($foreign_key, $nome_tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        foreach ($foreign_key as $key => $value) {
            $stmt = $db->prepare("CALL InserisciForeignKey(:nome_tabella, :nome_attributo, :tabella_riferita, :attributo_riferito)");
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $key);
            $stmt->bindParam(':tabella_riferita', $value['tabella_riferita']);
            $stmt->bindParam(':attributo_riferito', $value['attributo_riferito']);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "FOREIGN PROBLEM <br>" . $th->getMessage();
    }
}
