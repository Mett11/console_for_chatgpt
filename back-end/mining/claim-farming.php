<?php
header('Content-Type: application/json');
require_once '../conn.php'; // Connessione al DB
require_once '../verify-token.php'; // Includi il file che contiene la funzione per verificare il token

//Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'];
$claimedPoints = $data['claimedPoints'];

if (!$userId || !$claimedPoints) {
    echo json_encode(["error" => "Dati mancanti."]);
    exit;
}

// Controlla lo stato del farming
$query = $conn->prepare("SELECT claimed FROM farming_status WHERE user_id = ? AND claimed = 0");
$query->bind_param("s", $userId);
$query->execute();
$result = $query->get_result();
$farmingData = $result->fetch_assoc();

if (!$farmingData || $farmingData['claimed']) {
    echo json_encode(["error" => "Farming già reclamato o non trovato."]);
    exit;
}

// Aggiorna il saldo e segna il farming come completato
$conn->begin_transaction();
try {
    $updateFarming = $conn->prepare("UPDATE farming_status SET claimed = 1 WHERE user_id = ?");
    $updateFarming->bind_param("s", $userId);
    $updateFarming->execute();

    $updateBalance = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
    $updateBalance->bind_param("is", $claimedPoints, $userId);
    $updateBalance->execute();

   

    $conn->commit();
    echo json_encode(["success" => true, "newBalance" => $claimedPoints]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["error" => "Errore nell'aggiornamento."]);
}
?>
