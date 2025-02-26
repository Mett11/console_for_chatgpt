<?php
header('Content-Type: application/json');

// Connessione al database
require_once(__DIR__ . '/../conn.php');
require_once(__DIR__ . '/../verify-token.php'); // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}
// Recupera i dati inviati via GET
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($user_id === null) {
    echo json_encode(["success" => false, "message" => "Missing user_id"]);
    exit;
}

// Verifica tutti i task completati dall'utente
$query_check = "SELECT task_id FROM usertask WHERE user_id = ? AND completato = 1";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

$completed_tasks = [];
while ($row = $check_result->fetch_assoc()) {
    $completed_tasks[] = $row['task_id'];
}

echo json_encode(["success" => true, "completed_tasks" => $completed_tasks]);
?>
