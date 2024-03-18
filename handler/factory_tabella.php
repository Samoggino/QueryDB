<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {

    $db = connectToDatabaseMYSQL();

    $input_data = array(
        'nome_tabella' => 'PRASSI',
        'numero_attributi' => 3,
        'attributi' => array(
            array('nome' => 'matricola', 'tipo' => 'INT'),
            array('nome' => 'cognome_prassi', 'tipo' => 'VARCHAR'),
            array('nome' => 'nome_prassi', 'tipo' => 'VARCHAR')
        ),
        'primary_keys' => array(0),
        'foreign_keys' => array(
            array('attributo' => 'cognome_prassi', 'tabella_riferimento' => 'tabella_di_esempio', 'attributo_riferimento' => 'cognome'),
            array('attributo' => 'nome_prassi', 'tabella_riferimento' => 'tabella_di_esempio', 'attributo_riferimento' => 'nome')
        )
    );
    // Recupera i dati inviati dal form
    // Rimuovi il recupero dei dati dalle variabili POST
    // $nome_tabella = $_POST['nome_tabella'];
    // $numero_attributi = $_POST['numero_attributi'];
    // $nome_attributo = $_POST['nome_attributo'];
    // $tipo_attributo = $_POST['tipo_attributo'];
    // $primary_keys = isset($_POST['primary_key']) ? $_POST['primary_key'] : array();
    // $foreign_key = isset($_POST['foreign_key']) ? $_POST['foreign_key'] : array();
    // $array_tabelle_vincolate = $_POST['tabella_vincolata'];
    // $array_attributi_vincolati = $_POST['attributo_vincolato'];


    // Utilizza i dati forniti nell'array associativo
    $nome_tabella = $input_data['nome_tabella'];
    $numero_attributi = $input_data['numero_attributi'];
    $attributi = $input_data['attributi'];
    $primary_keys = $input_data['primary_keys'];
    $foreign_keys = $input_data['foreign_keys'];

    // Estrai i dati dagli attributi nell'array associativo
    $nome_attributo = array_column($attributi, 'nome');
    $tipo_attributo = array_column($attributi, 'tipo');

    // Utilizza invece i dati forniti nelle chiavi esterne dell'array associativo
    $array_tabelle_vincolate = array_column($foreign_keys, 'tabella_riferimento');
    $array_attributi_vincolati = array_column($foreign_keys, 'attributo_riferimento');



    creaLaTabella(
        $nome_tabella,
        $numero_attributi,
        $nome_attributo,
        $tipo_attributo,
        $primary_keys,
        $foreign_keys,
        $array_tabelle_vincolate,
        $array_attributi_vincolati
    );


    // Esecuzione della query di creazione

    // $tabellaCreata = true;
    try {
        // inserisciInTabellaDelleTabelle($nome_tabella);
    } catch (\Throwable $th) {
        $tabellaCreata = false;
        echo "Errore nella creazione della tabella nella tabella delle tabelle! <br>";
        echo $th->getMessage();
    }

    try {
        // inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella);
    } catch (\Throwable $th) {
        $tabellaCreata = false;
        echo "Errore nell'inserimento degli attributi nella tabella degli attributi! <br>";
        echo $th->getMessage();
    }

    $db = null;
} catch (\Throwable $th) {
    echo $th->getMessage();
}

