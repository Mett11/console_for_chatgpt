<?php

    session_start();

    if (!isset($_SESSION['my_session_userid'])) {
        header("Location: index.php");
    }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, height=device-height">
    <title>Layout Mobile</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Outfit:wght@100..900&family=Sixtyfour+Convergence&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
    <script src="https://unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js"></script>  
    <script src="https://sad.adsgram.ai/js/sad.min.js"></script>
</head>
<body>
   

    <header>
        <img src="img/logo.svg" alt="logo Console"/>
        <p>Console</p>
        <p id="user-id">
            <?php 
                if (isset($_SESSION['my_session_userid'])) {
                    echo htmlspecialchars($_SESSION['my_session_userid']); // Mostra il valore della sessione
                } 
                else {
                    echo "No ID.";
                }
            ?>
        </p>

        
    </header>



    

    <div class="scroll-container" id="scroll-page-container">
        <section id="home"  class="section active">
            <div id="level-up-popup" class="level-up-popup">
                <p id="level-up-message"></p>
            </div>
            <div class="container home">
                <div class="first-line">
                    <div class="next-claim-box first-line-box">
                        <h1 class="h1-info-first-line-homepage">Next Claim</h1>
                        <div id="countdown" class="info-first-line-homepage">00:00:00</div>
                    </div>
                    <div class="level-box first-line-box">
                        <h1 class="h1-info-first-line-homepage">Level</h1>
                        <div id="user-level" class="info-first-line-homepage"></div>
                    </div>
                    <div class="next-level-box first-line-box">
                        <h1 class="h1-info-first-line-homepage">Next Level Point</h1>
                        <div id="next-level-point" class="info-first-line-homepage"></div>
                    </div>
                </div>
                <div class="point">
                    <h1>$CNSL Point</h1>
                    <h2 id="CNSL-point" class="user-point">0</h2>
                </div>
                <div class="box-start-button">
                    <img id="user-keys" class="user_keys_enabled" disabled>
                </div>
                <div class="container-h1-open-keys">
                    <h1 id="h1-open-keys">Discover the other <cite id="open-keys">Keys</cite></h1>
                </div>

                <div class = "open-daily-reward-box">
                    <img id="open-daily-button" src="img/open_daily_reward.png"/>
                </div>
            </div>

            <div class="daily-reward-box">
                <h1 id="header-daily-box">Claim your daily reward!</h1>
                <button class="daily-reward-class" id ="daily-reward-button">Claim</button>
                <h1 id="close-daily-reward-box">X</h1>
                <h1 id="streak-days-reward"></h1>
                <h1 id="countdown-daily-reward"></h1>
            </div>
            <div id="popup">
                <div class="header-box-keys">
                    <h3>Buy a new <i>Keys</i></h3>
                    <h3 id="close-popup">X</h3>

                </div>

                <div id="popup-items"></div>
            </div>
        </section>

        
    </div>


    
    <nav class="bottom-menu">
        <a href="homepage.php" class="menu-item" id="homeButton" data-page="home" data-text="Home">
            <h1 class ="text-menu-active">Home</h1>
        </a>
        <a href="task.php" class="menu-item" id="taskButton" data-page="task" data-text="Task">
            <img src="img/icons-task.svg" alt="Task Icon" />
        </a>
        <a href="mining.php" class="menu-item" id="miningButton" data-page="mining" data-text="Mining">
            <img src="img/icons-pc.svg" alt="Mining Icon" />
        </a>
        <a href="friends.php" class="menu-item" id="friendsButton" data-page="friends" data-text="Friends">
            <img src="img/icons-friends.svg" alt="Friends Icon" />
        </a>
        <a href="wallet.php" class="menu-item" id="walletButton" data-page="wallet" data-text="Wallet">
            <img src="img/icons-wallet.svg" alt="Wallet Icon" />
        </a>
    </nav>

    <script type="module" src="./js/homepage.js?v=<?php echo time();?>"></script>


</body>


</html>
