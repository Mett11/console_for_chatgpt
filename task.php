<?php
require_once(__DIR__ . '/back-end/verify-token.php');

ob_start(); // Avvia output buffering per evitare problemi con gli header

// Recupera lo user_id dalla query string
$user_id = $_GET['user_id'] ?? null;
// Recupera il token dal cookie
$token = $_COOKIE['jwt_token'] ?? null;
if (!$user_id || !$token) {
    header("Location: index.php");
    exit;
}

// Verifica il token (la funzione verifyAuthorizationHeader() deve accettare il token direttamente)
if (!verifyAuthorizationHeader($token)) {
    header("Location: index.php");
    exit;
}

ob_end_flush(); // Svuota il buffer
?>




<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, height=device-height">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, orientation=portrait">
    <title>Layout Mobile</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Outfit:wght@100..900&family=Sixtyfour+Convergence&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
    <script src="https://telegram.org/js/telegram-web-app.js" defer></script>
    <script src="https://unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js"></script>
    <script src="https://sad.adsgram.ai/js/sad.min.js"></script>
</head>
<body>
    
<header>
        <img src="img/logo.svg" alt="logo Console"/>
        <p>Console</p>
        <p id="user-id">
            <?php 
                echo  $user_id; // Mostra il valore della sessione
            ?>
        </p>
    </header>
    

    

    <div class="scroll-container scroll-task" id="scroll-page-container">        
        <section id="task" class="section">
            <div class="menu-type-task">
                <button id = "game-activity-button" class="task-type-button">Game</button>
                <button id = "social-activity-button" class="task-type-button">Social</button>
                <button id = "partner-activity-button" class="task-type-button">Adv</button>
            </div>
            <div id="contenuto">
                <div class="container-task-by-type"></div>
            </div>
        </section>

        
        
    </div>
    
    
    <nav class="bottom-menu">
        <a href="homepage.php?user_id=<?= $user_id ?>" class="menu-item" id="homeButton" data-page="home" data-text="Home">
            <img src="img/icons-home.svg" alt="Task Icon" />
        </a>
        <a href="task.php?user_id=<?= $user_id ?>" class="menu-item" id="taskButton" data-page="task" data-text="Task">
            <h1 class="text-menu-active">Task</h1>
        </a>
        <a href="mining.php?user_id=<?= $user_id ?>" class="menu-item" id="miningButton" data-page="mining" data-text="Mining">
            <img src="img/icons-pc.svg" alt="Mining Icon" />
        </a>
        <a href="friends.php?user_id=<?= $user_id ?>" class="menu-item" id="friendsButton" data-page="friends" data-text="Friends">
            <img src="img/icons-friends.svg" alt="Friends Icon" />
        </a>
        <a href="wallet.php?user_id=<?= $user_id ?>" class="menu-item" id="walletButton" data-page="wallet" data-text="Wallet">
            <img src="img/icons-wallet.svg" alt="Wallet Icon" />
        </a>
    </nav>

    <script type="module" src="js/task.js?v=<?php echo time(); ?>"></script>

</body>
</html>
