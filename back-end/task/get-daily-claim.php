<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../conn.php');
require_once(__DIR__ . '/../verify-token.php');

$token = getBearerToken(); 
if (!$token || !verifyAuthorizationHeader($token)) { 
    echo json_encode(['error' => 'Invalid or missing token']);
    exit;
}

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$taskType = isset($_GET['task_type']) ? $_GET['task_type'] : "";
$taskId = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

$today = date('Y-m-d');

if ($taskType === 'partner') {
    $query = "SELECT claim_count, data_claim FROM usertask WHERE user_id = ? AND task_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('ii', $userId, $taskId);
        $stmt->execute();
        $result = $stmt->get_result();
        $claimCount = 0;
        if ($data = $result->fetch_assoc()) {
            // Se l'ultimo claim è di oggi, ritorna il conteggio, altrimenti 0
            if ($data['data_claim'] == $today) {
                $claimCount = $data['claim_count'];
            } else {
                $claimCount = 0;
            }
        }
        $stmt->close();
        echo json_encode(['success' => true, 'claimCount' => $claimCount]);
    } else {
        echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => true, 'claimCount' => 0]);
}

$conn->close();
?>
