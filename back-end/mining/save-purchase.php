<?php
header('Content-Type: application/json');

// Connessione al database
session_start();
require_once '../conn.php';
require_once '../verify-token.php'; // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}
// Recupera i dati JSON inviati
$data = json_decode(file_get_contents('php://input'), true);

// Debug: Logga i dati ricevuti
error_log(print_r($data, true));

// Controlla che tutte le chiavi necessarie siano presenti
if (!isset($data['userId'], $data['hardwareType'], $data['hardwareName'], $data['miningProfit'], $data['hardwareLevel'])) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti nella richiesta.']);
    exit;
}

// Salva i dati nel database
$userId = $data['userId'];
$hardwareType = $data['hardwareType'];
$hardwareName = $data['hardwareName'];
$priceCNS = isset($data['priceCNS']) ? $data['priceCNS'] : null;
$priceUSDT = isset($data['priceUSDT']) ? $data['priceUSDT'] : null;
$miningProfit = $data['miningProfit'];
$hardwareLevel = $data['hardwareLevel'];

// Verifica se l'hardware è già stato acquistato
$sqlCheckPurchase = "SELECT is_purchased FROM user_hardware WHERE user_id = ? AND hardware_name = ?";
$stmt = $conn->prepare($sqlCheckPurchase);
$stmt->bind_param("ss", $userId, $hardwareName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['is_purchased'] == 1) {
        // Se l'oggetto è già stato acquistato, restituisci un errore
        echo json_encode(['success' => false, 'message' => 'Questo hardware è già stato acquistato.']);
        exit;
    }
}

$sqlInsertHardware = "INSERT INTO user_hardware (user_id, hardware_type, hardware_name, price_cns, price_usdt, mining_profit, is_purchased, hardware_level)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE price_cns = ?, price_usdt = ?, mining_profit = ?, is_purchased = ?";

$stmt = $conn->prepare($sqlInsertHardware);

// Controlla se priceCNS o priceUSDT sono nulli, e passa NULL se lo sono
if ($priceCNS === null && $priceUSDT === null) {
    // Se i prezzi sono nulli, includi anche hardware_level nella query
    $stmt->bind_param("ssssdisdssss", $userId, $hardwareType, $hardwareName, $priceCNS, $priceUSDT, $miningProfit, $isPurchased, $hardwareLevel, $priceCNS, $priceUSDT, $miningProfit, $isPurchased);
} else {
    // Se i prezzi non sono nulli, li tratti come numeri (double) e includi hardware_level
    $stmt->bind_param("ssssddisdddd", $userId, $hardwareType, $hardwareName, $priceCNS, $priceUSDT, $miningProfit, $isPurchased, $hardwareLevel, $priceCNS, $priceUSDT, $miningProfit, $isPurchased);
}


// Imposta come acquistato
$isPurchased = true;

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Errore durante il salvataggio dell\'hardware.']);
    exit;
}

// Aggiorna il PPD dell'utente
$sqlUpdatePPD = "UPDATE users SET ppd_value = ppd_value + ? WHERE user_id = ?";
$stmt = $conn->prepare($sqlUpdatePPD);
$stmt->bind_param("is", $miningProfit, $userId);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Errore durante l\'aggiornamento del PPD.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Acquisto salvato con successo!']);
$conn->close();
?>
