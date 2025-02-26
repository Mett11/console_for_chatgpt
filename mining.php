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
    <script src="https://unpkg.com/tonweb/dist/tonweb.min.js"></script>

    <script type="module" src="js/mining/mining.js?v=<?php echo time(); ?>"></script>
    
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
    

    

    <div class="scroll-container mining-container" id="scroll-page-container">
        
 

        <section id="mining" class="section">
            <div class="container-page-mining">
                <h1 class="header-phrase">
                Start your farming and claim each 24 hours
                </h1>
                <div class="info-header-mining">
                    <h2 id="CNSL-point-mining" class="user-point-mining">$CNSL </h2>
                    <span><h2 id="PxD" class="profit_per_day_class">PPD </h2></span>
                </div>
                <div id="grid-component">
                    <div id="CPU" class="component">CPU</div>
                    <div id="GPU" class="component">GPU</div>
                    <div id="OS" class="component">O.S.</div>
                    <div id="SSD" class="component">SSD</div>
                    <div id="HDD" class="component">HDD</div>
                    <div id="NETWORK-CARD" class="component">NETWORK CARD</div>
                    <div id="RAM" class="component">RAM</div>
                </div>
                <div class="button-manage-farm">
                    <button id="farming-button" disabled></button>

                </div>
                
            </div>
        </section>
        

    </div>
    
    
    <nav class="bottom-menu">
        <a href="homepage.php?user_id=<?= $user_id ?>" class="menu-item" id="homeButton" data-page="home" data-text="Home">
            <img src="img/icons-home.svg" alt="Task Icon" />
        </a>
        <a href="task.php?user_id=<?= $user_id ?>" class="menu-item" id="taskButton" data-page="task" data-text="Task">
            <img src="img/icons-task.svg" alt="Task Icon" />
        </a>
        <a href="mining.php?user_id=<?= $user_id ?>" class="menu-item" id="miningButton" data-page="mining" data-text="Mining">
            <h1 class="text-menu-active">Mining</h1>
        </a>
        <a href="friends.php?user_id=<?= $user_id ?>" class="menu-item" id="friendsButton" data-page="friends" data-text="Friends">
            <img src="img/icons-friends.svg" alt="Friends Icon" />
        </a>
        <a href="wallet.php?user_id=<?= $user_id ?>" class="menu-item" id="walletButton" data-page="wallet" data-text="Wallet">
            <img src="img/icons-wallet.svg" alt="Wallet Icon" />
        </a>
    </nav>
    <div id="ton-connect" style="display:none;"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            // Prova a ripristinare il wallet salvato
            const savedWallet = localStorage.getItem('connectedWallet');
            if (savedWallet) {
                window.tonWallet = JSON.parse(savedWallet);
                console.log("Wallet ripristinato da localStorage:", window.tonWallet.account.address);
            } else {
                console.log("Nessun wallet salvato in localStorage.");
            }

            // Inizializza TON Connect UI
            window.tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
                manifestUrl: 'https://raw.githubusercontent.com/Mett11/tg_console_repo/refs/heads/main/tonconnect-manifest.json',
                buttonRootId: 'ton-connect'
            });
            
            window.tonConnectUI.onStatusChange((wallet) => {
                if (wallet) {
                    console.log("Wallet connesso:", wallet.account.address);
                    localStorage.setItem('connectedWallet', JSON.stringify(wallet));
                    window.tonWallet = wallet;
                } else {
                    console.log("Wallet disconnesso.");
                    localStorage.removeItem('connectedWallet');
                    window.tonWallet = null;
                }
            });
        });

    </script>



    

</body>
</html>
