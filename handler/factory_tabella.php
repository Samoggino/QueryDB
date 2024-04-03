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
        'numero_attributi' => 4,
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
                'nome' => 'eta_prassi',
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
                'attributo' => 'eta_prassi',
                'tabella_riferimento' => 'tabella_di_esempio',
                'attributo_riferimento' => 'eta'
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

    $db = connectToDatabaseMYSQL();
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

    echo "<script>console.log('" . $query_corrente . "')</script>";

    $stmt = $db->prepare($query_corrente);
    // $stmt->execute();

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

    try {
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
    } catch (\Throwable $th) {
        echo "ATTRIBUTES PROBLEM <br>" . $th->getMessage();
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
    try {
        // Aggiungi le chiavi primarie
        if (count($primary_keys) > 0) {
            $query_corrente .= ", PRIMARY KEY (";
            foreach ($primary_keys as $key) {
                $query_corrente .= $nome_attributo[$key] . ", ";
            }
            $query_corrente = rtrim($query_corrente, ", "); // Rimuove l'ultima virgola
            $query_corrente .= ")";
        }
    } catch (\Throwable $th) {
        echo "PRIMARY KEY PROBLEM <br>" . $th->getMessage();
    }

    return $query_corrente;
}


function foreign_key($input_data, $query_corrente)
{

    try {
        // prendi gli indici delle chiavi esterne


        $json = convertToJSONFormat($input_data); // Converto l'array in formato JSON per facilitare la manipolazione
        $decodedJson = json_decode($json, true);

        // Esempio di utilizzo
        foreach ($decodedJson['foreign_keys'] as $tableName => $attributes) {

            $db = connectToDatabaseMYSQL();
            $sql = "CALL GetPrimaryKey(:tableName)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':tableName', $tableName);
            $stmt->execute();
            $attributi_ordinati = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;

            $attributes = verificaOrdine($attributi_ordinati, $attributes);

            $query_corrente .= ", FOREIGN KEY (" . implode(", ", array_column($attributes, 'attributo')) . ") REFERENCES $tableName(" . implode(", ", array_column($attributes, 'attributo_riferimento')) . ") ON DELETE CASCADE ";
        }

        // Rimuovi l'ultima virgola
        // $query_corrente = rtrim($query_corrente, ", ");
    } catch (\Throwable $th) {
        echo "FOREIGN KEY PROBLEM <br>" . $th->getMessage();
    }
    return $query_corrente;
}

function convertToJSONFormat($input)
{
    $output = array();

    foreach ($input['foreign_keys'] as $key => $value) {
        $tableName = $value['tabella_riferimento'];

        if (!isset($output['foreign_keys'][$tableName])) {
            $output['foreign_keys'][$tableName] = array();
        }

        $output['foreign_keys'][$tableName][] = array(
            'attributo' => $value['attributo'],
            'attributo_riferimento' => $value['attributo_riferimento']
        );
    }

    return json_encode($output, JSON_PRETTY_PRINT);
}

function verificaOrdine($attributi_ordinati, $attributes)
{

    // verifica che l'attributo sia chiave della tabella di riferimento, se non lo è rimuovilo da attributes 
    foreach ($attributes as $key => $value) {
        if (!in_array($value['attributo_riferimento'], array_column($attributi_ordinati, 'NOME_ATTRIBUTO'))) {
            unset($attributes[$key]);
        }
    }


    // Creare una matrice associativa con il nome dell'attributo come chiave e l'indice come valore
    $indiceAttributiOrdinati = array();
    foreach ($attributi_ordinati as $attributo) {
        $indiceAttributiOrdinati[$attributo['NOME_ATTRIBUTO']] = $attributo['INDICE'];
    }

    // Ordinare gli attributi locali in base all'indice ottenuto dalla matrice associativa
    usort($attributes, function ($a, $b) use ($indiceAttributiOrdinati) {
        $indiceA = isset($indiceAttributiOrdinati[$a['attributo_riferimento']]) ? $indiceAttributiOrdinati[$a['attributo_riferimento']] : PHP_INT_MAX;
        $indiceB = isset($indiceAttributiOrdinati[$b['attributo_riferimento']]) ? $indiceAttributiOrdinati[$b['attributo_riferimento']] : PHP_INT_MAX;
        return $indiceA - $indiceB;
    });

    // controllo l'ordine degli attributi, se manca un elemento della chiave esterna allora lancia un'eccezione
    for ($i = 0; $i < count($attributes); $i++) {
        echo  $attributes[$i]['attributo_riferimento'] . " = " . $attributi_ordinati[$i]['NOME_ATTRIBUTO'] . "?<br>";
        if ($attributes[$i]['attributo_riferimento'] != $attributi_ordinati[$i]['NOME_ATTRIBUTO']) {
            // echo "<script>console.log(" . json_encode($attributes) . ");</script>";
            throw new Exception("Non sono presenti tutti gli elementi necessari per fare la chiave esterna.");
        }
    }

    // // Aggiungi gli attributi rimossi in precedenza
    // foreach ($temp as $value => $key) {
    //     $attributes[] = $key;
    // }


    return $attributes;
}
