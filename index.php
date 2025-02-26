
<!DOCTYPE html>
<html lang="it">
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, height=device-height">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, orientation=portrait">

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
<div id="loading-screen" class="loading-screen">
  <div class="monitor-spinner">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
      <!-- Corpo del monitor -->
      <rect class="monitor-body" x="10" y="25" width="180" height="90" rx="10" ry="10" fill="#2c3e50" />
      <!-- Schermo del monitor -->
      <rect class="monitor-screen" x="15" y="30" width="170" height="70" rx="5" ry="5" fill="black" />
      
      <!-- Definizione del clipPath per confinare il contenuto -->
      <clipPath id="screenClip">
        <rect x="15" y="30" width="170" height="70" />
      </clipPath>
      
      <!-- Gruppo con clipPath applicato -->
      <g clip-path="url(#screenClip)">
        <!-- Prima riga: codice semplice -->
        <text id="code-text-1" x="20" y="45" fill="#00ff00" font-family="monospace" font-size="7">
          console.log("Mining in progress");
        </text>
        <!-- Seconda riga: inizio blocco setTimeout -->
        <text id="code-text-2" x="20" y="57" fill="#00ff00" font-family="monospace" font-size="7">
          setTimeout(() => {
        </text>
        <!-- Terza riga: rientro per il console.log interno -->
        <text id="code-text-3" x="30" y="69" fill="#00ff00" font-family="monospace" font-size="7">
          console.log("Almost done...");
        </text>
        <!-- Quarta riga: chiusura della funzione -->
        <text id="code-text-4" x="20" y="81" fill="#00ff00" font-family="monospace" font-size="7">
          }, 3000);
        </text>
      </g>
    </svg>
  </div>
</div>



    <div class="container_homepage" style="display:none;">
        <p id="p_index">
        Build your virtual machine and start missing as many $CNSL Points as possible!
        </p>
        <div class="div-continue-button">
            <a href="#" id="link_homepage"><button id = "first-button-continue">Go!</button disabled></a> 
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