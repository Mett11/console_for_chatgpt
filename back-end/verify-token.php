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
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        if (verifyToken($token)) {
            return 200; // Token valido
        } else {
            return 401; // Token non valido
        }
    } else {
        return 400; // Header mancante
    }
}



// Verifica del token JWT
function verifyToken($token) {
    $secretKey = '7944123584:AAFz-N6nuulgO5IP_lj3WZNbH2UnKZlAuC8'; // Assicurati che sia la stessa usata per generare il token
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        return $decoded; // Restituiamo i dati decodificati
    } catch (Exception $e) {
        return false;
    }
}
?>