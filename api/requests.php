<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $requests = get_all_requests($pdo);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'requests' => $requests
        ]
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = isset($input['action']) ? $input['action'] : '';

    if ($action === 'submit_request') {
        $land_id = isset($input['land_id']) ? intval($input['land_id']) : 0;
        $plot_id = isset($input['plot_id']) ? intval($input['plot_id']) : 0;
        $purpose = isset($input['purpose']) ? htmlspecialchars(trim($input['purpose'])) : '';
        $duration = isset($input['duration']) ? htmlspecialchars(trim($input['duration'])) : '6 months';

        if (!$land_id || !$plot_id || empty($purpose)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        // Retrieve land title
        $stmtLand = $pdo->prepare("SELECT title FROM `lands` WHERE id = ?");
        $stmtLand->execute([$land_id]);
        $land_title = $stmtLand->fetchColumn() ?: "Community Garden";

        // Retrieve plot number
        $stmtPlot = $pdo->prepare("SELECT plot_number FROM `plots` WHERE id = ?");
        $stmtPlot->execute([$plot_id]);
        $plot_num = $stmtPlot->fetchColumn() ?: "Plot";

        $gardener_id = $_SESSION['user_id'] ?? 3;
        $gardener_name = $_SESSION['user_name'] ?? 'Mary Gardener';

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `requests` 
                (gardener_id, gardener_name, land_id, plot_id, land_title, plot_number, purpose, message, requested_duration, status)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $gardener_id, $gardener_name, $land_id, $plot_id,
                $land_title, $plot_num, $purpose, $purpose, $duration
            ]);
            $new_id = (int)$pdo->lastInsertId();

            $newRequest = [
                'id' => $new_id,
                'land_id' => $land_id,
                'plot_id' => $plot_id,
                'gardener' => $gardener_name,
                'land_title' => $land_title,
                'plot_num' => $plot_num,
                'purpose' => $purpose,
                'duration' => $duration,
                'status' => 'pending',
                'notes' => ''
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Plot application submitted successfully! Tracking is available under Requests.',
                'data' => $newRequest
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'update_status') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $status = isset($input['status']) ? htmlspecialchars(trim($input['status'])) : '';
        $notes = isset($input['notes']) ? htmlspecialchars(trim($input['notes'])) : '';

        if (!$id || !in_array($status, ['approved', 'rejected'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action parameters.'
            ]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE `requests` 
                SET `status` = ?, `response_notes` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$status, $notes, $id]);

            // If approved, update plot to occupied
            if ($status === 'approved') {
                $stmtReq = $pdo->prepare("SELECT plot_id, gardener_name FROM `requests` WHERE id = ?");
                $stmtReq->execute([$id]);
                $reqData = $stmtReq->fetch(PDO::FETCH_ASSOC);
                if ($reqData && !empty($reqData['plot_id'])) {
                    $pdo->prepare("UPDATE `plots` SET `status` = 'occupied', `farmer_name` = ? WHERE `id` = ?")
                        ->execute([$reqData['gardener_name'], $reqData['plot_id']]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Request status updated successfully!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid API action.'
    ]);
    exit;
}
