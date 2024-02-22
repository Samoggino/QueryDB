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
    <?php
    header("Location: pages/login.php");
    ?>


</body>

</html>