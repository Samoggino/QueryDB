<?php
session_start();
require '../helper/connessione_mongodb.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
function login()
{
    global $db, $email, $PASSWORD, $utente;

    try {


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $PASSWORD = $_POST["password"];

            $db = connectToDatabaseMYSQL();

            // Utilizzo della stored procedure
            $sql = "CALL authenticateUser(:email, :psswd)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':psswd', $PASSWORD, PDO::PARAM_STR);
            $stmt->execute();
            $utente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (count($utente) > 0) {

                foreach ($utente as $key => $value) {
                    echo "<script>console.log('Chiave: " . $key . " Valore: " . $value . "');</script>";
                }

                $_SESSION['email'] = $email;
                $_SESSION['nome'] = $utente['nome'];
                $_SESSION['cognome'] = $utente['cognome'];
                
                // Close the cursor for the previous query
                $stmt->closeCursor();

                if ($result = getUtenteType($db, $email)) { // Use a function to get the user type

                    if ($result['RUOLO'] == 'STUDENTE') {
                        connectToDatabaseMONGODB([
                            'ruolo' => 'STUDENTE',
                            'email' => $email
                        ]);

                        $_SESSION['matricola'] = $utente['matricola'];
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
