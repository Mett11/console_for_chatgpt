<?php
header('Content-Type: application/json');
require_once '../conn.php';
require_once '../verify-token.php';

$token = getBearerToken();
if (!$token || !verifyAuthorizationHeader($token)) { 
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['task_id'])) {
    echo json_encode(["success" => false, "message" => "Missing user_id or task_id"]);
    exit;
}

$user_id = intval($data['user_id']);
$task_id = intval($data['task_id']);

$query = "SELECT t.type, t.required_level, t.claim_point FROM task t WHERE t.id_task = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Task not found"]);
    exit;
}

$task = $result->fetch_assoc();

$query_user_level = "SELECT user_level FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($query_user_level);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$user = $user_result->fetch_assoc();

if ($task['type'] === 'game' && $user['user_level'] < $task['required_level']) {
    echo json_encode(["success" => false, "message" => "Level not reached"]);
    exit;
}

if ($task['type'] !== 'partner') {
    $query_check = "SELECT * FROM usertask WHERE user_id = ? AND task_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("ii", $user_id, $task_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Task already completed"]);
        exit;
    }
}

if ($task['type'] === 'partner') {
    $daily_limit = 50;
    $today = date('Y-m-d');

    // Verifica se esiste già un record per la coppia user_id-task_id
    $query_check = "SELECT claim_count, data_claim FROM usertask WHERE user_id = ? AND task_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("ii", $user_id, $task_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        // Se il record è aggiornato a oggi
        if ($row['data_claim'] == $today) {
            if ($row['claim_count'] >= $daily_limit) {
                echo json_encode(["success" => false, "message" => "Hai raggiunto il limite giornaliero di task partner."]);
                exit;
            } else {
                // Incrementa il contatore per oggi
                $query_update = "UPDATE usertask SET claim_count = claim_count + 1, data_claim = ? WHERE user_id = ? AND task_id = ?";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param("sii", $today, $user_id, $task_id);
                $stmt_update->execute();
                if ($stmt_update->affected_rows > 0) {
                    echo json_encode(["success" => true, "message" => "Task completed successfully"]);
                } else {
                    error_log("Errore durante l'aggiornamento: " . $stmt_update->error);
                    echo json_encode(["success" => false, "message" => "Error updating task", "error" => $stmt_update->error]);
                }
                exit;
            }
        } else {
            // Il record esiste, ma la data non è oggi: resetta il contatore a 1 e aggiorna la data
            $query_update = "UPDATE usertask SET claim_count = 1, data_claim = ? WHERE user_id = ? AND task_id = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param("sii", $today, $user_id, $task_id);
            $stmt_update->execute();
            if ($stmt_update->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Task completed successfully"]);
            } else {
                error_log("Errore durante il reset: " . $stmt_update->error);
                echo json_encode(["success" => false, "message" => "Error resetting task", "error" => $stmt_update->error]);
            }
            exit;
        }
    } else {
        // Nessun record esistente: inserisci una nuova riga con claim_count = 1
        $query_insert = "INSERT INTO usertask (user_id, task_id, data_claim, claim_count) VALUES (?, ?, ?, 1)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iis", $user_id, $task_id, $today);
        $stmt_insert->execute();
        if ($stmt_insert->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Task completed successfully"]);
        } else {
            error_log("Errore durante l'inserimento: " . $stmt_insert->error);
            echo json_encode(["success" => false, "message" => "Error completing task", "error" => $stmt_insert->error]);
        }
        exit;
    }
}

$query_insert_update = "INSERT INTO usertask (user_id, task_id, completato, data_claim) VALUES (?, ?, 1, CURDATE())
                        ON DUPLICATE KEY UPDATE completato = 1, data_claim = CURDATE()";
$stmt_insert_update = $conn->prepare($query_insert_update);
$stmt_insert_update->bind_param("ii", $user_id, $task_id);
$stmt_insert_update->execute();

if ($stmt_insert_update->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Task completed successfully"]);
} else {
    error_log("Errore durante l'inserimento/aggiornamento: " . $stmt_insert_update->error);
    echo json_encode(["success" => false, "message" => "Error completing task", "error" => $stmt_insert_update->error]);
}

$conn->close();
?>
