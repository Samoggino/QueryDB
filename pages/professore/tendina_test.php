<?php
require_once "../../helper/connessione_mysql.php";
session_start();

if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}

function tendinaTest()
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetTestDelProfessore(:email_professore);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email_professore', $_SESSION['email']);
    try {
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<option hidden value=0>Seleziona un test</option>";
        foreach ($tests as $test) {
            if ($test['VisualizzaRisposte'] == 0 && $_SESSION['completati'] == 1) {
                echo "<option value='" . $test['titolo'] . "'required>" . $test['titolo'] . "</option>";
            } else if (isset($_SESSION['completati']) == false) {
                echo "<option value='" . $test['titolo'] . "'required>" . $test['titolo'] . "</option>";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>console.log('Errore: " . $th . "');</script>";
    }
    $stmt->closeCursor();
    $db = null;
}
