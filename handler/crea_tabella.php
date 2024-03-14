<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {

    $conn = connectToDatabaseMYSQL();


    // Recupera i dati inviati dal form
    $nomeTabella = $_POST['nome_tabella'];
    $numeroAttributi = $_POST['numero_attributi'];
    $nomiAttributi = $_POST['nome_attributo'];
    $tipiAttributi = $_POST['tipo_attributo'];
    $primaryKeys = isset($_POST['primary_key']) ? $_POST['primary_key'] : array();

    creaLaTabella($nomeTabella, $numeroAttributi, $nomiAttributi, $tipiAttributi, $primaryKeys);


    // Esecuzione della query di creazione

    // $tabellaCreata = true;
    try {
        inserisciInTabellaDelleTabelle($nomeTabella);
    } catch (\Throwable $th) {
        $tabellaCreata = false;
        echo "Errore nella creazione della tabella nella tabella delle tabelle! <br>";
        echo $th->getMessage();
    }

    try {
        inserisciInAttributi($numeroAttributi, $nomiAttributi, $tipiAttributi);
    } catch (\Throwable $th) {
        $tabellaCreata = false;
        echo "Errore nell'inserimento degli attributi nella tabella degli attributi! <br>";
        echo $th->getMessage();
    }

    // try {
    //     inserisciInRelazioneAttributiTabella($nomeTabella, $nomiAttributi);
    // } catch (\Throwable $th) {
    //     $tabellaCreata = false;
    //     echo "Errore nell'inserimento della relazione tra attributi e tabella! <br>";
    //     echo $th->getMessage();
    // }


    $conn = null;
} catch (\Throwable $th) {
    echo $th->getMessage();
}

function creaLaTabella($nomeTabella, $numeroAttributi, $nomiAttributi, $tipiAttributi, $primaryKeys)
{

    $conn = connectToDatabaseMYSQL();

    // Creazione della tabella nel database
    $createTableQuery = "CREATE TABLE IF NOT EXISTS $nomeTabella (";

    // Aggiunge gli attributi dinamici alla query di creazione
    for ($i = 0; $i < $numeroAttributi; $i++) {
        $createTableQuery .= $nomiAttributi[$i] . " ";

        // Se il tipo è VARCHAR, aggiungi la grandezza specificata
        if ($tipiAttributi[$i] == 'VARCHAR') {
            $createTableQuery .= $tipiAttributi[$i] . "(100)";
        } else {
            $createTableQuery .= $tipiAttributi[$i];
        }

        $createTableQuery .= ", ";
    }

    // aggiungi le chiavi primarie
    $createTableQuery .= "  PRIMARY KEY (";

    for ($i = 0; $i < $numeroAttributi; $i++) {

        if (in_array($i, $primaryKeys)) {
            $createTableQuery .= " " . $nomiAttributi[$i];
            if ($i < count($primaryKeys) - 1) {
                $createTableQuery .= ", ";
            }
        }
    }
    $createTableQuery .= "));";

    echo $createTableQuery;

    return $conn->exec($createTableQuery);
}

function inserisciInTabellaDelleTabelle($nomeTabella)
{
    $conn = connectToDatabaseMYSQL();
    $stmt = $conn->prepare("INSERT INTO TABELLA_DELLE_TABELLE (nome_tabella) VALUES (:nome_tabella)");
    $stmt->bindParam(':nome_tabella', $nomeTabella);
    $stmt->execute();
}

function inserisciInAttributi($numeroAttributi, $nomiAttributi, $tipiAttributi)
{
    $conn = connectToDatabaseMYSQL();
    for ($i = 0; $i < $numeroAttributi; $i++) {
        $stmt = $conn->prepare("INSERT INTO ATTRIBUTI (nome_attributo, tipo_attributo) VALUES (:nome_attributo, :tipo_attributo)");
        $stmt->bindParam(':nome_attributo', $nomiAttributi[$i]);
        $stmt->bindParam(':tipo_attributo', $tipiAttributi[$i]);
        $stmt->execute();
    }
}

function inserisciInRelazioneAttributiTabella($nomeTabella, $nomiAttributi)
{
    $conn = connectToDatabaseMYSQL();
    for ($i = 0; $i < count($nomiAttributi); $i++) {
        $stmt = $conn->prepare("INSERT INTO RELAZIONE_ATTRIBUTI_TABELLA (nome_tabella, nome_attributo) VALUES (:nome_tabella, :nome_attributo)");
        $stmt->bindParam(':nome_tabella', $nomeTabella);
        $stmt->bindParam(':nome_attributo', $nomiAttributi[$i]);
        $stmt->execute();
    }
}
