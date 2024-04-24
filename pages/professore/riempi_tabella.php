<?php
session_start();
require_once '../../helper/connessione_mysql.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// TODO: gestire il caso in cui non ci siano valori nella tabella di riferimento

if ($_SESSION['ruolo'] != 'PROFESSORE') {
    echo "<script>alert('Non hai i permessi per accedere a questa pagina!'); window.location.replace('/pages/login.php')</script>";
}
if (isset($_POST)) {
    echo "<script>console.log('POST: " . json_encode($_POST) . "');</script>";
    unset($_POST);
}
if (isset($_GET['nome_tabella'])) {
    $nome_tabella = $_GET['nome_tabella'];
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

        try {
            $db = connectToDatabaseMYSQL();
            $stmt = $db->prepare("CALL GetChiaviEsterne(:nome_tabella)");
            $stmt->bindParam(':nome_tabella', $nome_tabella, PDO::PARAM_STR);
            $stmt->execute();
            $tabelle_riferite = $stmt->fetchAll();
            $stmt->closeCursor();
        } catch (\Throwable $th) {
            echo "<script>alert('PROBLEM VINCOLI <br>" . $th->getMessage() . ")</script>";
        }


        if ($valori == null) {
            if (!isset($_GET['factory'])) {
                echo "<script>alert('La tabella è vuota, inserisci dei valori!');</script>";
            }
        }
    } catch (\Throwable $th) {
        echo "<script>alert('PROBLEM <br>" . $th->getMessage() . ")</script>";
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
        <?php
        if ($tabelle_riferite != null) {

        ?>.tabelle {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(calc(33.33% - 20px), 1fr));
            grid-gap: 50px;
            justify-content: center;
        }

        <?php
        } else {

        ?>.tabelle {
            position: relative;
            justify-content: center;
            max-width: 50%;
            left: 25%;
        }

        <?php } ?>

        #intestazione {
            margin-bottom: 20px;
            gap: 35dvw;
            margin-left: 3dvw;
        }

        input {
            width: auto;
            height: auto;
            border-radius: 0;
            font-size: 15px;
            font-weight: 600;
            padding: 0;
            background-color: lightgray;
        }


        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .vincoli.widget-classifica.on {
            /* grid-column: span 3; */
            width: 50%;
            margin-bottom: 50px;
        }

        .vincoli.widget-classifica.off {
            display: none;
        }


        .mostra-vincoli {
            position: relative;
            left: 90%;
            bottom: 145%;
        }
    </style>
</head>

<body>
    <div id="riempi">

        <div id="intestazione">
            <div class="icons-container">
                <a class="logout" href="/pages/logout.php"></a>
                <a class="home" href="/pages/<?php echo strtolower($_SESSION['ruolo']) . '/' . strtolower($_SESSION['ruolo']) . ".php" ?>"></a>
            </div>
            <h1>Inserisci valori</h1>

        </div>

        <?php
        if ($tabelle_riferite != null) {
            echo ' <button class="mostra-vincoli" onclick="mostraVincoli()">Mostra vincoli</button>';
        } ?>
        <?php
        if ($tabelle_riferite != null) {
        ?>
            <div class="vincoli widget-classifica off">
                <h2>Vincoli di integrità</h2>
                <table>
                    <tr>
                        <th>Attributo in <?php echo $nome_tabella ?></th>
                        <th>Reference </th>
                    </tr>
                    <tbody>
                        <?php foreach ($tabelle_riferite as $tabella) { ?>
                            <tr>
                                <td><?php echo strtoupper($tabella['nome_tabella']) . "." .  $tabella['nome_attributo'] . " ===> "; ?></td>
                                <td><?php echo strtoupper($tabella['tabella_vincolata']) . "." . $tabella['attributo_vincolato']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <div class="tabelle">
            <div class="widget-classifica">
                <form id="insert_values" method='post' action='/pages/professore/riempi_tabella_handler.php?nome_tabella=<?php echo $nome_tabella; ?>'>

                    <?php
                    require_once '../../helper/print_table.php';
                    generateTable($nome_tabella);
                    ?>
                    <tbody>
                        <tr>
                            <?php foreach ($attributi as $attributo) {
                                $tipo_placeholder = $attributo['tipo_attributo'];
                                $placeholder = $attributo['nome_attributo'];

                                echo "<td><input name='$placeholder' placeholder='Inserisci *$placeholder*'";

                                if (strtoupper($tipo_placeholder) == 'INT') {
                                    echo "type='number' step='1'";
                                } else if (strtoupper($tipo_placeholder) == 'FLOAT') {
                                    echo "type='number' step='0.01'";
                                } else if (strtoupper($tipo_placeholder) == 'DATE') {
                                    echo "type='date'";
                                } else if (strtoupper($tipo_placeholder) == 'DECIMAL') {
                                    echo "type='number' step='0.01'";
                                } else {
                                    echo "type='text'";
                                }
                                if ($attributo['is_key'] == "TRUE") {
                                    echo  "required>";
                                }
                                echo "</td>";
                            } ?>
                        </tr>
                    </tbody>
                    </table>
                    <button type='submit'>Aggiungi riga</button>
                </form>
            </div>

            <!-- mostra anche le tabelle a cui la tabella in get fa reference se ne ha-->
            <?php

            if ($tabelle_riferite != null) {
            ?>


                <!-- <h3>Righe tabella vincolata</h3> -->
                <?php
                require_once '../../helper/print_table.php';

                foreach ($tabelle_riferite as $tabella) {

                    echo '<div class="widget-classifica">';
                    generateTable($tabella['tabella_vincolata']);
                    echo "</table> </div>";
                }

                ?>

            <?php } ?>
        </div>


        <script>
            function mostraVincoli() {
                var vincoli = document.querySelector('.vincoli');
                vincoli.classList.toggle('on');
                vincoli.classList.toggle('off');
            }
        </script>
</body>

</html>