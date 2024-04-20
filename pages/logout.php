<?php

// Inizia la sessione
session_start();

// Elimina tutte le variabili di sessione
$_SESSION = array();

// Cancella la sessione
session_destroy();

// Reindirizza l'utente alla pagina di login
header("Location: /pages/login.php");
exit();
