<?php
session_start();
header('Content-Type: application/json');
require_once '../conn.php';
require_once '../verify-token.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    sendResponse(false, 'Accesso negato: token non valido o mancante');
}

if (isset($_SESSION['my_session_userid'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'];
    $referral_code = $data['referral_code'];
    $referred_by = isset($data['referred_by']) ? $data['referred_by'] : null;

    // Assicurati che referred_by sia l'ID utente e non un codice referral
    $referred_by_id = null;
    if ($referred_by !== null) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE ref_code = ?");
        $stmt->bind_param("s", $referred_by);
        $stmt->execute();
        $stmt->bind_result($referred_by_id);
        $stmt->fetch();
        $stmt->close();

        // Se non esiste alcun utente con quel codice referral
        if ($referred_by_id === null) {
            sendResponse(false, 'Codice referral non valido: nessun utente trovato');
        }
    }

    // Verifica se l'utente è già stato invitato
    if ($referred_by_id !== null) {
        $checkReferralSql = "SELECT COUNT(*) FROM referrals WHERE inviter_id = ? AND invited_id = ?";
        $checkReferralStmt = $conn->prepare($checkReferralSql);
        $checkReferralStmt->bind_param("ss", $referred_by_id, $userId);
        $checkReferralStmt->execute();
        $checkReferralStmt->bind_result($referralCount);
        $checkReferralStmt->fetch();
        $checkReferralStmt->close();

        if ($referralCount == 0) {
            // Inserisci nella tabella referrals
            $referralsSql = "INSERT INTO referrals (inviter_id, invited_id, created_at) VALUES (?, ?, NOW())";
            $referralsStmt = $conn->prepare($referralsSql);
            $referralsStmt->bind_param("ss", $referred_by_id, $userId);
            if (!$referralsStmt->execute()) {
                sendResponse(false, 'Errore durante il salvataggio nella tabella referrals', ['error' => $referralsStmt->error]);
            }
            $referralsStmt->close();

            // Aggiungi 1000 $CNSL Point al bilancio dell'utente invitante
            $updateBalanceSql = "UPDATE users SET balance = balance + 1000 WHERE user_id = ?";
            $updateBalanceStmt = $conn->prepare($updateBalanceSql);
            $updateBalanceStmt->bind_param("s", $referred_by_id);
            if (!$updateBalanceStmt->execute()) {
                sendResponse(false, 'Errore durante l\'aggiornamento del bilancio', ['error' => $updateBalanceStmt->error]);
            }
            $updateBalanceStmt->close();
        }
    }

    // Aggiorna la tabella users solo se referred_by è null
    $sql = "UPDATE users SET ref_code = ?, referred_by = IF(referred_by IS NULL, ?, referred_by) WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $referral_code, $referred_by_id, $userId);

    if ($stmt->execute()) {
        sendResponse(true, 'Codice referral salvato con successo', ['referral_code' => $referral_code]);
    } else {
        sendResponse(false, 'Errore durante il salvataggio del codice referral', ['error' => $stmt->error]);
    }

    $stmt->close();
} else {
    sendResponse(false, 'Errore: utente non identificato');
}

$conn->close();
?>
