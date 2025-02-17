<?php
header('Content-Type: application/json');
require_once 'conn.php';
require_once 'verify-token.php'; // File dedicato per gestire la verifica del token
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Telegram\Bot\Api;

    

$data = json_decode(file_get_contents('php://input'), true);

function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}
// Genera un token JWT
function generateJWT($userId, $botToken) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token valido per 1 ora
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
    if (validateTelegramData($initData, $secret)) {
        $balance = 0;  
        $user_level = 1;  
        $next_level_points = 10000;  
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
            $token = generateJWT($telegram_id, $secret);
            // L'utente esiste già, recuperiamo lo stato del countdown
            $sql_countdown = "SELECT * FROM countdown WHERE user_id = ?";
            $stmt_countdown = $conn->prepare($sql_countdown);
            $stmt_countdown->bind_param("s", $telegram_id);
            $stmt_countdown->execute();
            $result_countdown = $stmt_countdown->get_result();

            if ($result_countdown->num_rows > 0) {
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
                    }
                }
                sendResponse(true, 'User already exists', [
                    'status_countdown' => $status_countdown,
                    'remaining_time' => $end_time->format(DateTime::ISO8601),
                    'token' => $token
                ]);
            } else {
                sendResponse(false, 'No countdown data found for user', [
                    'token' => $token
                ]);
            }
        } else {
            // L'utente non esiste, quindi lo registriamo
            $token = generateJWT($telegram_id, $secret);
            $conn->begin_transaction();

            try {
                $token = generateJWT($telegram_id, $secret);
                // Inserimento nuovi utenti
                $sql = "INSERT INTO users (user_id, balance, user_level, next_level_points, ppd_value) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siiii", $telegram_id, $balance, $user_level, $next_level_points, $ppd_value);
                $stmt->execute();

                // Inserimento per useritems
                $sql_item = "INSERT INTO useritems (user_id, item_id, purchased, active) VALUES (?, ?, ?, ?)";
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->bind_param("siii", $telegram_id, $item_id, $purchased, $active);
                $stmt_item->execute();

                // Inizializzazione countdown
                $start_time = '9999-12-31 23:59:59';
                $end_time = '9999-12-31 23:59:59';
                $status_countdown = "inattivo";
                $sql_countdown = "INSERT INTO countdown (user_id, start_time, end_time, status_countdown) VALUES (?, ?, ?, ?)";
                $stmt_countdown = $conn->prepare($sql_countdown);
                $stmt_countdown->bind_param("ssss", $telegram_id, $start_time, $end_time, $status_countdown);
                $stmt_countdown->execute();
                

                $conn->commit();
               

                sendResponse(true, 'User already exists', [
                    'success' => true,
                    'message' => 'User and items added successfully, countdown inactive',
                    'token' => $token
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                sendResponse(false, $e->getMessage());
            }
        }
    } else {
        sendResponse(false, 'Invalid init data');
    }
} else {
    sendResponse(false, 'Invalid or missing parameters');
}

$conn->close();
?>