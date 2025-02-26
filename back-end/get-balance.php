<?php
// Imposta l'header per il contenuto JSON
header('Content-Type: application/json');

// Connessione al database
require_once(__DIR__ . '/conn.php'); // Modifica il percorso se necessario
require_once(__DIR__ . '/verify-token.php'); // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

// Verifica se il parametro user_id è presente
if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "Missing 'user_id' parameter"]);
    exit;
}

$user_id = $_GET['user_id']; // Recupera l'user_id

// Query per recuperare il saldo dell'utente
$query = "SELECT balance FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Restituisce il saldo dell'utente
    echo json_encode(["success" => true, "balance" => $row['balance']]);
} else {
    // Se l'utente non esiste
    echo json_encode(["error" => "User not found"]);
}
?>
