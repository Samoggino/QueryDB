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

    <!-- Aggiunta del bottone per il reindirizzamento -->
    <a href="pages/login.php"><button>Accedi</button></a>

    <!-- sudo php --server localhost:8080 --docroot /home/samoggino/VSC/basi/ -->
    <?php
    // require './helper/connessione_mongodb.php';
    // connectToDatabaseMONGODB();

    header("Location: pages/login.php");
    ?>


</body>

</html>
