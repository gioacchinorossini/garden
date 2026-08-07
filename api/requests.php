<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure mock collections exist
if (!isset($_SESSION['mock_requests'])) {
    $_SESSION['mock_requests'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'requests' => $_SESSION['mock_requests']
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
        $duration = isset($input['duration']) ? htmlspecialchars(trim($input['duration'])) : '';

        if (!$land_id || !$plot_id || empty($purpose) || empty($duration)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        // Retrieve land title
        $land_title = "Unknown Land";
        if (isset($_SESSION['mock_lands'])) {
            foreach ($_SESSION['mock_lands'] as $land) {
                if ($land['id'] === $land_id) {
                    $land_title = $land['title'];
                    break;
                }
            }
        }

        // Retrieve plot number
        $plot_num = "Any Plot";
        if (isset($_SESSION['mock_plots'])) {
            foreach ($_SESSION['mock_plots'] as $plot) {
                if ($plot['id'] === $plot_id) {
                    $plot_num = $plot['plot_number'];
                    break;
                }
            }
        }

        $newRequest = [
            'id' => count($_SESSION['mock_requests']) + 1,
            'gardener' => $_SESSION['user_name'] ?? 'Mary Gardener',
            'email' => 'gardener@garden.com',
            'phone' => '09333456789',
            'land_title' => $land_title,
            'plot_num' => $plot_num,
            'purpose' => $purpose,
            'duration' => $duration,
            'status' => 'pending',
            'notes' => ''
        ];

        $_SESSION['mock_requests'][] = $newRequest;

        echo json_encode([
            'status' => 'success',
            'message' => 'Plot application submitted successfully! Tracking is available under Requests.',
            'data' => $newRequest
        ]);
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

        $found = false;
        foreach ($_SESSION['mock_requests'] as &$req) {
            if ($req['id'] === $id) {
                $req['status'] = $status;
                $req['notes'] = $notes;
                $found = true;
                break;
            }
        }

        if ($found) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Request status updated successfully!'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Request not found.'
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
