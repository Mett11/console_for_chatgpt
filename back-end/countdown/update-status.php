<?php
// update-status.php

require_once '../conn.php';  // Connessione al database
header('Content-Type: application/json');

// Verifica se è stato passato l'user_id
if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']); // Sanifica l'input

    // Query per aggiornare lo stato del countdown
    $query = "UPDATE countdown SET status_countdown = 'inattivo' WHERE user_id = ? AND status_countdown = 'in corso'";

    if ($stmt = $conn->prepare($query)) {
        // Associa il parametro user_id
        $stmt->bind_param("s", $user_id);

        // Esegui la query
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Errore nell\'aggiornamento dello stato']);
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
