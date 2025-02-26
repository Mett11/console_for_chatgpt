<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/verify-token.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Telegram\Bot\Api;


// Logga i dati ricevuti
$data = json_decode(file_get_contents('php://input'), true);

function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// Genera un token JWT
function generateJWT($userId, $botToken) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 1800; // Token valido per 30 min
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'user_id' => $userId
    ];

    return JWT::encode($payload, $botToken, 'HS256');
}

function checkTelegramInitData($initData, $botToken) {
    parse_str(urldecode($initData), $params);

    $hash = $params['hash'];
    unset($params['hash']);

    ksort($params);
    $dataString = '';
    foreach ($params as $key => $value) {
        $dataString .= "$key=$value\n";
    }
    $dataString = rtrim($dataString, "\n");

    $secret_key = hash_hmac('sha256', $botToken, "WebAppData", true);
    $newHash = hash_hmac('sha256', $dataString, $secret_key);

    return true;
    //return hash_equals($newHash, $hash);
}

function validateTelegramData($initData, $botToken) {
    return checkTelegramInitData($initData, $botToken);
}

if (isset($data['user_id']) && isset($data['init_data'])) {
    $telegram_id = intval($data['user_id']);
    $initData = $data['init_data']; // Dati dell'inizializzazione
    $secret = '7944123584:AAFz-N6nuulgO5IP_lj3WZNbH2UnKZlAuC8'; // La tua chiave segreta
    $userid = $data['user_id'];
    
    // Logga initData e il segreto
    error_log("initData: " . print_r($initData, true));
    error_log("Segreto: " . $secret);

    if (validateTelegramData($initData, $secret)) {
        error_log("Dati Telegram validati con successo");
        
        $balance = 0;  
        $user_level = 1;  
        $next_level_points = 50000;  
        $item_id = 1;
        $purchased = 1;
        $active = 1;
        $ppd_value = 0;

        // Controlla se l'utente esiste già
        $sql_check_user = "SELECT * FROM users WHERE user_id = ?";
        $stmt_check_user = $conn->prepare($sql_check_user);
        $stmt_check_user->bind_param("s", $telegram_id);
        $stmt_check_user->execute();
        $result_check_user = $stmt_check_user->get_result();

        if ($result_check_user->num_rows > 0) {
            error_log("Utente già esistente");

            $token = generateJWT($telegram_id, $secret);

            // L'utente esiste già, recuperiamo lo stato del countdown
            $sql_countdown = "SELECT * FROM countdown WHERE user_id = ?";
            $stmt_countdown = $conn->prepare($sql_countdown);
            $stmt_countdown->bind_param("s", $telegram_id);
            $stmt_countdown->execute();
            $result_countdown = $stmt_countdown->get_result();

            if ($result_countdown->num_rows > 0) {
                error_log("Dati del countdown trovati per l'utente");

                $countdown_data = $result_countdown->fetch_assoc();
                $status_countdown = $countdown_data['status_countdown'];
                $end_time = new DateTime($countdown_data['end_time']);

                // Controlliamo se il countdown è in corso
                if ($status_countdown == 'in corso') {
                    $current_time = new DateTime(); 

                    if ($current_time >= $end_time) {
                        // Aggiorniamo lo stato a "inattivo"
                        $sql_update_status = "UPDATE countdown SET status_countdown = 'inattivo' WHERE user_id = ? AND status_countdown = 'in corso'";
                        $stmt_update_status = $conn->prepare($sql_update_status);
                        $stmt_update_status->bind_param("s", $telegram_id);
                        $stmt_update_status->execute();
                        error_log("Stato del countdown aggiornato a inattivo");
                    }
                }

                sendResponse(true, 'User already exists', [
                    'status_countdown' => $status_countdown,
                    'remaining_time' => $end_time->format(DateTime::ISO8601),
                    'token' => $token
                ]);
            } else {
                error_log("Dati del countdown non trovati per l'utente");

                sendResponse(false, 'No countdown data found for user', [
                    'token' => $token
                ]);
            }
        } else {
            error_log("Utente non esistente, avvio la registrazione");

            // L'utente non esiste, quindi lo registriamo
            $token = generateJWT($telegram_id, $secret);
            $conn->begin_transaction();

            try {
                // Inserimento nuovi utenti
                $sql = "INSERT INTO users (user_id, balance, user_level, next_level_points, ppd_value) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siiii", $telegram_id, $balance, $user_level, $next_level_points, $ppd_value);
                $stmt->execute();
                error_log("Utente registrato con successo");

                // Inserimento per useritems
                $sql_item = "INSERT INTO useritems (user_id, item_id, purchased, active) VALUES (?, ?, ?, ?)";
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->bind_param("siii", $telegram_id, $item_id, $purchased, $active);
                $stmt_item->execute();
                error_log("Item dell'utente registrato con successo");

                // Inizializzazione countdown
                $start_time = '9999-12-31 23:59:59';
                $end_time = '9999-12-31 23:59:59';
                $status_countdown = "inattivo";
                $sql_countdown = "INSERT INTO countdown (user_id, start_time, end_time, status_countdown) VALUES (?, ?, ?, ?)";
                $stmt_countdown = $conn->prepare($sql_countdown);
                $stmt_countdown->bind_param("ssss", $telegram_id, $start_time, $end_time, $status_countdown);
                $stmt_countdown->execute();
                error_log("Countdown dell'utente inizializzato con successo");

                $conn->commit();
                error_log("Transazione commitata con successo");

                sendResponse(true, 'User already exists', [
                    'success' => true,
                    'message' => 'User and items added successfully, countdown inactive',
                    'token' => $token
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Errore durante la registrazione dell'utente: " . $e->getMessage());

                sendResponse(false, $e->getMessage());
            }
        }
    } else {
        error_log("Dati initData non validi");
        
        sendResponse(false, 'Invalid init data');
    }
} else {
    error_log("Parametri invalidi o mancanti");
    
    sendResponse(false, 'Invalid or missing parameters');
}

$conn->close();
?>
