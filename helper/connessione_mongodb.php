<?php
require 'composer/vendor/autoload.php'; // include Composer's autoloader

// Connessione al server MongoDB
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

// Selezione del database
$database = $mongoClient->prova_database;

// Selezione della collezione
$collection = $database->paolo;

// Documento da inserire
$document = [
    'campo1' => 'valore1',
    'campo2' => 'valore2',
    'campo3' => 'valore3'
];

// Inserimento del documento nella collezione
$result = $collection->insertOne($document);

// Verifica se l'inserimento è avvenuto con successo
if ($result->getInsertedCount() > 0) {
    echo "Documento inserito con successo.";
} else {
    echo "Si è verificato un errore durante l'inserimento del documento.";
}
