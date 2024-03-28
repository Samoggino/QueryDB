<?php
function visualizzaMessaggi()
{
    $db = connectToDatabaseMYSQL();

    if ($_SESSION['ruolo'] == 'STUDENTE') {
        $sql = "CALL GetMessaggiStudente(:email);";
    } else if ($_SESSION['ruolo'] == 'PROFESSORE') {
        $sql = "CALL GetMessaggiProfessore(:email);";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $messaggi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $db = null;

    if (count($messaggi) > 0) { ?>

        <h1>Messaggi ricevuti</h1>
        <table>
            <tr>
                <th> Mittente </th>
                <th> Titolo </th>
                <th> Testo </th>
                <th> Data </th>
                <th> Test associato </th>
            </tr>
            <?php
            foreach ($messaggi as $messaggio) {
            ?>
                <tr>
                    <td><?php echo $messaggio['mittente']; ?></td>
                    <td><?php echo $messaggio['titolo']; ?></td>
                    <td><?php echo $messaggio['testo']; ?></td>
                    <td><?php echo $messaggio['data_inserimento']; ?></td>
                    <td><?php echo $messaggio['test_associato']; ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php } else { ?>
        <h3>Non hai messaggi</h3>
<?php }
} ?>