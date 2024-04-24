<?php
session_start();
require_once '../helper/connessione_mysql.php';
require_once '../helper/connessione_mongodb.php';
require_once 'tabella_logica.php';
require_once 'tabella_fisica.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


// se non è stato un post non fare nulla

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    throw new Exception("Richiesta non valida");
} else {

    try {

        $db = connectToDatabaseMYSQL();
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

            $_POST['foreign_key'] = $foreign_keys;
        }

        $query_corrente = crea_tabella_logica($nome_tabella, $numero_attributi, $nome_attributo, $tipo_attributo, $primary_keys, $foreign_keys);

        // echo "<script>console.log('" . $query_corrente . "')</script>";


        // Inserimento della tabella nel database delle tabelle create
        crea_tabella_fisica($numero_attributi, $nome_attributo, $tipo_attributo, $nome_tabella, $foreign_keys, $primary_keys);

        $query_corrente = inserisciTriggerNumeroRighe($query_corrente, $nome_tabella);

        $stmt = $db->prepare($query_corrente);

        $documento = (
            array(
                'creazione_tabella' => 'creazione_tabella',
                'nome_tabella' => $nome_tabella,
                // 'errore' => $th->getMessage(),
                'data' => date('Y-m-d'),
                'query' => $query_corrente
            )
        );

        connectToDatabaseMONGODB($documento);
        if ($stmt->execute()) {
            $db = null;
            echo "<script>alert('Tabella creata con successo, riempila'); window.location.replace('/pages/professore/riempi_tabella.php?nome_tabella=$nome_tabella&factory=true')</script>";
        } else {
            echo "<script>alert('Errore nella creazione della tabella')</script>";
        }
    } catch (\Throwable $th) {
        // eliminare la tabella logica
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("CALL EliminaTabella(:nome_tabella)");
        $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        echo "<script>console.log('" . $nome_tabella . " eliminata a causa di un errore nella creazione fisica')</script>";
        echo "<script>console.log('" . json_encode($_POST) . "')</script>";
        echo "<script>console.log('" . $query_corrente . "')</script>";
        echo "<script>alert('Errore nella creazione della tabella'); window.location.replace('/pages/professore/crea_tabella.php')</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Document</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/global.css">
</head>

<body>

</body>

</html>