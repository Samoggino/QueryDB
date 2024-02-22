<?php
require_once '../helper/connessione_mysql.php';

function registrazione()
{
    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];
        $email = $_POST["email"];
        $password = $_POST["password"];


        // Connect to the database
        $pdo = connectToDatabaseMYSQL();
        if ($pdo) {
            // Prepare the SQL statement
            $sql = "INSERT INTO utenti (nome, cognome, email, password) VALUES (:nome, :cognome, :email, :password)";
            $query = $pdo->prepare($sql);
            // Execute the SQL statement
            $query->execute(['nome' => $nome, 'cognome' => $cognome, 'email' => $email, 'password' => $password]);
            // Redirect to success page
            // header("Location: ../landing.html");
            // exit;

            echo "<p>Utente registrato con successo!</p>";
            echo "<p>Benvenuto, $nome $cognome!</p>";
        } else {
            echo "<p>Errore di connessione al database!</p>";
        }

        // // Redirect to success page
        // header("Location: index.php");
        // exit;
    }
}
