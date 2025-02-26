<?php
session_start();
header('Content-Type: application/json');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/verify-token.php');

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Controlla se l'ID utente è passato come parametro
if (isset($data['user_id'])) {
    $userId = $data['user_id'];

    // Prepara la query per ottenere i dati utente
    $sql_user = "SELECT user_id, balance, user_level, next_level_points, ppd_value FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    
    if ($stmt_user === false) {
        echo json_encode([
            'error' => 'Query preparation failed for users',
            'mysqli_error' => $conn->error, // Mostra l'errore del database
            'sql' => $sql_user // Mostra la query per verificare eventuali errori
        ]);
        exit;
    }
    

    $stmt_user->bind_param("i", $userId);
    if (!$stmt_user->execute()) {
        echo json_encode([
            'error' => 'Query execution failed for users',
            'mysqli_error' => $stmt_user->error,
            'mysqli_errno' => $stmt_user->errno,
        ]);
        exit;
    }

    $result_user = $stmt_user->get_result();

    // Dati utente
    if ($result_user->num_rows > 0) {
        $user_data = $result_user->fetch_assoc();
        $_SESSION['balance'] = $user_data['balance'];
        $_SESSION['ppd_value'] = $user_data['ppd_value'];
        // Recupera i dettagli del countdown, se presente
        $sql_countdown = "SELECT start_time, end_time, status_countdown FROM countdown WHERE user_id = ? ORDER BY id DESC LIMIT 1";
        $stmt_countdown = $conn->prepare($sql_countdown);

        if ($stmt_countdown === false) {
            echo json_encode([
                'error' => 'Countdown query preparation failed',
                'mysqli_error' => $conn->error,
                'sql' => $sql_countdown
            ]);
            exit;
        }
        

        $stmt_countdown->bind_param("s", $userId);
        if (!$stmt_countdown->execute()) {
            echo json_encode([
                'error' => 'Countdown query execution failed',
                'mysqli_error' => $stmt_countdown->error,
                'mysqli_errno' => $stmt_countdown->errno,
            ]);
            exit;
        }

        $result_countdown = $stmt_countdown->get_result();

        // Aggiungi i dati del countdown all'array di risposta
        if ($result_countdown->num_rows > 0) {
            $countdown_data = $result_countdown->fetch_assoc();

            // Verifica lo stato del countdown
            if ($countdown_data['status_countdown'] == 'in corso') {
                $current_time = new DateTime();
                $end_time = new DateTime($countdown_data['end_time']);

                // Aggiungi il tempo di fine countdown al risultato
                $user_data['countdown_end_time'] = $end_time->format(DateTime::ISO8601);
            } else {
                // Se il countdown non è in corso, ritorna null
                $user_data['countdown_end_time'] = null;
            }
        } else {
            // Se non esiste un countdown, ritorna null
            $user_data['countdown_end_time'] = null;
        }

        // Restituisci tutti i dati come JSON
        echo json_encode($user_data);

    } else {
        echo json_encode(['error' => 'No user data found']);
    }

    $stmt_user->close();
    $stmt_countdown->close();
} else {
    echo json_encode(['error' => 'Invalid or missing user ID']);
}

$conn->close();
?>
