<?php
header('Content-Type: application/json');

// Connessione al database
require_once(__DIR__ . '/../conn.php'); // Modifica il percorso se necessario
require_once(__DIR__ . '/../verify-token.php'); // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}
// Recupera i dati inviati dalla richiesta POST
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? null;
$farmingDuration = $data['farmingDuration'] ?? 86400; // Durata di default

// Verifica che l'userId sia valido
if ($userId === null) {
    echo json_encode(['error' => 'User ID mancante']);
    exit();
}

// Controlla lo stato del farming per l'utente
$sql_check = "SELECT * FROM farming_status WHERE user_id = ? ORDER BY farming_start DESC LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $userId);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$farming = $result_check->fetch_assoc();

$response = []; // Variabile per la risposta

if ($farming && $farming['claimed'] == 0) {
    // Se esiste un farming attivo (claimed = 0), restituisci errore
    $response = ['error' => 'Esiste già un farming in corso per questo utente'];
} elseif ($farming && $farming['claimed'] == 1) {
    // Se esiste un farming completato (claimed = 1), riavvia il farming aggiornando la riga
    $sql_restart = "UPDATE farming_status SET farming_start = NOW(), farming_duration = ?, claimed = 0, progress = 0 WHERE id = ?";
    $stmt_restart = $conn->prepare($sql_restart);
    $stmt_restart->bind_param("ii", $farmingDuration, $farming['id']);

    if ($stmt_restart->execute()) {
        $response = [
            'success' => true,
            'message' => 'Farming riavviato con successo!',
            'farmingStart' => date('Y-m-d H:i:s'),
            'farmingDuration' => $farmingDuration
        ];
    } else {
        $response = ['error' => 'Errore durante il riavvio del farming'];
    }
} else {
    // Se non esiste alcun farming, crea una nuova riga
    $sql_insert = "INSERT INTO farming_status (user_id, farming_start, farming_duration, claimed, progress) VALUES (?, NOW(), ?, 0, 0)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("si", $userId, $farmingDuration);

    if ($stmt_insert->execute()) {
        $response = [
            'success' => true,
            'message' => 'Farming iniziato con successo!',
            'farmingStart' => date('Y-m-d H:i:s'),
            'farmingDuration' => $farmingDuration
        ];
    } else {
        $response = ['error' => 'Errore durante l\'avvio del farming'];
    }
}

// Restituisci la risposta al client
echo json_encode($response);

// Chiudi la connessione
$conn->close();
?>
