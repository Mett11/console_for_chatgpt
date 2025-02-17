<?php
require_once 'verify-token.php'; // Verifica del token

header('Content-Type: application/json');

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['key'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$token = getBearerToken(); // Recupera il token dall'intestazione
if (!$token || !verifyAuthorizationHeader($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing token']);
    exit;
}

session_start();
$_SESSION['my_session_userid'] = $data['key'];
echo json_encode(['success' => true, "user_id" => $_SESSION['my_session_userid']]);

?>