<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize schedules session array if not already set, or on explicit reset
if (!isset($_SESSION['mock_schedules']) || isset($_GET['reset'])) {
    $_SESSION['mock_schedules'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'schedules' => $_SESSION['mock_schedules']
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

    if ($action === 'create_schedule') {
        $title = isset($input['title']) ? htmlspecialchars(trim($input['title'])) : '';
        $description = isset($input['description']) ? htmlspecialchars(trim($input['description'])) : '';
        $task_type = isset($input['task_type']) ? htmlspecialchars(trim($input['task_type'])) : 'other';
        $start_time = isset($input['start_time']) ? htmlspecialchars(trim($input['start_time'])) : '';
        $end_time = isset($input['end_time']) ? htmlspecialchars(trim($input['end_time'])) : '';

        if (empty($title) || empty($start_time) || empty($end_time)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $land_title = isset($input['land_title']) && !empty($input['land_title']) ? htmlspecialchars(trim($input['land_title'])) : 'Garden Plot';
        $gardener = isset($input['gardener']) && !empty($input['gardener']) ? htmlspecialchars(trim($input['gardener'])) : ($_SESSION['user_name'] ?? 'Gardener');

        $newSched = [
            'id' => count($_SESSION['mock_schedules']) + 1,
            'land_title' => $land_title,
            'gardener' => $gardener,
            'title' => $title,
            'description' => $description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'task_type' => $task_type,
            'status' => 'pending',
            'xp' => 150
        ];

        $_SESSION['mock_schedules'][] = $newSched;

        echo json_encode([
            'status' => 'success',
            'message' => 'Task scheduled successfully!',
            'data' => $newSched
        ]);
        exit;
    }

    if ($action === 'complete_schedule') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $role = isset($input['role']) ? trim($input['role']) : (isset($_SESSION['active_role']) ? $_SESSION['active_role'] : 'gardener');
        $completed_by = isset($input['completed_by']) ? htmlspecialchars(trim($input['completed_by'])) : '';
        $notes = isset($input['notes']) ? htmlspecialchars(trim($input['notes'])) : '';
        $proof_image = isset($input['proof_image']) ? trim($input['proof_image']) : '';

        // Handle uploaded image file if provided in $_FILES
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/quests/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $ext = 'jpg';
            }
            $filename = 'proof_' . $id . '_' . time() . '.' . $ext;
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target)) {
                $proof_image = 'uploads/quests/' . $filename;
            }
        }

        // Only gardeners upload proof photos; landowners mark complete directly
        if ($role === 'landowner') {
            $proof_image = ''; // Landowners do not upload photos
        } else {
            // Gardener role: strictly enforce photo proof
            if (empty($proof_image)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'To complete this task as a gardener, please attach a photo of your completed work.'
                ]);
                exit;
            }
        }

        if (empty($completed_by)) {
            $completed_by = ($role === 'landowner') ? 'Landowner' : (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Gardener');
        }

        $found = false;
        $updatedItem = null;
        foreach ($_SESSION['mock_schedules'] as &$sched) {
            if ($sched['id'] === $id) {
                $sched['status'] = 'completed';
                $sched['proof_image'] = $proof_image;
                $sched['completed_by'] = $completed_by;
                $sched['completed_at'] = date('Y-m-d H:i:s');
                if (!empty($notes)) {
                    $sched['completion_notes'] = $notes;
                }
                $found = true;
                $updatedItem = $sched;
                break;
            }
        }

        if ($found) {
            $completed_all = array_filter($_SESSION['mock_schedules'], fn($s) => ($s['status'] ?? '') === 'completed');
            $total_pts = 0;
            foreach ($completed_all as $cs) {
                $total_pts += isset($cs['points']) ? intval($cs['points']) : (isset($cs['xp']) ? intval($cs['xp']) : 50);
            }
            $earned_pts = isset($updatedItem['points']) ? intval($updatedItem['points']) : (isset($updatedItem['xp']) ? intval($updatedItem['xp']) : 50);

            echo json_encode([
                'status' => 'success',
                'message' => ($role === 'landowner') 
                    ? "Task verified and marked complete! +{$earned_pts} Stewardship Points recorded." 
                    : "Task completed and proof submitted successfully! +{$earned_pts} Stewardship Points recorded.",
                'data' => $updatedItem,
                'gamification' => [
                    'earned_points' => $earned_pts,
                    'total_points' => $total_pts,
                    'completed_count' => count($completed_all)
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Task not found.'
            ]);
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
