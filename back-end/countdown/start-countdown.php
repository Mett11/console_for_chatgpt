<?php
// Includi il file di connessione
include('../conn.php');
require_once '../verify-token.php'; // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);

// Verifica se il user_id è passato via POST
if (isset($data['user_id'])) {
    $user_id = $data['user_id'];

    // Controlla se esiste un countdown attivo per l'utente
    $sql_check = "SELECT * FROM countdown WHERE user_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // Se c'è un countdown attivo o inattivo, aggiorna i tempi
    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $countdown_id = $row['id'];
        $status = $row['status_countdown'];
        $end_time = strtotime($row['end_time']);

        // Se il countdown è ancora attivo, non fare nulla
        if ($status == 'in corso' && $end_time > time()) {
            echo json_encode(['success' => false, 'message' => 'Un countdown è già in corso.']);
            exit;
        }

        // Calcola il nuovo tempo di scadenza (24 ore in avanti)
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Prepara la query per aggiornare il countdown
        $stmt_update = $conn->prepare("UPDATE countdown SET start_time = ?, end_time = ?, status_countdown = 'in corso' WHERE id = ?");
        $stmt_update->bind_param("ssi", $start_time, $end_time, $countdown_id);

        if ($stmt_update->execute()) {
            echo json_encode([
                'success' => true,
                'end_time' => $end_time
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore nell\'aggiornamento del countdown.']);
        }

        $stmt_update->close();
    } else {
        // Se non esiste un countdown, inserisci un nuovo countdown
        $start_time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Prepara la query per inserire il countdown
        $stmt_insert = $conn->prepare("INSERT INTO countdown (user_id, start_time, end_time, status_countdown) VALUES (?, ?, ?, 'in corso')");
        $stmt_insert->bind_param("sss", $user_id, $start_time, $end_time);

        if ($stmt_insert->execute()) {
            echo json_encode([
                'success' => true,
                'end_time' => $end_time
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore nell\'inserimento del countdown.']);
        }

        $stmt_insert->close();
    }

    $stmt_check->close();
} else {
    echo json_encode(['success' => false, 'message' => 'user_id non fornito']);
}

$conn->close();
?>
