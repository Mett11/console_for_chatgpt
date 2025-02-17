<?php

$botToken = '7944123584:AAFz-N6nuulgO5IP_lj3WZNbH2UnKZlAuC8';  // Sostituisci con il tuo token del bot
$apiURL = "https://api.telegram.org/bot$botToken/";
$webAppUrl = "https://05ce-2a0e-41d-27dd-0-808b-45e-93b7-4216.ngrok-free.app/Console_2/index.php";
$pathGlobal = "";



// Funzione per inviare messaggi a Telegram
function sendTelegramMessage($chat_id, $message, $keyboard = null) {
    global $apiURL;

    $data = [
        'chat_id' => $chat_id,
        'text' => $message
    ];

    if ($keyboard !== null) {
        $data['reply_markup'] = json_encode($keyboard);
    }

    file_get_contents($apiURL . "sendMessage?" . http_build_query($data));
}

// Leggi il contenuto della richiesta inviata da Telegram
$update = json_decode(file_get_contents('php://input'), true);

// Verifica se ci sono messaggi nuovi
if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $message_text = $update['message']['text'];
    $username = isset($update['message']['from']['username']) ? $update['message']['from']['username'] : 'Sconosciuto';  // Recupera il nome utente (se disponibile)

    // Quando l'utente avvia la conversazione (cliccando su "Avvia")
    if (isset($update['message']['text']) && $message_text === '/start') {
        // Quando l'utente invia /start (sia al primo avvio che successivamente)
        //$webAppUrl = $webAppUrl;
        $webAppUrl .= '?user_id=' . urlencode($chat_id);
        $_SESSION['my_session_userid'] = $chat_id;


        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Open Console!', 'web_app' => ['url' => $webAppUrl]]  // Link alla tua Web App con parametri
                ]
            ]
        ];

        // Rispondi direttamente con il bottone "Apri la mini app"
        sendTelegramMessage($chat_id, "Clicca qui per aprire Console!", $keyboard);
    } elseif ($message_text !== '/start') {
        
        // Se l'utente scrive qualcos'altro, possiamo comunque inviare il pulsante per aprire la mini app
        // Senza chiedere di inviare /start.
        //$webAppUrl = 'https://db4e-2a0e-41d-27dd-0-6db2-d170-eaff-faf0.ngrok-free.app/Console/index.php';
        $webAppUrl .= '?user_id=' . urlencode($chat_id);
        $_SESSION['my_session_userid'] = $chat_id;
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Apri la mini app', 'web_app' => ['url' => $webAppUrl]]  // Link alla tua Web App con parametri
                ]
            ]
        ];

        // Rispondi con il bottone per aprire la mini app
        sendTelegramMessage($chat_id, "Clicca qui per aprire Console!", $keyboard);
    }
}
?>