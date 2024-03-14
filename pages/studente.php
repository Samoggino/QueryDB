<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>Visualizza tutti i test che può sostenere lo studente</h1>
    <?php
    echo "<h1>La tua email è : " . $_SESSION['email'] . "</h1>";
    ?>

</body>

</html>