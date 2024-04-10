<?php
session_start();
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


// se non è stato un post non fare nulla

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    throw new Exception("Richiesta non valida");
} else {

    try {

        $db = connectToDatabaseMYSQL();
        // TODO: ha il problema dell'ordine della chiave della tabella di riferimento
        // un modo per risolvere è quello di creare una tabella delle chiavi.

        $input_data = array(
            'nome_tabella' => 'PRASSI',
            'numero_attributi' => 5,
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
                ),
                array(
                    'nome' => 'tab1',
                    'tipo' => 'INT'
                ),
            ),
            'primary_keys' => array(1, 2),
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
                ),
                array(
                    'attributo' => 'tab1',
                    'tabella_riferimento' => 'Tabella1',
                    'attributo_riferimento' => 'id'
                ),
            )
        );



        //  Recupera i dati inviati dal form
        //  Rimuovi il recupero dei dati dalle variabili POST
        $nome_tabella = $_POST['nome_tabella'];
        $nome_attributo = $_POST['nome_attributo'];
        $tipo_attributo = $_POST['tipo_attributo'];
        $primary_keys = isset($_POST['primary_key']) ? $_POST['primary_key'] : array();
        // Itera attraverso ogni elemento dell'array
        foreach ($primary_keys as $key => $value) {
            // Converti il valore corrente da stringa a numero intero
            $primary_keys[$key] = intval($value);
        }
        $foreign_keys = isset($_POST['foreign_key']) ? $_POST['foreign_key'] : array();
        // $array_tabelle_vincolate = $_POST['tabella_vincolata'];
        // $array_attributi_vincolati = $_POST['attributo_vincolato'];

        // $input_data = $_POST;
        // $_POST = array();

        $numero_attributi = count($nome_attributo) == count($tipo_attributo) ? count($nome_attributo) : 0;



        // Verifica se sono state inviate foreign keys
        if (isset($_POST['foreign_key'])) {
            $foreign_keys = array();

            // Ciclo per ogni foreign key selezionata
            foreach ($_POST['foreign_key'] as $index) {
                // Assicurati che siano stati inviati anche gli altri campi
                if (isset($_POST['nome_attributo'][$index]) && isset($_POST['tabella_vincolata'][$index]) && isset($_POST['attributo_vincolato'][$index])) {
                    // Crea un array per rappresentare la foreign key e aggiungilo alla lista
                    $foreign_key = array(
                        'attributo' => $_POST['nome_attributo'][$index],
                        'tabella_riferimento' => $_POST['tabella_vincolata'][$index],
                        'attributo_riferimento' => $_POST['attributo_vincolato'][$index]
                    );
                    $foreign_keys[] = $foreign_key;
                }
            }

            // Ora $foreign_keys contiene tutte le foreign keys selezionate dall'utente
            // Puoi utilizzarle come desideri, ad esempio salvandole nel database o utilizzandole per generare output
            // echo "<script>console.log('Ciao');</script>";
            // echo "<script>console.log(" . json_encode($foreign_keys) . ");</script>";
            $_POST['foreign_key'] = $foreign_keys;
        }



        // echo "<script>console.log(" . json_encode($_POST) . ");</script>";
        // echo "<script>console.log(" . json_encode($input_data) . ");</script>";




        // Utilizza i dati forniti nell'array associativo
        // $nome_tabella = $input_data['nome_tabella'];
        // $attributi = $input_data['attributi'];
        // $primary_keys = $input_data['primary_keys'];
        // $foreign_keys = $input_data['foreign_keys'];

        // // Estrai i dati dagli attributi nell'array associativo
        // $nome_attributo = array_column($attributi, 'nome');
        // $tipo_attributo = array_column($attributi, 'tipo');

        // // Utilizza invece i dati forniti nelle chiavi esterne dell'array associativo
        // $array_tabelle_vincolate = array_column($foreign_keys, 'tabella_riferimento');
        // $array_attributi_vincolati = array_column($foreign_keys, 'attributo_riferimento');

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

        if (isset($foreign_keys) && count($foreign_keys) > 0) {
            $query_corrente = foreign_key($foreign_keys, $query_corrente);
        }

        $query_corrente .= ");";

        echo "<script>console.log('" . $query_corrente . "')</script>";


        // Inserimento della tabella nel database delle tabelle create
        inserisciInTabellaDelleTabelle($nome_tabella);
        inserisciInAttributi($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella);
        inserisciForeignKey($nome_tabella, $foreign_keys);
        inserisciPrimaryKey($nome_tabella, $primary_keys, $nome_attributo);


        $stmt = $db->prepare($query_corrente);
        $stmt->execute();
        $db = null;

        header("Location: /pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella");
        exit();
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }
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
                $query_corrente .= $tipo_attributo[$i] . "(20)";
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

