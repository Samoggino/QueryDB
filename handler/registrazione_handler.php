<?php
require_once '../helper/connessione_mysql.php';

function getPDO() {
    $pdo = connectToDatabaseMYSQL();
    if (!$pdo) {
        throw new Exception("Errore di connessione al database!");
    }
    return $pdo;
}

function insertNewStudent($pdo, $email, $nome, $cognome, $password, $telefono, $annoImmatricolazione) {
    $sql = "CALL InserisciNuovoStudente(:email, :nome, :cognome, :password, :telefono, :annoImmatricolazione)";
    $query = $pdo->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':nome', $nome, PDO::PARAM_STR);
    $query->bindParam(':cognome', $cognome, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);
    $query->bindParam(':annoImmatricolazione', $annoImmatricolazione, PDO::PARAM_STR);
    $query->execute();
    echo "<p>Studente registrato con successo!</p>";
}

function insertNewProfessor($pdo, $email, $nome, $cognome, $password, $telefono, $dipartimento, $corso) {
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
    echo "<p>Professore registrato con successo!</p>";
}

function registrazione() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $tipoUtente = $_POST["tipo_utente"];
        $telefono = $_POST["telefono"];
        $annoImmatricolazione = $_POST["anno_immatricolazione"];
        $dipartimento = $_POST["dipartimento"];
        $corso = $_POST["corso"];

        try {
            $pdo = getPDO();
            if ($tipoUtente == "studente") {
                insertNewStudent($pdo, $email, $nome, $cognome, $password, $telefono, $annoImmatricolazione);
            } elseif ($tipoUtente == "professore") {
                insertNewProfessor($pdo, $email, $nome, $cognome, $password, $telefono, $dipartimento, $corso);
            } else {
                throw new Exception("Tipo utente non valido!");
            }
        } catch (Exception $e) {
            echo "<p>Errore durante la registrazione dell'utente: " . $e->getMessage() . "</p>";
        }
    }
}
?>
