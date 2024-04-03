<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {

    $db = connectToDatabaseMYSQL();
    // TODO: ha il problema dell'ordine della chiave della tabella di riferimento
    // un modo per risolvere è quello di creare una tabella delle chiavi.

    $input_data = array(
        'nome_tabella' => 'PRASSI',
        'numero_attributi' => 3,
        'attributi' => array(
            array(
                'nome' => 'matricola',
                'tipo' => 'INT'
            ),
            array(
                'nome' => 'cognome_prassi',
                'tipo' => 'VARCHAR'
            ),
            array(
                'nome' => 'nome_prassi',
                'tipo' => 'VARCHAR'
            ),
            array(
                'nome' => 'id_tabella1',
                'tipo' => 'INT'
            )
        ),
        'primary_keys' => array(0),
        'foreign_keys' => array(
            array(
                'attributo' => 'cognome_prassi',
                'tabella_riferimento' => 'tabella_di_esempio',
                'attributo_riferimento' => 'cognome'
            ),
            array(
                'attributo' => 'nome_prassi',
                'tabella_riferimento' => 'tabella_di_esempio',
                'attributo_riferimento' => 'nome'
            ),
            array(
                'attributo' => 'id_tabella1',
                'tabella_riferimento' => 'Tabella1',
                'attributo_riferimento' => 'id'
            )
        )
    );

    //  Recupera i dati inviati dal form
    //  Rimuovi il recupero dei dati dalle variabili POST
    //  $nome_tabella = $_POST['nome_tabella'];
    //  $numero_attributi = $_POST['numero_attributi'];
    //  $nome_attributo = $_POST['nome_attributo'];
    //  $tipo_attributo = $_POST['tipo_attributo'];
    //  $primary_keys = isset($_POST['primary_key']) ? $_POST['primary_key'] : array();
    //  $foreign_key = isset($_POST['foreign_key']) ? $_POST['foreign_key'] : array();
    //  $array_tabelle_vincolate = $_POST['tabella_vincolata'];
    //  $array_attributi_vincolati = $_POST['attributo_vincolato'];


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

    $conn = connectToDatabaseMYSQL();
    // Creazione della tabella nel database
    $query_corrente = "CREATE TABLE IF NOT EXISTS $nome_tabella (";



    $query_corrente = attributi(
        $query_corrente,
        $numero_attributi,
        $nome_attributo,
        $tipo_attributo,
    );

    $query_corrente = primary_key($query_corrente, $primary_keys, $nome_attributo);

    $query_corrente = foreign_key($input_data, $query_corrente);

    $query_corrente .= ");";

    echo "$query_corrente";
    $db = null;
} catch (\Throwable $th) {
    echo $th->getMessage();
}

function attributi(
    $query_corrente,
    $numero_attributi,
    $nome_attributo,
    $tipo_attributo,
) {

    // Aggiunge gli attributi dinamici alla query di creazione
    for ($i = 0; $i < $numero_attributi; $i++) {
        $query_corrente .= $nome_attributo[$i] . " ";
        // Se il tipo è VARCHAR, aggiungi la grandezza specificata
        if ($tipo_attributo[$i] == 'VARCHAR') {
            $query_corrente .= $tipo_attributo[$i] . "(100)";
        } else {
            $query_corrente .= $tipo_attributo[$i];
        }
        // Aggiunge virgola se non è l'ultimo attributo
        if ($i < $numero_attributi - 1) {
            $query_corrente .= ", ";
        }
    }

    return $query_corrente;
}


/*
function inserisciInTabellaDelleTabelle($nome_tabella)
{
    $conn = connectToDatabaseMYSQL();
    $stmt = $conn->prepare("INSERT INTO TABELLA_DELLE_TABELLE (nome_tabella) VALUES (:nome_tabella)");
    $stmt->bindParam(':nome_tabella', $nome_tabella);
    // $stmt->execute();
}

function inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella)
{
    $conn = connectToDatabaseMYSQL();
    for ($i = 0; $i < $numero_attributi; $i++) {
        $stmt = $conn->prepare("INSERT INTO TAB_ATT (nome_tabella, nome_attributo, tipo_attributo) VALUES (:nome_tabella, :nome_attributo, :tipo_attributo)");
        $stmt->bindParam(':nome_tabella', $nome_tabella);
        $stmt->bindParam(':nome_attributo', $nome_attributo[$i]);
        $stmt->bindParam(':tipo_attributo', $tipo_attributo[$i]);
        // $stmt->execute();
    }
}*/

function primary_key($query_corrente, $primary_keys, $nome_attributo)
{
    // Aggiungi le chiavi primarie
    if (count($primary_keys) > 0) {
        $query_corrente .= ", PRIMARY KEY (";
        foreach ($primary_keys as $key) {
            $query_corrente .= $nome_attributo[$key] . ", ";
        }
        $query_corrente = rtrim($query_corrente, ", "); // Rimuove l'ultima virgola
        $query_corrente .= ")";
    }

    return $query_corrente . ", ";
}


function foreign_key($input_data, $query_corrente)
{
    $groupby_tabella_rif = array();
    foreach ($input_data['foreign_keys'] as $foreign_key) {
        $tabella_riferimento = $foreign_key['tabella_riferimento'];
        if (!isset($groupby_tabella_rif[$tabella_riferimento])) {
            $groupby_tabella_rif[$tabella_riferimento] = 1;
        } else {
            $groupby_tabella_rif[$tabella_riferimento]++;
        }
    }

    foreach ($groupby_tabella_rif as $key => $count_tab_rif) {

        if ($count_tab_rif > 1) {
            for ($i = 0; $i < $count_tab_rif; $i++) {
                $attributo[$i] = $input_data['foreign_keys'][$i]['attributo'];
                $attributo_riferimento[$i] = $input_data['foreign_keys'][$i]['attributo_riferimento'];
            }
            $query_corrente .=  "FOREIGN KEY (" . implode(", ", $attributo) . ") REFERENCES $key(" . implode(", ", $attributo_riferimento) . ") ON DELETE CASCADE, ";
        } else {
            $attributo = $input_data['foreign_keys'][2]['attributo'];
            $attributo_riferimento = $input_data['foreign_keys'][2]['attributo_riferimento'];
            $query_corrente .= "FOREIGN KEY ($attributo) REFERENCES $key($attributo_riferimento) ON DELETE CASCADE, ";
        }
    }

    // rimuovi l'ultima virgola di $query_foreign_key
    $query_corrente = rtrim($query_corrente, ", ");

    return $query_corrente;
}
