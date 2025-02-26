<?php
// check-countdown-status.php

require_once '../conn.php';  // Connessione al database


// Verifica se è stato passato l'user_id
if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']); // Sanifica l'input

    // Query per ottenere lo stato del countdown
    $query = "SELECT status_countdown FROM countdown WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        // Associa il parametro user_id
        $stmt->bind_param("s", $user_id);

        // Esegui la query
        $stmt->execute();

        // Ottieni il risultato
        $stmt->store_result();
        $stmt->bind_result($status_countdown);

        if ($stmt->fetch()) {
            // Restituisce lo stato del countdown in formato JSON
            echo json_encode(['status_countdown' => $status_countdown]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }

        // Chiudi lo statement
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Database query failed']);
    }
} else {
    echo json_encode(['error' => 'User ID missing']);
}
?>
