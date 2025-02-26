<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/verify-token.php'); // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id']) && is_numeric($data['user_id'])) {
    $userId = intval($data['user_id']);
    
    // Ottieni l'item attivo e l'immagine corrispondente
    $sql = "
    SELECT useritems.item_id, items.img_path 
    FROM useritems 
    JOIN items ON useritems.item_id = items.item_id 
    WHERE useritems.user_id = ? AND useritems.active = 1 
    LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $active_item = $result->fetch_assoc();
    } else {
        $active_item = null;
    }
    $stmt->close();

    if ($active_item) {
        echo json_encode([
            'success' => true,
            'item_id' => $active_item['item_id'],
            'img_path_user_keys' => $active_item['img_path']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No active item found'
        ]);
    }
} else {
    echo json_encode(['error' => 'Invalid or missing parameters']);
}

$conn->close();
?>


