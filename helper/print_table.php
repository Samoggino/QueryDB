<?php

function generateTable($tabella)
{
    try {
        $db = connectToDatabaseMYSQL();
        $stmt = $db->prepare("SELECT * FROM " . $tabella);
        $stmt->execute();
        $valori_tabella_fisica = $stmt->fetchAll();
        $stmt->closeCursor();

        $stmt = $db->prepare("CALL GetAttributiTabella(:nome_tabella)");
        $stmt->bindParam(':nome_tabella', $tabella, PDO::PARAM_STR);
        $stmt->execute();
        $attributi = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (\Throwable $th) {
        echo "PROBLEM RIGHE TABELLA VINCOLATA <br>" . $th->getMessage();
    }
?>
    <style>
        table {
            max-width: 50%;
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        table img {
            max-width: 250px;
            max-height: 250px;
        }
    </style>

    <h3><?php echo "Tabella: " . strtoupper($tabella); ?></h3>
    <table>
        <thead>
            <tr>
                <?php foreach ($attributi as $attributo) { ?>
                    <th><?php echo $attributo['nome_attributo'] . ": "  . $attributo['tipo_attributo']; ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($valori_tabella_fisica as $riga) { ?>
                <tr>
                    <?php foreach ($riga as $key => $value) { ?>
                        <td><?php echo $value; ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>