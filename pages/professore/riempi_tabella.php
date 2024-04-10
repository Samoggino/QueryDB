<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// prende in GET il nome della tabella e la stampa permettendo l'inserimento dei dati

// $nome_tabella = $_GET['nome_tabella'];

if (isset($_GET['nome_tabella'])) {
    $nome_tabella = $_GET['nome_tabella'];
    echo "<script>console.log('NOME TABELLA: " . $nome_tabella . "');</script>";
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("CALL GetAttributiTabella(:nome_tabella)");
        $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
        $stmt->execute();
        $attributi = $stmt->fetchAll();
        $stmt->closeCursor();


        // prendi i valori degli attributi
        $stmt = $db->prepare("SELECT * FROM $nome_tabella");
        $stmt->execute();
        $valori = $stmt->fetchAll();
        $stmt->closeCursor();

        echo "<script>console.log('ATTRIBUTI: " . json_encode($attributi) . "');</script>";
        echo "<script>console.log('VALORI: " . json_encode($valori) . "');</script>";

        if ($valori == null) {
            echo "<br>VALORI NULLI";
        }
    } catch (\Throwable $th) {
        echo "PROBLEM RIEMPIMENTO <br>" . $th->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Inserisci valori</title>
    <link rel="icon" href="../../images/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../styles/table.css">
</head>

<body>

    <h1>Inserisci i valori all'interno della tabella</h1>
    <form id="insert_values" method='post' action='/pages/professore/riempi_tabella_handler.php?nome_tabella=<?php echo $nome_tabella; ?>'>
        <table>
            <thead>
                <tr>
                    <?php foreach ($attributi as $attributo) { ?>
                        <th style="color:<?php if ($attributo['is_key'] == 1) {
                                                echo "red";
                                            } else {
                                                echo "black";
                                            }; ?>">
                            <?php echo $attributo['nome_attributo']; ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($valori as $valore) { ?>
                    <tr>
                        <?php foreach ($attributi as $attributo) { ?>
                            <td><?php echo $valore[$attributo['nome_attributo']]; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>

            <tbody>
                <tr>
                    <?php foreach ($attributi as $attributo) { ?>
                        <td><input type="text" name="<?php echo $attributo['nome_attributo']; ?>"></td>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
        <input type='submit' value='Aggiungi riga'>

</body>

</html>