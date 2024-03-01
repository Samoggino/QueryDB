<?php
require_once '../helper/connessione_mysql.php';

function getPDO()
{
    $pdo = connectToDatabaseMYSQL();
    if (!$pdo) {
        throw new Exception("Errore di connessione al database!");
    }
    return $pdo;
}

function registrazione()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];
        $email = $_POST["email"];
        $PASSWORD = $_POST["password"];
        $tipoUtente = $_POST["tipo_utente"];
        $telefono = $_POST["telefono"];

        try {
            $pdo = getPDO();
            if ($tipoUtente == "studente") {
                $annoImmatricolazione = $_POST["anno_immatricolazione"];
                $codiceAlfanumerico = $_POST["codice_alfanumerico"];
                insertNewStudent($pdo, $email, $nome, $cognome, $PASSWORD, $telefono, $annoImmatricolazione, $codiceAlfanumerico);
                echo "<p>Studente registrato con successo!</p>";
            } elseif ($tipoUtente == "professore") {
                $dipartimento = $_POST["dipartimento"];
                $corso = $_POST["corso"];
                insertNewProfessor($pdo, $email, $nome, $cognome, $PASSWORD, $telefono, $dipartimento, $corso);
                echo "<p>Professore registrato con successo!</p>";
            } else {
                throw new Exception("Tipo utente non valido!");
            }
        } catch (PDOException $e) {
            echo "<p>Errore durante l'accesso al database: " . $e->getMessage() . "</p>";
        } catch (Exception $e) {
            echo "<p>Errore durante la registrazione dell'utente: " . $e->getMessage() . "</p>";
        }
    }
}

function insertNewStudent($pdo, $email, $nome, $cognome, $password, $telefono, $annoImmatricolazione, $codiceAlfanumerico)
{
    $sql = "CALL InserisciNuovoStudente(:email, :nome, :cognome, :password, :telefono, :annoImmatricolazione, :codiceAlfanumerico)";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':nome', $nome, PDO::PARAM_STR);
    $query->bindParam(':cognome', $cognome, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
    $query->bindParam(':annoImmatricolazione', $annoImmatricolazione, PDO::PARAM_STR);
    $query->bindParam(':codiceAlfanumerico', $codiceAlfanumerico, PDO::PARAM_STR);
    $query->execute();
}

function insertNewProfessor($pdo, $email, $nome, $cognome, $password, $telefono, $dipartimento, $corso)
{
    $sql = "CALL InserisciNuovoProfessore(:email, :nome, :cognome, :password, :telefono, :dipartimento, :corso)";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':nome', $nome, PDO::PARAM_STR);
    $query->bindParam(':cognome', $cognome, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
    $query->bindParam(':dipartimento', $dipartimento, PDO::PARAM_STR);
    $query->bindParam(':corso', $corso, PDO::PARAM_STR);
    $query->execute();
}
