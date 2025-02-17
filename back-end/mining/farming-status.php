<?php
header('Content-Type: application/json');
require_once "../conn.php";

// Recupera l'ID utente dalla query string
require_once '../verify-token.php'; // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$userId = $_GET['userId'];

if (!$userId) {
    echo json_encode(["error" => "User ID mancante."]);
    exit;
}

// Prepara e esegui la query
$query = "SELECT farming_start, farming_duration, claimed FROM farming_status WHERE user_id = ?";
$uquery = $conn->prepare($query);
$uquery->bind_param("s", $userId); // "s" indica che userId è una stringa
$uquery->execute();

// Recupera i dati
$result = $uquery->get_result();
$farmingData = $result->fetch_assoc();

if (!$farmingData) {
    echo json_encode(["error" => "Farming non trovato per l'utente."]);
    exit;
}

// Calcola il progresso
$currentTimestamp = time();
$farmingStart = strtotime($farmingData['farming_start']);
$farmingDuration = $farmingData['farming_duration'];
$elapsedTime = $currentTimestamp - $farmingStart;

$progress = min(($elapsedTime / $farmingDuration) * 100, 100);

// Prepara la risposta
$response = [
    "farmingStart" => $farmingData['farming_start'],
    "farmingDuration" => $farmingData['farming_duration'],
    "progress" => round($progress, 2),
    "claimed" => $farmingData['claimed'],
    "isCompleted" => $progress >= 100 && !$farmingData['claimed'] // Farming completato ma non ancora reclamato
];


// Restituisce la risposta in formato JSON
echo json_encode($response);
?>
