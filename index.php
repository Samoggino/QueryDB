<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/global.css">
    <link rel="icon" href="images/favicon/favicon.ico" type="image">
    <title>Progetto</title>
</head>

<body>
    <h1>Benvenuto sulla mia pagina PHP!</h1>
    <p>Questa è una pagina PHP di esempio.</p>

    <!-- sudo php --server localhost:8080 --docroot /home/samoggino/VSC/basi/ -->

    <!-- Modulo per l'inserimento del nome e cognome -->
    <form method="post" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome">
        <br>
        <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome">
        <br>
        <input type="submit" value="Invia">
    </form>

    <?php
    // Controlla se sono stati inviati i valori tramite il modulo
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Leggi i valori inseriti dall'utente
        $nome = $_POST["nome"];
        $cognome = $_POST["cognome"];

        // Stampare il saluto con i valori inseriti dall'utente
        echo "<p>Ciao, $nome $cognome!</p>";
    }
    ?>

    <?php // provo la connessione al database
    require_once 'helper/connessione_mysql.php';
    require_once 'helper/connessione_mongodb.php';
    require_once 'helper/crea_tabella.php';

    
    $pdo = connectToDatabase();

    // Creazione di una tabella nel database
    creaTabella($pdo);

    if ($pdo) {
        echo "<p>Connessione al database avvenuta con successo!</p>";
    } else {
        echo "<p>Errore di connessione al database!</p>";
    }
    ?>

    


</body>

</html>