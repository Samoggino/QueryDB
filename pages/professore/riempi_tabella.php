<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}



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
    <link rel="stylesheet" href="../../styles/global.css">
    <style>
        .tabelle {
            display: flex;
            gap: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Inserisci i valori all'interno della tabella</h1>
        <div class="tabelle">
            <div>
                <form id="insert_values" method='post' action='/pages/professore/riempi_tabella_handler.php?nome_tabella=<?php echo $nome_tabella; ?>'>

                    <?php
                    require_once '../../helper/print_table.php';
                    generateTable($nome_tabella);
                    ?>
                    <tbody>
                        <tr>
                            <?php foreach ($attributi as $attributo) { ?>
                                <td><input type="text" name="<?php echo $attributo['nome_attributo']; ?>" required></td>
                            <?php } ?>
                        </tr>
                    </tbody>
                    </table>
                    <input type='submit' value='Aggiungi riga'>
                </form>


                <!-- mostra anche le tabelle a cui la tabella in get fa reference se ne ha-->
                <?php
                try {
                    $db = connectToDatabaseMYSQL();
                    $stmt = $db->prepare("CALL GetChiaviEsterne(:nome_tabella)");
                    $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
                    $stmt->execute();
                    $tabelle_riferite = $stmt->fetchAll();
                    $stmt->closeCursor();
                } catch (\Throwable $th) {
                    echo "PROBLEM TABELLE RIFERITE <br>" . $th->getMessage();
                }

                if ($tabelle_riferite != null) {
                ?>

            </div>
            <div class="tabella-esterna">
                <h2>Righe tabella vincolata</h2>
                <?php
                    require_once '../../helper/print_table.php';
                    generateTable($tabelle_riferite[0]['tabella_vincolata']);
                    echo "</table>";
                ?>
            </div>
        </div>


        <div id="vincoli">
            <h2>Vincoli di integrità</h2>
            <table>
                <tr>
                    <th>Tabella padre</th>
                    <th>Attributo in <?php echo $nome_tabella ?></th>
                    <th>Reference </th>
                </tr>
                <tbody>
                    <?php foreach ($tabelle_riferite as $tabella) { ?>
                        <tr>
                            <td><?php echo $tabella['tabella_vincolata']; ?></td>
                            <td><?php echo $tabella['nome_attributo'] . " -> "; ?></td>
                            <td><?php echo $tabella['attributo_vincolato']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } ?>
    </div>

</body>

</html>