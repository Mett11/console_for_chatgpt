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

if (isset($data['user_id']) && isset($data['level']) && isset($data['nextLevel'])) {
    $userId = intval($data['user_id']);
    $level = intval($data['level']);
    $nextLevelPoints = intval($data['nextLevel']);

    // Recupera il livello attuale dell'utente per assicurarti che il livello non venga diminuito
    $sql = "SELECT user_level FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->bind_result($currentLevel);
    $stmt->fetch();
    $stmt->close();

    // Se il nuovo livello è maggiore o uguale al livello corrente, aggiorna
    if ($level >= $currentLevel) {
        $sql = "UPDATE users SET user_level = ?, next_level_points = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['error' => 'Query preparation failed']);
            exit;
        }

        // Corretto tipo di binding
        $stmt->bind_param("iis", $level, $nextLevelPoints, $userId);
        $stmt->execute();

        // Controllo del risultato
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made (data might already be up-to-date)']);
            http_response_code(204);
        }

        $stmt->close();
    } else {
        // Se il nuovo livello è inferiore a quello attuale, non fare nulla
        echo json_encode(['success' => false, 'message' => 'Level cannot be decreased']);
    }
} else {
    echo json_encode(['error' => 'Invalid or missing parameters']);
}

$conn->close();
?>
