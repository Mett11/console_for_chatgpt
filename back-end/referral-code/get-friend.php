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
if (!$token || !verifyAuthorizationHeader($token)) {
    sendResponse(false, 'Invalid or missing token');
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id']; // Assegna il valore alla variabile $userId

if (isset($userId)) {

    $sql = "SELECT invited_id, created_at FROM referrals WHERE inviter_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = [];
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }

    sendResponse(true, 'Friends retrieved successfully', ['friends' => $friends]);

    $stmt->close();
} else {
    sendResponse(false, 'Error not telegram user :(');
}

$conn->close();
?>
