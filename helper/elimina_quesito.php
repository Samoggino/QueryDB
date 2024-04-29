<?php
require_once 'connessione_mysql.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_quesito'])) {
    $titolo_test = $_POST['id_quesito'];

    $db = connectToDatabaseMYSQL();
    $sql = "CALL EliminaQuesito(:id_quesito)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_quesito', $titolo_test);
    $stmt->execute();
    $stmt->closeCursor();

    // Verifica se l'eliminazione è avvenuta con successo
    $eliminato = $stmt->rowCount() > 0;

    if ($eliminato) {
        // Se l'eliminazione è avvenuta con successo, reindirizza l'utente alla pagina precedente o ad una specifica pagina
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    } else {
        // Se c'è stato un errore nell'eliminazione, mostra un messaggio di errore
        echo "Errore nell'eliminazione del quesito.";
    }
} else {
    // Se la richiesta non è una POST o manca il parametro id_quesito, restituisci un errore

}
