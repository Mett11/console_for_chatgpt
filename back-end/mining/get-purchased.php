<?php
// Imposta l'header per il contenuto JSON
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
// Verifica se il parametro user_id è presente
if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "Missing 'user_id' parameter"]);
    exit;
}

$user_id = $_GET['user_id']; // Recupera e converte user_id in intero

// Verifica la connessione al database
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Query per recuperare gli item acquistati (senza il filtro is_purchased)
$query = "SELECT hardware_name FROM user_hardware WHERE user_id = ?";
$stmt = $conn->prepare($query);

// Controllo se la preparazione della query è riuscita
if ($stmt === false) {
    echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
    exit;
}

// Eseguiamo la query e otteniamo il risultato
$stmt->bind_param("s", $user_id); // Cambia 's' in 'i' poiché user_id è un intero
$stmt->execute();
$result = $stmt->get_result();

// Array per memorizzare gli item acquistati
$purchasedItems = [];
while ($row = $result->fetch_assoc()) {
    $purchasedItems[] = $row['hardware_name'];
}

$stmt->close();
$conn->close();

// Se ci sono items acquistati, restituiamo un array di item
if (!empty($purchasedItems)) {
    echo json_encode(["success" => true, "purchasedItems" => $purchasedItems]);
} else {
    // Se non ci sono item acquistati
    echo json_encode(["error" => "No purchased items found"]);
}
?>

