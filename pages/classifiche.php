<?php
require '../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = connectToDatabaseMYSQL();
$sql = "CALL GetClassificaRisposteGiuste();";

$stmt = $db->prepare($sql);
$stmt->execute();
$classifica = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classifiche</title>
</head>

<body>  
    
</body>

</html>