<?php

session_start();
// Impostiamo il tipo di contenuto come JSON
header('Content-Type: application/json');
require_once(__DIR__ . '/../conn.php');
require_once(__DIR__ . '/../verify-token.php'); // Includi il file che contiene la funzione per verificare il token

// Verifica del token
$token = getBearerToken(); // Recupera il token dalla richiesta
if (!$token || !verifyAuthorizationHeader($token)) { // Usa la funzione per verificare il token
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

// Impostiamo il fuso orario
date_default_timezone_set('Europe/Rome');


// Funzione per ottenere la data odierna
function getToday() {
    return date("Y-m-d");
}

// Funzione per gestire il claim giornaliero
function checkAndClaim($user_id) {
    global $conn;
    $current_time = date("Y-m-d H:i:s"); // Data e ora attuali

    // Otteniamo le informazioni sull'utente
    $query = "SELECT * FROM user_daily_check WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Utente non esiste, creiamo una nuova entry
        $consecutive_days = 1;
        $points = 50;
        $stmt = $conn->prepare("INSERT INTO user_daily_check (user_id, last_claim_date, consecutive_days, points) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $user_id, $current_time, $consecutive_days, $points);
        $stmt->execute();
        return ["message" => "Claim effettuato", "points" => 50];
    } else {
        // Utente esiste, controlliamo se ha già fatto il claim oggi
        $row = $result->fetch_assoc();
        $last_claim_time = $row['last_claim_date']; // Data e ora dell'ultimo claim
        $consecutive_days = $row['consecutive_days'];
        $points = $row['points'];

        // Verifica se il claim è già stato fatto entro le ultime 24 ore
        $next_claim_time = strtotime($last_claim_time) + (24 * 3600); // Aggiungi 24 ore al momento dell'ultimo claim
        $time_remaining = $next_claim_time - time(); // Differenza tra ora attuale e prossimo claim

        if ($time_remaining > 0) {
            // Claim già effettuato, restituiamo il tempo rimanente
            return [
                "message" => "Hai già fatto il claim oggi",
                "points" => $points,
                "time_remaining" => gmdate("H:i:s", $time_remaining),
                "consecutive_days" => $consecutive_days
            ];
        }

                // Confronta il giorno precedente con la data attuale
        $last_claim_day = date("Y-m-d", strtotime($last_claim_time)); // Data dell'ultimo claim
        $yesterday = date("Y-m-d", strtotime("-1 day")); // Data di ieri

        if ($last_claim_day === $yesterday) {
            // Claim consecutivo
            $consecutive_days++;
        } else {
            // Claim non consecutivo (giorno diverso o più di un giorno fa)
            $consecutive_days = 1;
        }


        // Calcolo dei punti in base ai giorni consecutivi
        switch ($consecutive_days) {
            case 1:
                $new_points = 50;
                break;
            case 2:
                $new_points = 100;
                break;
            case 3:
                $new_points = 150;
                break;
            case 4:
                $new_points = 200;
                break;
            case 5:
                $new_points = 250;
                break;
            case 6:
                $new_points = 300;
                break;
            case 7:
                $new_points = 400;
                break;
            default:
                $new_points = 50; // Punti base dopo il ciclo di 7 giorni
                break;
        }

        // Aggiornamento dei dati nel DB
        $stmt = $conn->prepare("UPDATE user_daily_check SET last_claim_date = ?, consecutive_days = ?, points = ? WHERE user_id = ?");
        $stmt->bind_param("siis", $current_time, $consecutive_days, $new_points, $user_id);
        $stmt->execute();

        return ["message" => "Claim effettuato", "points" => $new_points, "consecutive_days" => $consecutive_days];
    }
}

$data = json_decode(file_get_contents('php://input'), true);


// Supponiamo che l'ID dell'utente sia passato tramite sessione
$user_id = $data['user_id']; // Questo dovrebbe essere già definito
$response = checkAndClaim($user_id);

// Restituiamo la risposta in formato JSON
echo json_encode($response);

?>
