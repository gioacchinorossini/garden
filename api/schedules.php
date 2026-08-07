<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Always reset mock schedules so title/data changes take effect immediately
$_SESSION['mock_schedules'] = [
    [
        'id' => 1,
        'land_title' => 'Downtown Rooftop Garden',
        'gardener' => 'Mary Gardener',
        'title' => 'Water the tomato plant',
        'description' => 'Review the pressure valve on the rooftop irrigation box and clear blockages in B-1 and B-2 drip lines.',
        'start_time' => '2026-08-08 09:00:00',
        'end_time' => '2026-08-08 11:30:00',
        'task_type' => 'watering',
        'status' => 'scheduled'
    ],
    [
        'id' => 2,
        'land_title' => 'Downtown Rooftop Garden',
        'gardener' => 'Mary Gardener',
        'title' => 'Tomato Seedling Planting',
        'description' => 'Transplant tomato seedlings into Plot A-1 and A-2 organic garden compost beds.',
        'start_time' => '2026-08-09 07:00:00',
        'end_time' => '2026-08-09 10:00:00',
        'task_type' => 'planting',
        'status' => 'scheduled'
    ]
];

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

        $newSched = [
            'id' => count($_SESSION['mock_schedules']) + 1,
            'land_title' => 'Downtown Rooftop Garden',
            'gardener' => 'Mary Gardener',
            'title' => $title,
            'description' => $description,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'task_type' => $task_type,
            'status' => 'scheduled'
        ];

        $_SESSION['mock_schedules'][] = $newSched;

        echo json_encode([
            'status' => 'success',
            'message' => 'Task scheduled successfully!',
            'data' => $newSched
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid API action.'
    ]);
    exit;
}
