<?php
session_start();
header('Content-Type: application/json');
require_once(__DIR__ . '/../conn.php');
require_once(__DIR__ . '/../verify-token.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    sendResponse(false, 'Invalid or missing token');
}

$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($userId) {

    $sql = "SELECT ref_code FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($referral_code);
    $stmt->fetch();

    if ($referral_code) {
        sendResponse(true, 'Referral code retrieved successfully', ['referral_code' => $referral_code]);
    } else {
        sendResponse(false, 'No referral code found');
    }

    $stmt->close();
} else {
    sendResponse(false, 'Error not telegram user :(');
}

$conn->close();
?>

