<?php
function creaTabella($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS UTENTI (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        cognome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE
    )";

    try {
        $pdo->exec($sql);
        // Ottieni informazioni sulla struttura della tabella appena creata
    } catch (PDOException $e) {
        echo "Errore nella creazione della tabella: " . $e->getMessage();
    }
}
