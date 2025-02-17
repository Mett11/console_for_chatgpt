
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$botToken = '7944123584:AAFz-N6nuulgO5IP_lj3WZNbH2UnKZlAuC8';

// Funzione per ottenere il token Bearer
function getBearerToken() {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Funzione per verificare l'header Authorization e il token
function verifyAuthorizationHeader($token) {
    // Leggi l'header Authorization
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader); // Rimuovi 'Bearer ' dal token

        // Verifica il token
        if (verifyToken($token)) {
            return true; // Token valido
        } else {
            // Token non valido
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            return false;
        }
    } else {
        // Header Authorization mancante
        http_response_code(400);
        echo json_encode(['error' => 'Authorization header missing']);
        return false;
    }
}


// Verifica del token JWT
function verifyToken($token) {
    global $botToken;
    try {
        $decoded = JWT::decode($token, new Key($botToken, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

?>