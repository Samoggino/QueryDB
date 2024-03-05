<?php
require_once '../helper/connessione_mongodb.php';

function creaTabella()
{
    // Recupero dati dal form
    // $nome = $_POST['nome'];
    // $data_creazione = $_POST['data_creazione'];
    // $num_righe = $_POST['num_righe'];
    // $attributi = $_POST['attributi'];
    // $tipi = $_POST['tipi'];
    // $chiavi_primarie = $_POST['chiavi_primarie'];
    // $attributi_vincoli = $_POST['attributi_vincoli'];
    // $tipi_vincoli = $_POST['tipi_vincoli'];

    // // Inserimento della tabella
    // $query = "INSERT INTO tabella_di_esercizio (nome, data_creazione, num_righe) VALUES (?, ?, ?)";
    // $stmt = $conn->prepare($query);
    // $stmt->execute([$nome, $data_creazione, $num_righe]);
    // $tabella_id = $conn->lastInsertId();

    // // Inserimento degli attributi
    // for ($i = 0; $i < count($attributi); $i++) {
    //     $query = "INSERT INTO attributi (nome, tipo, tabella_id_tabella, parte_chiave) VALUES (?, ?, ?, ?)";
    //     $stmt = $conn->prepare($query);
    //     $stmt->execute([$attributi[$i], $tipi[$i], $tabella_id, $chiavi_primarie[$i] ?? 0]);
    // }

    // // Inserimento dei vincoli
    // for ($i = 0; $i < count($attributi_vincoli); $i += 3) {
    //     $query = "INSERT INTO vincoli (attributo1_id_attributo, attributo2_id_attributo, tipo_vincolo) VALUES (?, ?, ?)";
    //     $stmt = $conn->prepare($query);
    //     $stmt->execute([$attributi_vincoli[$i], $attributi_vincoli[$i + 1], $tipi_vincoli[$i / 3]]);
    // }

    // Redirect o visualizzazione di un messaggio di successo
    // header('Location: index.php');
    // exit;

    // chatGPT

    // Query per creare la tabella di esercizio
    $nome_scelto = bin2hex(random_bytes(5));
    $nome_scelto = '25c94d8853';
    echo '' . $nome_scelto . '';
    $sql_create_table = 'CREATE TABLE IF NOT EXISTS ' . $nome_scelto . ' (
        id_tabella INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        data_creazione DATE,
        num_righe INT)';    

    $database = connectToDatabaseMYSQL();

    if ($database->query($sql_create_table)) {
        echo "Tabella di esercizio creata con successo<br>";

        // Query per creare il trigger solo dopo la creazione della tabella
        try {
            $sql_create_trigger =
                'CREATE TRIGGER IF NOT EXISTS update_' . $nome_scelto
                . ' BEFORE INSERT ON `' . $nome_scelto . '`
                FOR EACH ROW 
                BEGIN
                SET NEW.data_creazione = NOW();
                SET NEW.num_righe = (SELECT COUNT(*) FROM `' . $nome_scelto . '`) + 1;
                END';


            $statement = $database->prepare($sql_create_trigger);

            if ($statement->execute()) {
                echo "Trigger creato con successo";
            } else {
                echo "Errore nella creazione del trigger: ";
            }

            // inserisci un elemento per testare il trigger
            $sql_insert = 'INSERT INTO ' . $nome_scelto . ' (nome) VALUES ("prova")';
            $statement = $database->prepare($sql_insert);
            if ($statement->execute()) {
                echo "Elemento inserito con successo";
            } else {
                echo "Errore nell'inserimento dell'elemento: ";
            }
        } catch (\Throwable $th) {
            echo "" . $th->getMessage() . "";
            //throw $th;
        }
    } else {
        echo "Errore nella creazione della tabella di esercizio: ";
    }
}
