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

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    // Query per ottenere il livello attuale dell'utente
    $sql = "SELECT user_level, next_level_points FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Query preparation failed']);
        exit;
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->bind_result($currentLevel, $nextLevelPoint);
    $stmt->fetch();
    $stmt->close();

    if ($currentLevel !== null) {
        // Restituisci il livello corrente dell'utente
        echo json_encode(['success' => true, 'level' => $currentLevel, 'next_level_points' => $nextLevelPoint]);
    } else {
        // Se l'utente non esiste, ritorna errore
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Missing user_id']);
}

$conn->close();
?>
