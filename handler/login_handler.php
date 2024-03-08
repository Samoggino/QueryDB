<?php
require '../helper/connessione_mongodb.php';
function login()
{
    global $database, $email, $PASSWORD, $utente;
    session_start();


    try {
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

                if ($utente) {
                    $_SESSION['email'] = $email;
                } else {
                    // lancia un'eccezione
                    throw new Exception("Utente non trovato!");
                }

                echo "<p>Utente: " . $utente['nome'] . " " . $utente['cognome'] . "</p>";

                // Close the cursor for the previous query
                $risultato_della_query->closeCursor();

                // inserisci su mongo il record del login
                $query = "CALL VerificaTipoUtente('$email')";

                if ($result = getUtenteType($database, $email)) { // Use a function to get the user type
                    if ($result['Ruolo'] == 'STUDENTE') {
                        echo "<p>Sei uno studente!</p>";
                        connectToDatabaseMONGODB([
                            'ruolo' => 'STUDENTE',
                            'email' => $email
                        ]);

                        $_SESSION['ruolo'] = 'STUDENTE';
                    } elseif ($result['Ruolo']  == 'PROFESSORE') {
                        echo "<p>Sei un professore!</p>";
                        connectToDatabaseMONGODB([
                            'ruolo' => 'PROFESSORE',
                            'email' => $email
                        ]);

                        $_SESSION['ruolo'] = 'PROFESSORE';
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

            // stampa la session
            foreach ($_SESSION as $key => $value) {
                echo "<p>Session: $key => $value</p>";
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
