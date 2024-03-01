<?php
require '../helper/connessione_mongodb.php';
function login()
{
    global $database, $email, $PASSWORD, $utente;

    try {
        echo "<p>login() function called!</p>";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $PASSWORD = $_POST["password"];

            $database = connectToDatabaseMYSQL();
            if ($database) {
                // Utilizzo della stored procedure
                $query = "CALL authenticateUser('$email', '$PASSWORD')";
                echo "<p>Query: $query</p>";
                $risultato_della_query = $database->query($query);
                $utente = $risultato_della_query->fetch();

                echo "<p>Utente: " . $utente['nome'] . " " . $utente['cognome'] . "</p>";

                // Close the cursor for the previous query
                $risultato_della_query->closeCursor();

                // inserisci su mongo il record del login
                $query = "CALL VerificaTipoUtente('$email')";

                if ($result = getUtenteType($database, $email)) { // Use a function to get the user type
                    if ($result['Ruolo'] == 'Studente') {
                        echo "<p>Sei uno studente!</p>";
                        connectToDatabaseMONGODB([
                            'ruolo' => 'studente',
                            'email' => $email
                        ]);
                    } elseif ($result['Ruolo']  == 'Professore') {
                        echo "<p>Sei un professore!</p>";
                        connectToDatabaseMONGODB([
                            'ruolo' => 'professore',
                            'email' => $email
                        ]);
                    } else {
                        echo "<p>Errore nel recupero del tipo utente!</p>";
                    }

                    if (count($result) == 0) {
                        echo "<p>Utente non trovato!</p>";
                    }
                } else {
                    echo "<p>Errore di connessione al database!</p>";
                }
            }
        }
    } catch (\Throwable $th) {
        //  throw $th;
        echo "<p>Errore: " . $th->getMessage() . "</p>";
    }
}



function getUtenteType($database, $email)
{
    $query = "CALL VerificaTipoUtente('$email')";
    $risultato_della_query = $database->query($query);
    $righe_del_database = $risultato_della_query->fetch();

    echo "<p>Eseguo getUtenteType</p>";

    return $righe_del_database;
}
