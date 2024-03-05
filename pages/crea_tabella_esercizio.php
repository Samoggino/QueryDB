<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Creazione Tabella</title>
    <link rel="stylesheet" href="../styles/global.css">
</head>

<body>
    <h1>Creazione Tabella</h1>
    <form action="../handler/crea_tabella.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br>

        <h2>Aggiungi attributi:</h2>
        <div id="attributi">
            <div class="attributo">
                <input type="text" name="attributi[]" placeholder="Nome" required>
                <input type="text" name="tipi[]" placeholder="Tipo">
                <input type="checkbox" name="chiavi_primarie[]" value="true"> Chiave primaria
            </div>
        </div>
        <button type="button" id="aggiungi_attributo">Aggiungi attributo</button><br>

        <h2>Aggiungi vincoli:</h2>
        <div id="vincoli">
            <div class="vincolo">
                <input type="text" name="attributi_vincoli[]" placeholder="Attributo 1">
                <input type="text" name="attributi_vincoli[]" placeholder="Attributo 2">
                <input type="text" name="tipi_vincoli[]" placeholder="Tipo vincolo">
            </div>
        </div>
        <button type="button" id="aggiungi_vincolo">Aggiungi vincolo</button><br>

        <button type="submit">Crea tabella</button>
    </form>

    <script>
        document.getElementById('aggiungi_attributo').addEventListener('click', function() {
            var attributo = document.createElement('div');
            attributo.className = 'attributo';
            attributo.innerHTML = `
                <input type="text" name="attributi[]" placeholder="Nome" required>
                <input type="text" name="tipi[]" placeholder="Tipo">
                <input type="checkbox" name="chiavi_primarie[]" value="true"> Chiave primaria
            `;
            document.getElementById('attributi').appendChild(attributo);
        });

        document.getElementById('aggiungi_vincolo').addEventListener('click', function() {
            var vincolo = document.createElement('div');
            vincolo.className = 'vincolo';
            vincolo.innerHTML = `
                <input type="text" name="attributi_vincoli[]" placeholder="Attributo 1">
                <input type="text" name="attributi_vincoli[]" placeholder="Attributo 2">
                <input type="text" name="tipi_vincoli[]" placeholder="Tipo vincolo">
            `;
            document.getElementById('vincoli').appendChild(vincolo);
        });
    </script>
</body>

</html>