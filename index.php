<?php
session_start();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, height=device-height">
    <title>Layout Mobile</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Outfit:wght@100..900&family=Sixtyfour+Convergence&display=swap" rel="stylesheet">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <script type="module" src="init-user.js?v<?php echo time();?>"></script>


</head>
<body>


    <div class="container_homepage">
        <p id="p_index">
        Build your virtual machine and start missing as many $CNSL Points as possible!
        </p>
        <div class="div-continue-button">
            <a href="homepage.php"><button id = "first-button-continue">Go!</button></a> 
        </div>
        <div id="lottie-animation" style="width: 300px; height: 300px;"></div>


        <p id="warning">
        $CNSL has no intrinsic value, so there is no expectation of financial return but enjoy the ride :)
        </p>
    </div>



    <script>
  
    
        var animation = lottie.loadAnimation({
                container: document.getElementById('lottie-animation'), // ID del contenitore
                renderer: 'svg', // Renderer (SVG, canvas o HTML)
                loop: true, // Imposta se deve ripetersi
                autoplay: true, // Imposta l'avvio automatico
                path: 'img/animation_pre_homepage.json' // Percorso del file JSON
        });
    </script>
    
</body>

</html>