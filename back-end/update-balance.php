<?php

require_once 'conn.php';

require_once 'verify-token.php'; // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);



if (isset($data['user_id']) && isset($data['amount']) && isset($data['type']) && is_numeric($data['user_id']) && is_numeric($data['amount'])) {
    $userId = intval($data['user_id']);
    $amount = floatval($data['amount']);
    $type = $data['type'];

    // Determina l'operazione da eseguire
    if ($type === 'purchase') {
        $sql = "UPDATE users SET balance = balance - ? WHERE user_id = ?";
    } elseif ($type === 'claim') {
        $sql = "UPDATE users SET balance = balance + ? WHERE user_id = ?";
    } else {
        echo json_encode(['error' => 'Invalid operation type']);
        exit;
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param("di", $amount, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No rows updated']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid or missing parameters']);
}

$conn->close();
?>