function creaLaTabella(
    $nome_tabella,
    $numero_attributi,
    $nome_attributo,
    $tipo_attributo,
    $primary_keys,
    $foreign_keys,
    $array_tabelle_vincolate,
    $array_attributi_vincolati
) {


    // foreach ($nome_attributo as $key => $value) {
    //     echo "<script>console.log('Nome attributo: $value');</script>";
    // }

    // foreach ($tipo_attributo as $key => $value) {
    //     echo "<script>console.log('Tipo attributo: $value');</script>";
    // }

    // foreach ($primary_keys as $key => $value) {
    //     echo "<script>console.log('Chiave primaria: $value');</script>";
    // }

    // foreach ($foreign_keys as $key => $value) {
    //     echo "<script>console.log('Chiave esterna: $value');</script>";
    // }

    // foreach ($array_tabelle_vincolate as $key => $value) {
    //     echo "<script>console.log('Tabella vincolata: $value');</script>";
    // }

    // foreach ($array_attributi_vincolati as $key => $value) {
    //     echo "<script>console.log('Attributo vincolato: $value');</script>";
    // }

    $conn = connectToDatabaseMYSQL();
    // Creazione della tabella nel database
    $createTableQuery = "CREATE TABLE IF NOT EXISTS $nome_tabella (";
    // Aggiunge gli attributi dinamici alla query di creazione
    for ($i = 0; $i < $numero_attributi; $i++) {
        $createTableQuery .= $nome_attributo[$i] . " ";
        // Se il tipo è VARCHAR, aggiungi la grandezza specificata
        if ($tipo_attributo[$i] == 'VARCHAR') {
            $createTableQuery .= $tipo_attributo[$i] . "(100)";
        } else {
            $createTableQuery .= $tipo_attributo[$i];
        }
        // Aggiunge virgola se non è l'ultimo attributo
        if ($i < $numero_attributi - 1) {
            $createTableQuery .= ", ";
        }
    }
    // Aggiungi le chiavi primarie
    if (count($primary_keys) > 0) {
        $createTableQuery .= ", PRIMARY KEY (";
        foreach ($primary_keys as $key) {
            $createTableQuery .= $nome_attributo[$key] . ", ";
        }
        $createTableQuery = rtrim($createTableQuery, ", "); // Rimuove l'ultima virgola
        $createTableQuery .= ")";
    }

    foreach ($foreign_keys as $foreign_key) {
        $referenceTable = $foreign_key['tabella_riferimento'];
        $referenceAttribute = $foreign_key['attributo_riferimento'];

        // Aggiungi i valori alle rispettive array
        $array_tabelle_vincolate[] = $referenceTable;
        $array_attributi_vincolati[] = $referenceAttribute;
    }

    // // Aggiungi le chiavi esterne
    if (!empty($foreign_keys) && !empty($array_attributi_vincolati)) {
        $foreign_keyQuery = '';
        $references = array();

        foreach ($foreign_keys as $key => $foreign_key) {
            $referenceTable = $foreign_key['tabella_riferimento'];
            $referenceAttribute = $foreign_key['attributo_riferimento'];

            if (!isset($references[$referenceTable])) {
                $references[$referenceTable] = array();
            }
            $references[$referenceTable][] = $referenceAttribute;
        }

        foreach ($references as $referenceTable => $referenceAttributes) {
            // $foreign_keyQuery .= ", FOREIGN KEY (" . implode(', ', $referenceAttributes) . ") REFERENCES $referenceTable(" . implode(', ', $referenceAttributes) . ") ON DELETE CASCADE";
            $foreign_keyQuery .= ", FOREIGN KEY ( cognome_prassi, nome_prassi  ) REFERENCES $referenceTable(" . implode(', ', $referenceAttributes) . ") ON DELETE CASCADE";
        }

        $createTableQuery .= $foreign_keyQuery;
    }

    $createTableQuery .= ");";
    echo "<script>console.log('$createTableQuery');</script>";
    // Esegui la query di creazione della tabella
    try {
        // delete if exists
        $conn->exec("DROP TABLE IF EXISTS $nome_tabella");
        $conn->exec($createTableQuery);
        echo "Tabella creata con successo.";
    } catch (PDOException $e) {
        echo "Errore durante la creazione della tabella: " . $e->getMessage();
    }
}





function inserisciInTabellaDelleTabelle($nome_tabella)
{
    $conn = connectToDatabaseMYSQL();
    $stmt = $conn->prepare("INSERT INTO TABELLA_DELLE_TABELLE (nome_tabella) VALUES (:nome_tabella)");
    $stmt->bindParam(':nome_tabella', $nome_tabella);
    $stmt->execute();
}

function inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella)
{
    $conn = connectToDatabaseMYSQL();
    for ($i = 0; $i < $numero_attributi; $i++) {
        $stmt = $conn->prepare("INSERT INTO TAB_ATT (nome_tabella, nome_attributo, tipo_attributo) VALUES (:nome_tabella, :nome_attributo, :tipo_attributo)");
        $stmt->bindParam(':nome_tabella', $nome_tabella);
        $stmt->bindParam(':nome_attributo', $nome_attributo[$i]);
        $stmt->bindParam(':tipo_attributo', $tipo_attributo[$i]);
        $stmt->execute();
    }
}
