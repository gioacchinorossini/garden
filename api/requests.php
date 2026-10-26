<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $requests = get_all_applications($pdo);

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

    if ($action === 'submit_request' || $action === 'submit_application') {
        $property_id = isset($input['property_id']) ? intval($input['property_id']) : (isset($input['land_id']) ? intval($input['land_id']) : 0);
        $stall_id = isset($input['stall_id']) ? intval($input['stall_id']) : (isset($input['plot_id']) ? intval($input['plot_id']) : 0);
        $food_concept = isset($input['food_concept']) ? htmlspecialchars(trim($input['food_concept'])) : (isset($input['purpose']) ? htmlspecialchars(trim($input['purpose'])) : '');
        $duration = isset($input['duration']) ? htmlspecialchars(trim($input['duration'])) : (isset($input['requested_duration']) ? htmlspecialchars(trim($input['requested_duration'])) : '3 months');

        if (!$property_id || empty($food_concept)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $vendor_id = $_SESSION['user_id'] ?? 3;
        $vendor_name = $_SESSION['user_name'] ?? 'Maria Street Vendor';

        try {
            // Retrieve property title
            $stmtProp = $pdo->prepare("SELECT title FROM `properties` WHERE id = ?");
            $stmtProp->execute([$property_id]);
            $property_title = $stmtProp->fetchColumn() ?: "Food Space";

            // Retrieve stall number
            $stall_num = "Any Stall";
            if ($stall_id > 0) {
                $stmtStall = $pdo->prepare("SELECT stall_number FROM `stalls` WHERE id = ?");
                $stmtStall->execute([$stall_id]);
                $stall_num = $stmtStall->fetchColumn() ?: "Stall";
            }

            $stmt = $pdo->prepare("
                INSERT INTO `stall_applications` 
                (vendor_id, property_id, stall_id, food_concept, requested_duration, status)
                VALUES 
                (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $vendor_id, $property_id, ($stall_id > 0 ? $stall_id : null),
                $food_concept, $duration
            ]);
            $new_id = (int)$pdo->lastInsertId();

            $newApplication = [
                'id' => $new_id,
                'property_id' => $property_id,
                'stall_id' => $stall_id,
                'gardener' => $vendor_name,
                'vendor_name' => $vendor_name,
                'land_title' => $property_title,
                'property_title' => $property_title,
                'plot_num' => $stall_num,
                'stall_number' => $stall_num,
                'purpose' => $food_concept,
                'food_concept' => $food_concept,
                'duration' => $duration,
                'status' => 'pending',
                'notes' => ''
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Stall rental application submitted successfully! Tracking is available under Applications.',
                'data' => $newApplication
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
                UPDATE `stall_applications` 
                SET `status` = ?, `response_notes` = ?, `responded_at` = NOW()
                WHERE `id` = ?
            ");
            $stmt->execute([$status, $notes, $id]);

            // If approved, update stall to occupied
            if ($status === 'approved') {
                $stmtReq = $pdo->prepare("SELECT stall_id FROM `stall_applications` WHERE id = ?");
                $stmtReq->execute([$id]);
                $stall_id = $stmtReq->fetchColumn();
                if (!empty($stall_id)) {
                    $pdo->prepare("UPDATE `stalls` SET `status` = 'occupied' WHERE `id` = ?")
                        ->execute([$stall_id]);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Application status updated successfully!'
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
