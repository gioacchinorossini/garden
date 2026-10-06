<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize mock schedules if not already set, or on explicit reset
if (!isset($_SESSION['mock_schedules']) || isset($_GET['reset'])) {
    $today = date('Y-m-d');
    $_SESSION['mock_schedules'] = [
    [
        'id'          => 1,
        'land_title'  => 'Plot A – North Rooftop',
        'gardener'    => 'Maria Santos',
        'title'       => 'Morning Irrigation Round',
        'description' => 'Water all raised beds in sections B-1 and B-2. Check drip-line pressure valves and clear any blockages before starting.',
        'start_time'  => $today . ' 06:00:00',
        'end_time'    => $today . ' 07:30:00',
        'task_type'   => 'watering',
        'status'      => 'in_progress',
        'difficulty'  => 'easy',
        'xp'          => 120,
    ],
    [
        'id'          => 2,
        'land_title'  => 'Plot B – Greenhouse Bay',
        'gardener'    => 'Juan dela Cruz',
        'title'       => 'Seedling Transplant – Cherry Tomatoes',
        'description' => 'Move cherry tomato seedlings from nursery trays into prepared compost beds. Spacing: 40 cm apart. Water gently after planting.',
        'start_time'  => date('Y-m-d', strtotime('+1 day')) . ' 08:00:00',
        'end_time'    => date('Y-m-d', strtotime('+1 day')) . ' 10:30:00',
        'task_type'   => 'planting',
        'status'      => 'pending',
        'difficulty'  => 'medium',
        'xp'          => 250,
    ],
    [
        'id'          => 3,
        'land_title'  => 'Plot C – East Strip',
        'gardener'    => null,
        'title'       => 'Weed Clearing – East Fence Line',
        'description' => 'Remove aggressive weed growth along the eastern boundary. Bag all cuttings and transport to compost bin.',
        'start_time'  => date('Y-m-d', strtotime('+2 days')) . ' 07:00:00',
        'end_time'    => date('Y-m-d', strtotime('+2 days')) . ' 09:00:00',
        'task_type'   => 'weeding',
        'status'      => 'pending',
        'difficulty'  => 'medium',
        'xp'          => 180,
    ],
    [
        'id'          => 4,
        'land_title'  => 'Plot D – South Cornfield',
        'gardener'    => 'Ana Reyes',
        'title'       => 'Corn Harvest – Rows 5–12',
        'description' => 'Harvest mature corn ears from rows 5 through 12. Sort by size: large to storage shed, medium to market bin.',
        'start_time'  => date('Y-m-d', strtotime('+3 days')) . ' 05:30:00',
        'end_time'    => date('Y-m-d', strtotime('+3 days')) . ' 09:00:00',
        'task_type'   => 'harvesting',
        'status'      => 'pending',
        'difficulty'  => 'hard',
        'xp'          => 400,
    ],
    [
        'id'          => 5,
        'land_title'  => 'Plot A – North Rooftop',
        'gardener'    => 'Maria Santos',
        'title'       => 'Woodchip & Straw Mulching',
        'description' => 'Spread a 3-inch protective mulch layer around tomato and pepper bases to retain moisture and regulate root temperature.',
        'start_time'  => date('Y-m-d', strtotime('+1 day')) . ' 09:00:00',
        'end_time'    => date('Y-m-d', strtotime('+1 day')) . ' 11:00:00',
        'task_type'   => 'mulching',
        'status'      => 'pending',
        'difficulty'  => 'easy',
        'xp'          => 150,
    ],
    [
        'id'          => 6,
        'land_title'  => 'Plot E – Orchard Row',
        'gardener'    => null,
        'title'       => 'Fruit Tree & Shrub Pruning',
        'description' => 'Shape and prune young fruit trees. Remove crossing branches and any dead wood. Seal cuts with clean pruning paste.',
        'start_time'  => date('Y-m-d', strtotime('+5 days')) . ' 07:00:00',
        'end_time'    => date('Y-m-d', strtotime('+5 days')) . ' 10:00:00',
        'task_type'   => 'pruning',
        'status'      => 'pending',
        'difficulty'  => 'hard',
        'xp'          => 220,
    ],
    [
        'id'          => 7,
        'land_title'  => 'Plot B – Greenhouse Bay',
        'gardener'    => 'Carlos Mendoza',
        'title'       => 'Climbing Vine & Trellis Setup',
        'description' => 'Install wooden lattice trellises for indeterminate tomato and pole bean rows. Tie main leaders with soft garden twine.',
        'start_time'  => date('Y-m-d', strtotime('+2 days')) . ' 08:00:00',
        'end_time'    => date('Y-m-d', strtotime('+2 days')) . ' 10:30:00',
        'task_type'   => 'trellising',
        'status'      => 'pending',
        'difficulty'  => 'medium',
        'xp'          => 210,
    ],
    [
        'id'          => 8,
        'land_title'  => 'Plot C – East Strip',
        'gardener'    => 'Juan dela Cruz',
        'title'       => 'Organic Compost Application',
        'description' => 'Apply compost mix to all raised beds in Plot C. Ratio: 2 kg per sq. meter. Work gently into top 5 cm of soil.',
        'start_time'  => date('Y-m-d', strtotime('-1 day')) . ' 09:00:00',
        'end_time'    => date('Y-m-d', strtotime('-1 day')) . ' 11:00:00',
        'task_type'   => 'fertilizing',
        'status'      => 'completed',
        'difficulty'  => 'medium',
        'xp'          => 300,
    ],
    [
        'id'          => 9,
        'land_title'  => 'Plot D – South Cornfield',
        'gardener'    => 'Ana Reyes',
        'title'       => 'Leaf Inspection & Pest Scouting',
        'description' => 'Check undersides of squash and bean foliage with magnifying glass for aphid colonies or mites. Release beneficial ladybugs.',
        'start_time'  => date('Y-m-d', strtotime('+3 days')) . ' 08:00:00',
        'end_time'    => date('Y-m-d', strtotime('+3 days')) . ' 09:30:00',
        'task_type'   => 'pests',
        'status'      => 'pending',
        'difficulty'  => 'easy',
        'xp'          => 140,
    ],
    [
        'id'          => 10,
        'land_title'  => 'Plot E – Tool Shed Bay',
        'gardener'    => 'Carlos Mendoza',
        'title'       => 'Tool Sanitization & Maintenance',
        'description' => 'Clean, disinfect, and sharpen trowels, hoes, and secateurs. Apply protective mineral oil coat to prevent rust.',
        'start_time'  => date('Y-m-d', strtotime('-2 days')) . ' 14:00:00',
        'end_time'    => date('Y-m-d', strtotime('-2 days')) . ' 15:30:00',
        'task_type'   => 'cleaning',
        'status'      => 'completed',
        'difficulty'  => 'easy',
        'xp'          => 100,
    ],
];
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

        $newSched = [
            'id' => count($_SESSION['mock_schedules']) + 1,
            'land_title' => 'Plot A – North Rooftop',
            'gardener' => 'Mary Gardener',
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
            $completed_by = ($role === 'landowner') ? 'Landowner' : (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Mary Gardener');
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
            $total_xp = array_sum(array_column($completed_all, 'xp'));
            $earned_xp = $updatedItem['xp'] ?? 100;

            echo json_encode([
                'status' => 'success',
                'message' => ($role === 'landowner') 
                    ? "Task verified and marked completed! +{$earned_xp} XP earned." 
                    : "Task completed and proof submitted successfully! +{$earned_xp} XP earned.",
                'data' => $updatedItem,
                'gamification' => [
                    'earned_xp' => $earned_xp,
                    'total_xp' => $total_xp,
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
