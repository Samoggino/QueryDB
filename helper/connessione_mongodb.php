<?php
require  '../composer/vendor/autoload.php'; // include Composer's autoloader

function connectToDatabaseMONGODB($document)
{
    // Connessione al server MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");

    // Selezione del database
    $database = $mongoClient->database_log;

    // Selezione della collezione
    $collection = $database->logs;

    // Inserimento del documento nella collezione
    $result = $collection->insertOne($document);

    // Verifica se l'inserimento è avvenuto con successo
    if ($result->getInsertedCount() > 0) {
        echo "Documento inserito con successo.";
    } else {
        echo "Si è verificato un errore durante l'inserimento del documento.";
    }
}
