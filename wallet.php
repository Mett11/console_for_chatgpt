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
    <link rel="manifest" href="./tonconnect-manifest.json">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Outfit:wght@100..900&family=Sixtyfour+Convergence&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
    <script src="https://telegram.org/js/telegram-web-app.js" defer></script>
    <script src="https://unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js"></script>
    



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

    <div class="container-wallet">
        
        <div id = "container-ton-image">
            <img id = "ton-image" src = "img/ton-3d-nofx.png"/>
            <h1> Connect your <cite id = "blue-ton-text">TON</cite> wallet </h1>
        </div>
            
        <div id = "container-info-wallet">
   
            <div id = "ton-connect">
 
            </div>
           
        </div>

    </div>



     
    <nav class="bottom-menu">
        <a href="homepage.php?user_id=<?= $user_id ?>" class="menu-item" id="homeButton" data-page="home" data-text="Home">
            <img src="img/icons-home.svg" alt="Task Icon" />
        </a>
        <a href="task.php?user_id=<?= $user_id ?>" class="menu-item" id="taskButton" data-page="task" data-text="Task">
            <img src="img/icons-task.svg" alt="Task Icon" />
        </a>
        <a href="mining.php?user_id=<?= $user_id ?>" class="menu-item" id="miningButton" data-page="mining" data-text="Mining">
            <img src="img/icons-pc.svg" alt="Mining Icon" />
        </a>
        <a href="friends.php?user_id=<?= $user_id ?>" class="menu-item" id="friendsButton" data-page="friends" data-text="Friends">
            <img src="img/icons-friends.svg" alt="Friends Icon" />
        </a>
        <a href="wallet.php?user_id=<?= $user_id ?>" class="menu-item" id="walletButton" data-page="wallet" data-text="Wallet">
            <h1 class="text-menu-active">Wallet</h1>
        </a>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Verifica che la libreria sia stata caricata
            if (typeof TON_CONNECT_UI === "undefined") {
                console.error("TonConnect UI non è stato caricato correttamente.");
                return;
            }
            
            // Inizializza TonConnect UI
            const tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
                manifestUrl: 'https://raw.githubusercontent.com/Mett11/tg_console_repo/refs/heads/main/tonconnect-manifest.json',
                buttonRootId: 'ton-connect'
            });
            
            // Verifica se il wallet è già connesso controllando la proprietà `wallet`
            if (tonConnectUI.wallet) {
                console.log("Wallet già connesso:", tonConnectUI.wallet.account.address);
                localStorage.setItem('connectedWallet', JSON.stringify(tonConnectUI.wallet));
            } else {
                console.log("Nessun wallet connesso.");
            }
            
            // Ascolta i cambiamenti di stato (connesso/disconnesso)
            tonConnectUI.onStatusChange((wallet) => {
                if (wallet) {
                    console.log("Wallet connesso:", wallet.account.address);
                    localStorage.setItem('connectedWallet', JSON.stringify(wallet));
                } else {
                    console.log("Wallet disconnesso.");
                    localStorage.removeItem('connectedWallet');
                }
            });
        });
    </script>
</body>

</html>