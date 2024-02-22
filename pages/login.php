<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/favicon/favicon.ico" type="image">
    <link rel="stylesheet" href="/styles/login.css">

    <title>Login</title>
</head>

<body>

    <div class="wrapper">
        <div class="card-switch">
            <label class="switch">
                <input type="checkbox" class="toggle">
                <span class="slider"></span>
                <span class="card-side"></span>
                <div class="flip-card__inner">
                    <div class="flip-card__front">
                        <div class="title">Log in</div>
                        <form method="POST" action="" class="flip-card__form">
                            <input for="email" class="flip-card__input" name="email" placeholder="Email" type="text" required>
                            <input for="password" class="flip-card__input" name="password" placeholder="Password" type="password" required>
                            <input type="hidden" name="action" value="login"> <!-- Campo nascosto per indicare login -->
                            <button class="flip-card__btn" name="login">Login!</button> <!-- Pulsante per eseguire il login -->
                        </form>
                    </div>
                    <div class="flip-card__back">
                        <div class="title">Sign up</div>
                        <form method="POST" action="" class="flip-card__form">
                            <input for="nome" name="nome" class="flip-card__input" placeholder="Nome" type="text" required>
                            <input for="cognome" name="cognome" class="flip-card__input" placeholder="Cognome" type="text" required>
                            <input for="email" name="email" class="flip-card__input" placeholder="Email" type="email" required>
                            <input for="password" name="password" class="flip-card__input" placeholder="Password" type="password" required>
                            <input type="hidden" name="action" value="registrazione"> <!-- Campo nascosto per indicare registrazione -->
                            <button class="flip-card__btn" name="registrazione">Registrami!</button> <!-- Pulsante per eseguire la registrazione -->
                        </form>
                    </div>
                </div>
            </label>
        </div>
    </div>


    <?php
    require '../handler/login_handler.php';
    require '../handler/registrazione_handler.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['login'])) {
            echo "<p>login() function called!</p>";
            login();
        } elseif (isset($_POST['registrazione'])) {
            registrazione();
        }
    }
    ?>



</body>

</html>