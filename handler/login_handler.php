<?php
require_once '../helper/connessione_mysql.php';

function login()
{
    echo "<p>login() function called!</p>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $pdo = connectToDatabaseMYSQL();
        if ($pdo) {
            $sql = "SELECT * FROM utenti WHERE email = :email AND password = :password";
            $query = $pdo->prepare($sql);
            $query->execute(['email' => $email, 'password' => $password]);
            $righe_del_database = $query->fetchAll();

            foreach ($righe_del_database as $singola_riga) {
                if ($singola_riga) {
                    echo "<p>Ciao, " . $singola_riga['nome'] . " " . $singola_riga['cognome'] . "!</p>";
                    // header("Location: ../landing.html");
                    // exit;
                } else {
                    echo "<p>Utente non trovato!</p>";
                }
            }

            if (count($righe_del_database) == 0) {
                echo "<p>Utente non trovato!</p>";
            }
        } else {
            echo "<p>Errore di connessione al database!</p>";
        }
    }
}
