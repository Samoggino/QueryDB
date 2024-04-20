<?php
require_once 'connessione_mysql.php';


/**
 * Stampa i quesiti associati ad un test
 * @param $test_associato
 */
function printQuesitiDiTest($test_associato)
{
    $db = connectToDatabaseMYSQL();
    $sql = "CALL GetQuesitiAssociatiAlTest(:test_associato)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':test_associato', $test_associato);
    $stmt->execute();
    $quesiti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (empty($quesiti)) {
        echo "<p>Non ci sono quesiti associati a questo test</p>";
        return;
    }
    echo "<table>";
    echo "<tr>";
    echo "<th>Numero quesito</th>";
    echo "<th>Descrizione</th>";
    echo "<th>Difficoltà</th>";
    echo "<th>Tipo</th>";
    echo "<th>Tabelle di riferimento</th>";
    echo "</tr>";

    foreach ($quesiti as $quesito) {
        $sql = "CALL GetTabelleQuesitiNum(:id_quesito)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_quesito', $quesito['ID']);
        $stmt->execute();
        $tabelle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $tabelle_di_riferimento = implode(", ", array_map(function ($tabella) {
            return $tabella['nome_tabella'];
        }, $tabelle)) ?: "-";

        echo "<tr>";
        echo "<td>" . $quesito['numero_quesito'] . "</td>";
        echo "<td>" . $quesito['descrizione'] . "</td>";
        echo "<td>" . $quesito['livello_difficolta'] . "</td>";
        echo "<td>" . $quesito['tipo_quesito'] . "</td>";
        echo "<td>" . $tabelle_di_riferimento . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    $db = null;
}