function inserisciForeignKey($nome_tabella, $foreign_keys)
{
    echo "<script>console.log(" . json_encode($foreign_keys) . ");</script>";
    try {
        $db = connectToDatabaseMYSQL();
        foreach ($foreign_keys as $key) {
            $stmt = $db->prepare("CALL InserisciChiaveEsterna(:nome_tabella, :nome_attributo, :tabella_riferimento, :attributo_riferimento)");
            $stmt->bindParam(':nome_tabella', $nome_tabella);
            $stmt->bindParam(':nome_attributo', $key['attributo']);
            $stmt->bindParam(':tabella_riferimento', $key['tabella_riferimento']);
            $stmt->bindParam(':attributo_riferimento', $key['attributo_riferimento']);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "FOREIGN INSERT PROBLEM <br>" . $th->getMessage();
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

function primary_key($query_corrente, $primary_keys, $nome_attributo)
{
    try {
        $db = connectToDatabaseMYSQL();
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


function foreign_key($foreign_keys_raw, $query_corrente)
{

    try {
        // prendi gli indici delle chiavi esterne

        echo "<script>console.log(" . json_encode($foreign_keys_raw) . ");</script>";

        $json = convertToJSONFormat($foreign_keys_raw); // Converto l'array in formato JSON per facilitare la manipolazione
        $decodedJson = json_decode($json, true);

        // echo "<script>console.log(" . json_encode($decodedJson) . ");</script>";

        // Esempio di utilizzo
        if ($decodedJson['foreign_keys'] > 0)
            foreach ($decodedJson['foreign_keys'] as $tableName => $attributes) {

                $db = connectToDatabaseMYSQL();
                $sql = "CALL GetPrimaryKey(:tableName)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':tableName', $tableName);
                $stmt->execute();
                $attributi_ordinati = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = null;

                $attributes = verificaOrdine($attributi_ordinati, $attributes);

                $query_corrente .= ", FOREIGN KEY ("
                    . implode(", ", array_column($attributes, 'attributo')) . ") REFERENCES $tableName("
                    . implode(", ", array_column($attributes, 'attributo_riferimento')) . ") ON DELETE CASCADE ";
            }
    } catch (\Throwable $th) {
        echo "FOREIGN KEY PROBLEM <br>" . $th->getMessage();
    }
    return $query_corrente;
}

function convertToJSONFormat($input)
{
    try {
        $output = array();

        echo "<script>console.log(" . json_encode($input) . ");</script>";

        foreach ($input as $key => $value) {
            $tableName = $value['tabella_riferimento'];

            if (!isset($output['foreign_keys'][$tableName])) {
                $output['foreign_keys'][$tableName] = array();
            }

            $output['foreign_keys'][$tableName][] = array(
                'attributo' => $value['attributo'],
                'attributo_riferimento' => $value['attributo_riferimento']
            );
        }
    } catch (\Throwable $th) {
        echo "JSON PROBLEM <br>" . $th->getMessage();
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
            throw new Exception("Non sono presenti tutti gli elementi necessari per fare la chiave esterna.");
        }
    }

    return $attributes;
}

function inserisciPrimaryKey($nome_tabella, $primary_keys, $nome_attributo)
{
    try {
        $db = connectToDatabaseMYSQL();
        foreach ($primary_keys as $key) {
            $sql = "CALL AggiungiChiavePrimaria(:nome_tabella, :nome_attributo);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nome_tabella', $_POST['nome_tabella']);
            $stmt->bindParam(':nome_attributo', $nome_attributo[$key]);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $db = null;
    } catch (\Throwable $th) {
        echo "PRIMARY KEY INSERT PROBLEM <br>" . $th->getMessage();
    }
}
