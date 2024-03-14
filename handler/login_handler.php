<?php
session_start();
require '../helper/connessione_mongodb.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
function login()
{
    global $database, $email, $PASSWORD, $user_exists;

    try {


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $PASSWORD = $_POST["password"];

            $database = connectToDatabaseMYSQL();

            if ($database) {
                // Utilizzo della stored procedure
                $query = "CALL authenticateUser('$email', '$PASSWORD')";
                $risultato_della_query = $database->query($query);
                $user_exists = $risultato_della_query->fetch();

                if ($user_exists['user_exists'] == 1) {
                    $_SESSION['email'] = $email;

                    // Close the cursor for the previous query
                    $risultato_della_query->closeCursor();

                    if ($result = getUtenteType($database, $email)) { // Use a function to get the user type
                        if ($result['RUOLO'] == 'STUDENTE') {
                            connectToDatabaseMONGODB([
                                'ruolo' => 'STUDENTE',
                                'email' => $email
                            ]);

                            $_SESSION['ruolo'] = 'STUDENTE';

                            return true;
                        } elseif ($result['RUOLO']  == 'PROFESSORE') {
                            connectToDatabaseMONGODB([
                                'ruolo' => 'PROFESSORE',
                                'email' => $email
                            ]);

                            $_SESSION['ruolo'] = 'PROFESSORE';
                            return true;
                        } else {
                            throw new Exception("Ruolo non riconosciuto!");
                        }

                        if (count($result) == 0) {
                            throw new Exception("Nessun risultato trovato!");
                        }
                    } else {
                        throw new Exception("Errore nella query!");
                    }
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

    return $righe_del_database;
}
