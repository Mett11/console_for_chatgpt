<?php
// process_usdt_payment.php
header('Content-Type: application/json');
require_once '../conn.php';

// Recupera i parametri POST inviati dal front-end
$userId   = $_POST['userId'] ?? null;
$itemData = $_POST['itemData'] ?? null;
$typeHW   = $_POST['typeHW'] ?? null;
$txHash   = $_POST['txHash'] ?? null; // eventuale hash della transazione (opzionale)

if (!$userId || !$itemData || !$typeHW) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Decodifica la stringa JSON con i dettagli dell'item
$itemData = json_decode($itemData, true);
if (!$itemData) {
    echo json_encode(['success' => false, 'message' => 'Invalid item data']);
    exit;
}

/*
  Mappatura dei dati:
  - hardware_type    : verrà valorizzato con il parametro $typeHW (ad es. "CPU", "RAM", etc.)
  - hardware_name    : viene preso dal campo "name" dell'item
  - price_cns        : dal campo "price_cns" (in questo caso probabilmente null)
  - price_usdt       : dal campo "price_usdt"
  - mining_profit    : dal campo "mining_profit_cns" (che indica il profitto di mining per quel livello)
  - hardware_level   : dal campo "level"
*/

$hardware_type  = $typeHW;
$hardware_name  = $itemData['name'];
$price_cns      = $itemData['price_cns'];    // probabilmente null se si acquista in USDT
$price_usdt     = $itemData['price_usdt'];
$mining_profit  = $itemData['mining_profit_cns'];
$hardware_level = $itemData['level'];

// Stabilisco la connessione al database

// Inserisco l'acquisto nella tabella "user_hardware"
$stmt = $conn->prepare("INSERT INTO user_hardware (hardware_type, hardware_name, price_cns, price_usdt, mining_profit, purchased_at, is_purchased, user_id, hardware_level) VALUES (?, ?, ?, ?, ?, NOW(), 1, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

// Specifica i tipi per i parametri (s = string, d = double/integer, i = integer)
// In questo esempio assumiamo che price_cns e price_usdt siano numerici (se price_cns è null va bene, verrà inserito come NULL)
// Il binding qui è: 
//   1) hardware_type (string)
//   2) hardware_name (string)
//   3) price_cns (double oppure NULL)
//   4) price_usdt (double)
//   5) mining_profit (double/integer)
//   6) user_id (integer)
//   7) hardware_level (integer)
$stmt->bind_param("ssddiis", $hardware_type, $hardware_name, $price_cns, $price_usdt, $mining_profit, $userId, $hardware_level);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Database execution error: ' . $stmt->error]);
    exit;
}

// Aggiorna il valore ppd_value dell'utente nella tabella "users"
// In questo caso incrementiamo ppd_value con il valore di mining_profit del componente appena acquistato.
$stmt2 = $conn->prepare("UPDATE users SET ppd_value = ppd_value + ? WHERE user_id = ?");
if ($stmt2) {
    $stmt2->bind_param("di", $mining_profit, $userId);
    $stmt2->execute();
}

echo json_encode(['success' => true, 'message' => 'Purchase registered successfully']);
exit;
?>
