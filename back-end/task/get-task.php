<?php
// Imposta l'header per il contenuto JSON
header('Content-Type: application/json');
require_once '../verify-token.php'; // Includi il file che contiene la funzione per verificare il token
require_once '../conn.php'; // Modifica il percorso se necessario

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}
// Connessione al database

// Controlla se il parametro `type` è presente
if (!isset($_GET['type'])) {
    echo json_encode(["error" => "Missing 'type' parameter"]);
    exit;
}

$type = $_GET['type']; // Recupera il tipo di attività

// Query per recuperare i task in base al tipo
$query = "SELECT id_task, nome_task, link_esterno, claim_point, type FROM task WHERE type = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

// Restituisci i dati in formato JSON
echo json_encode($tasks);
?>
