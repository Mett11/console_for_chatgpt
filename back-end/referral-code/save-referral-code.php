<?php
session_start();
header('Content-Type: application/json');
require_once(__DIR__ . '/../conn.php');
require_once(__DIR__ . '/../verify-token.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$token = getBearerToken();
if (!$token || !verifyAuthorizationHeader($token)) { 
    sendResponse(false, 'Accesso negato: token non valido o mancante');
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;
$referral_code = $data['referral_code'] ?? null;
$referred_by_code = $data['referred_by'] ?? null;

if (!$userId || !$referral_code) {
    sendResponse(false, 'Errore: user_id e referral_code sono obbligatori');
}

// Verifica se l'utente è già stato invitato da qualcuno in passato
$previousRefCheck = $conn->prepare("SELECT referred_by FROM users WHERE user_id = ?");
$previousRefCheck->bind_param("s", $userId);
$previousRefCheck->execute();
$previousRefCheck->bind_result($existingReferredBy);
$previousRefCheck->fetch();
$previousRefCheck->close();

$referred_by_id = null;

// Se c'è un codice referral, cerchiamo l'ID corrispondente
if ($referred_by_code !== null && $existingReferredBy === null) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE ref_code = ?");
    $stmt->bind_param("s", $referred_by_code);
    $stmt->execute();
    $stmt->bind_result($referred_by_id);
    $stmt->fetch();
    $stmt->close();

    if ($referred_by_id === null) {
        sendResponse(false, 'Codice referral non valido: nessun utente trovato');
    }
}

// Controlla quante persone ha già invitato l'utente
$checkInvitedCountSql = "SELECT COUNT(*) FROM referrals WHERE inviter_id = ?";
$checkInvitedCountStmt = $conn->prepare($checkInvitedCountSql);
$checkInvitedCountStmt->bind_param("s", $referred_by_id);
$checkInvitedCountStmt->execute();
$checkInvitedCountStmt->bind_result($invitedCount);
$checkInvitedCountStmt->fetch();
$checkInvitedCountStmt->close();

// Se l'utente ha già invitato 15 persone, impedisci l'invito
if ($invitedCount >= 15) {
    sendResponse(false, 'Hai già raggiunto il numero massimo di 15 invitati.');
}

// Se l'utente è stato invitato e non era già stato registrato come invitato
if ($referred_by_id !== null && $existingReferredBy === null) {
    // Controlla se esiste già una relazione di referral tra i due utenti (in entrambe le direzioni)
    $checkReciprocalSql = "SELECT COUNT(*) FROM referrals WHERE (inviter_id = ? AND invited_id = ?) OR (inviter_id = ? AND invited_id = ?)";
    $checkReciprocalStmt = $conn->prepare($checkReciprocalSql);
    $checkReciprocalStmt->bind_param("ssss", $referred_by_id, $userId, $userId, $referred_by_id);
    $checkReciprocalStmt->execute();
    $checkReciprocalStmt->bind_result($reciprocalCount);
    $checkReciprocalStmt->fetch();
    $checkReciprocalStmt->close();

    if ($reciprocalCount == 0) {
        // Inserisci l'invito nella tabella referrals
        $insertReferralSql = "INSERT INTO referrals (inviter_id, invited_id, created_at) VALUES (?, ?, NOW())";
        $insertReferralStmt = $conn->prepare($insertReferralSql);
        $insertReferralStmt->bind_param("ss", $referred_by_id, $userId);
        if (!$insertReferralStmt->execute()) {
            sendResponse(false, 'Errore durante il salvataggio nella tabella referrals', ['error' => $insertReferralStmt->error]);
        }
        $insertReferralStmt->close();

        // Aggiungi 3000 $CNSL Point al balance dell'invitante
        $updateBalanceSql = "UPDATE users SET balance = balance + 3000 WHERE user_id = ?";
        $updateBalanceStmt = $conn->prepare($updateBalanceSql);
        $updateBalanceStmt->bind_param("s", $referred_by_id);
        if (!$updateBalanceStmt->execute()) {
            sendResponse(false, 'Errore durante l\'aggiornamento del bilancio', ['error' => $updateBalanceStmt->error]);
        }
        $updateBalanceStmt->close();
    }
}

// 🔹 Controllo aggiuntivo: se l'utente ha già invitato qualcuno, non aggiornare il campo referred_by
$checkIfInviterStmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE inviter_id = ?");
$checkIfInviterStmt->bind_param("s", $userId);
$checkIfInviterStmt->execute();
$checkIfInviterStmt->bind_result($isInviterCount);
$checkIfInviterStmt->fetch();
$checkIfInviterStmt->close();

if ($isInviterCount > 0) {
    // L'utente ha già invitato qualcuno: non aggiornare referred_by
    $referred_by_id = null;
}

// Aggiorna i dati dell'utente (ref_code sempre, referred_by solo se è NULL)
$updateUserSql = "UPDATE users SET ref_code = ?, referred_by = IF(referred_by IS NULL, ?, referred_by) WHERE user_id = ?";
$updateUserStmt = $conn->prepare($updateUserSql);
$updateUserStmt->bind_param("sss", $referral_code, $referred_by_id, $userId);

if ($updateUserStmt->execute()) {
    sendResponse(true, 'Codice referral salvato con successo', ['referral_code' => $referral_code]);
} else {
    sendResponse(false, 'Errore durante il salvataggio del codice referral', ['error' => $updateUserStmt->error]);
}

$updateUserStmt->close();
$conn->close();
?>