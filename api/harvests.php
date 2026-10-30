<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Make sure session harvests exists
if (!isset($_SESSION['mock_harvests'])) {
    $_SESSION['mock_harvests'] = [
        [
            'id' => 1,
            'crop_name' => 'Organic Tomatoes',
            'quantity' => 35.0,
            'unit' => 'kg',
            'harvest_date' => '2026-08-04',
            'plot_num' => 'Plot B-1',
            'notes' => 'First major yield! Plentiful tomatoes. Very sweet skin, harvested early morning.'
        ],
        [
            'id' => 2,
            'crop_name' => 'Romaine Lettuce',
            'quantity' => 22.5,
            'unit' => 'kg',
            'harvest_date' => '2026-08-02',
            'plot_num' => 'Plot B-2',
            'notes' => 'Crisp leafy greens, washed and trimmed ready for dispatch.'
        ]
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'harvests' => $_SESSION['mock_harvests']
        ]
    ]);
    exit;
}

if ($method === 'POST') {
    // Read input either from json or post variables
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $crop = isset($input['crop_name']) ? htmlspecialchars(trim($input['crop_name'])) : '';
    $qty = isset($input['quantity']) ? floatval($input['quantity']) : 0;
    $unit = isset($input['unit']) ? htmlspecialchars(trim($input['unit'])) : 'kg';
    $date = isset($input['harvest_date']) ? htmlspecialchars($input['harvest_date']) : date('Y-m-d');
    $notes = isset($input['notes']) ? htmlspecialchars(trim($input['notes'])) : '';
    $plot = isset($input['plot_num']) ? htmlspecialchars($input['plot_num']) : '';

    if (empty($crop) || $qty <= 0 || empty($plot)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Please provide valid crop name, quantity, and source plot.'
        ]);
        exit;
    }

    $newHarvest = [
        'id' => count($_SESSION['mock_harvests']) + 1,
        'crop_name' => $crop,
        'quantity' => $qty,
        'unit' => $unit,
        'harvest_date' => $date,
        'plot_num' => $plot,
        'notes' => $notes
    ];

    $_SESSION['mock_harvests'][] = $newHarvest;

    echo json_encode([
        'status' => 'success',
        'message' => 'Harvest logged successfully!',
        'data' => $newHarvest
    ]);
    exit;
}
