<?php

/**
 * Funzione per connettersi al database
 */
function connectToDatabaseMYSQL()
{
    $dsn = 'mysql:host=localhost;dbname=test';
    $username = 'root';
    $password = 'fbyhm3J#pmE%6g2%7d1@';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Gestione degli errori
        echo "Errore di connessione al database: " . $e->getMessage();
        return null;
    }
}
