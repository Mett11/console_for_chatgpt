<?php
header('Content-Type: application/json');
require_once 'conn.php';
require_once 'verify-token.php'; // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id']) && isset($data['purchased']) && isset($data['item_id'])) {
    $userId = intval($data['user_id']);
    $purchased = intval($data['purchased']);
    $itemId = intval($data['item_id']);
    $active = 1;

    // Aggiorna l'item corrente con il nuovo acquisto
    $sql = "UPDATE useritems SET item_id = ?, purchased = ?, active = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Update query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iiii", $itemId, $purchased, $active, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Ottieni il nuovo percorso dell'immagine
        $imgSql = "SELECT img_path FROM items WHERE item_id = ?";
        $imgStmt = $conn->prepare($imgSql);
        if ($imgStmt === false) {
            echo json_encode(['error' => 'Image query preparation failed: ' . $conn->error]);
            exit;
        }

        $imgStmt->bind_param("i", $itemId);
        $imgStmt->execute();
        $imgResult = $imgStmt->get_result();

        if ($imgResult->num_rows > 0) {
            $imgData = $imgResult->fetch_assoc();
            echo json_encode(['success' => true, 'new_src' => $imgData['img_path'], 'item_id' => $itemId]);
        } else {
            echo json_encode(['error' => 'Image not found']);
        }

        $imgStmt->close();
    } else {
        echo json_encode(['error' => 'No data updated']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid or missing parameters']);
}

$conn->close();
?>
