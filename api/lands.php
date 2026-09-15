<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure mock lands and plots exist
if (!isset($_SESSION['mock_lands'])) {
    $_SESSION['mock_lands'] = [
        [
            'id' => 1,
            'title' => 'Sunnyvale Gardening Lot',
            'title_type' => 'TCT',
            'title_number' => 'TCT No. 004-2022019482',
            'rod_name' => 'Quezon City',
            'epeb_type' => 'CCV',
            'epeb_no' => '2023004819',
            'title_document_path' => 'assets/images/sample_title_cert.svg',
            'lra_receipt_path' => 'assets/images/sample_lra_receipt.svg',
            'is_lra_verified' => 1,
            'landowner' => 'John Landowner',
            'address' => '124 Green Ave, Sunnyvale',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'area' => 250.00,
            'status' => 'approved',
            'reason' => '',
            'description' => 'A spacious lot with fertile soil and partial shade, perfect for root vegetables like carrots and potatoes.',
            'crops' => ['Root Vegetables', 'Tuber Crops']
        ],
        [
            'id' => 2,
            'title' => 'Downtown Rooftop Garden',
            'title_type' => 'TCT',
            'title_number' => 'TCT No. 002-2021008172',
            'rod_name' => 'City of Manila',
            'epeb_type' => 'CCV',
            'epeb_no' => '2022001923',
            'title_document_path' => 'assets/images/sample_title_cert.svg',
            'lra_receipt_path' => 'assets/images/sample_lra_receipt.svg',
            'is_lra_verified' => 1,
            'landowner' => 'John Landowner',
            'address' => '45 Main St, Business District',
            'latitude' => 14.6010,
            'longitude' => 120.9890,
            'area' => 85.50,
            'status' => 'approved',
            'reason' => '',
            'description' => 'An elevated deck prepared with planters and drip irrigation, ideal for leafy greens and culinary herbs.',
            'crops' => ['Leafy Greens', 'Herbs']
        ],
        [
            'id' => 3,
            'title' => 'Riverdale Acres',
            'title_type' => 'OCT',
            'title_number' => 'OCT No. 008-1998004112',
            'rod_name' => 'Province of Rizal',
            'epeb_type' => 'CCV',
            'epeb_no' => '2023008472',
            'title_document_path' => 'assets/images/sample_title_cert.svg',
            'lra_receipt_path' => 'assets/images/sample_lra_receipt.svg',
            'is_lra_verified' => 1,
            'landowner' => 'Robert Johnson',
            'address' => 'Riverside Dr, Block B',
            'latitude' => 14.5950,
            'longitude' => 120.9780,
            'area' => 500.00,
            'status' => 'approved',
            'reason' => '',
            'description' => 'Large idle pasture near the riverbed. High soil quality, direct sunlight access. Excellent for fruits, tomatoes and legumes.',
            'crops' => ['Fruits', 'Legumes', 'Leafy Greens']
        ],
        [
            'id' => 4,
            'title' => 'Eastside Clay Meadows',
            'title_type' => 'TCT',
            'title_number' => 'TCT No. 010-2023001893',
            'rod_name' => 'Province of Laguna, Calamba Branch',
            'epeb_type' => 'CCV',
            'epeb_no' => '2024009182',
            'title_document_path' => 'assets/images/sample_title_cert.svg',
            'lra_receipt_path' => 'assets/images/sample_lra_receipt.svg',
            'is_lra_verified' => 1,
            'landowner' => 'Sarah Connor',
            'address' => '789 East Blvd, Clay District',
            'latitude' => 14.6120,
            'longitude' => 121.0020,
            'area' => 180.00,
            'status' => 'approved',
            'reason' => '',
            'description' => 'Rich heavy clay loam soil retaining moisture well. Best suited for cabbage, broccoli, and tuber crops.',
            'crops' => ['Cruciferous', 'Tuber Crops']
        ]
    ];
}

if (!isset($_SESSION['mock_plots'])) {
    $_SESSION['mock_plots'] = [
        ['id' => 1, 'land_id' => 2, 'plot_number' => 'Plot A-1', 'area' => 20.0, 'status' => 'occupied', 'crop' => 'Tomato', 'crop_icon' => 'tomato/tomato.svg', 'crops' => ['Tomato', 'Herbs']],
        ['id' => 2, 'land_id' => 2, 'plot_number' => 'Plot A-2', 'area' => 20.0, 'status' => 'occupied', 'crop' => 'Lettuce', 'crop_icon' => 'romaine/romaine.svg', 'crops' => ['Lettuce', 'Spinach']],
        ['id' => 3, 'land_id' => 2, 'plot_number' => 'Plot B-1', 'area' => 22.0, 'status' => 'available', 'crop' => 'Herbs', 'crop_icon' => 'basil/basil.svg', 'crops' => ['Herbs', 'Tomato']],
        ['id' => 4, 'land_id' => 2, 'plot_number' => 'Plot B-2', 'area' => 23.5, 'status' => 'available', 'crop' => 'Carrot', 'crop_icon' => 'carrot/carrot.svg', 'crops' => ['Carrot', 'Potato']],
        ['id' => 5, 'land_id' => 3, 'plot_number' => 'Plot R-1', 'area' => 100.0, 'status' => 'available', 'crop' => 'Tomato', 'crop_icon' => 'tomato/tomato.svg', 'crops' => ['Tomato', 'Pepper']],
        ['id' => 6, 'land_id' => 4, 'plot_number' => 'Plot E-1', 'area' => 90.0, 'status' => 'available', 'crop' => 'Potato', 'crop_icon' => 'russet-potato/russet-potato.svg', 'crops' => ['Potato', 'Carrot']],
        ['id' => 7, 'land_id' => 4, 'plot_number' => 'Plot E-2', 'area' => 90.0, 'status' => 'available', 'crop' => 'Beans', 'crop_icon' => 'broad-bean/broad-bean.svg', 'crops' => ['Beans', 'Squash']]
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'lands' => $_SESSION['mock_lands'],
            'plots' => $_SESSION['mock_plots']
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

    if ($action === 'create_land') {
        $title = isset($input['title']) ? htmlspecialchars(trim($input['title'])) : '';
        $address = isset($input['address']) ? htmlspecialchars(trim($input['address'])) : '';
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : 14.5995;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : 120.9842;
        $area = isset($input['area']) ? floatval($input['area']) : 0.0;
        $description = isset($input['description']) ? htmlspecialchars(trim($input['description'])) : '';
        $crops = isset($input['crops']) ? $input['crops'] : ['General Gardening'];
        $title_type = isset($input['title_type']) ? htmlspecialchars(trim($input['title_type'])) : 'TCT';
        $title_number = isset($input['title_number']) ? htmlspecialchars(trim($input['title_number'])) : '';
        $rod_name = isset($input['rod_name']) ? htmlspecialchars(trim($input['rod_name'])) : '';
        $epeb_type = isset($input['epeb_type']) ? htmlspecialchars(trim($input['epeb_type'])) : 'CCV';
        $epeb_no = isset($input['epeb_no']) ? htmlspecialchars(trim($input['epeb_no'])) : '';
        $title_doc = isset($input['title_document_path']) ? htmlspecialchars(trim($input['title_document_path'])) : '';
        $receipt_doc = isset($input['lra_receipt_path']) ? htmlspecialchars(trim($input['lra_receipt_path'])) : '';

        if (empty($title) || empty($address) || !$area) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $newLand = [
            'id' => count($_SESSION['mock_lands']) + 1,
            'title' => $title,
            'title_type' => $title_type,
            'title_number' => $title_number,
            'rod_name' => $rod_name,
            'epeb_type' => $epeb_type,
            'epeb_no' => $epeb_no,
            'title_document_path' => $title_doc,
            'lra_receipt_path' => $receipt_doc,
            'is_lra_verified' => 0,
            'landowner' => $_SESSION['user_name'] ?? 'John Landowner',
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'area' => $area,
            'status' => 'pending',
            'reason' => '',
            'description' => $description,
            'crops' => $crops
        ];

        $_SESSION['mock_lands'][] = $newLand;

        echo json_encode([
            'status' => 'success',
            'message' => 'Land registered successfully! It is now pending administrative verification.',
            'data' => $newLand
        ]);
        exit;
    }

    if ($action === 'update_land') {
        $land_id = isset($input['land_id']) ? intval($input['land_id']) : 0;
        $title = isset($input['title']) ? htmlspecialchars(trim($input['title'])) : '';
        $address = isset($input['address']) ? htmlspecialchars(trim($input['address'])) : '';
        $description = isset($input['description']) ? htmlspecialchars(trim($input['description'])) : '';
        $area = isset($input['area']) ? floatval($input['area']) : null;
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;
        $crops = isset($input['crops']) ? $input['crops'] : null;

        if (!$land_id || empty($title) || empty($address)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $found = false;
        foreach ($_SESSION['mock_lands'] as &$land) {
            if ($land['id'] === $land_id) {
                $land['title'] = $title;
                $land['address'] = $address;
                $land['description'] = $description;
                if ($area !== null && $area > 0) $land['area'] = $area;
                if ($latitude !== null && $latitude != 0) $land['latitude'] = $latitude;
                if ($longitude !== null && $longitude != 0) $land['longitude'] = $longitude;
                if ($crops !== null) $land['crops'] = $crops;
                $found = true;
                break;
            }
        }

        if ($found) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Land details updated successfully!'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Land not found.'
            ]);
        }
        exit;
    }

    if ($action === 'update_status') {
        $id = isset($input['id']) ? intval($input['id']) : 0;
        $status = isset($input['status']) ? htmlspecialchars(trim($input['status'])) : '';
        $reason = isset($input['reason']) ? htmlspecialchars(trim($input['reason'])) : '';

        if (!$id || !in_array($status, ['approved', 'rejected'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action parameters.'
            ]);
            exit;
        }

        $found = false;
        foreach ($_SESSION['mock_lands'] as &$land) {
            if ($land['id'] === $id) {
                $land['status'] = $status;
                $land['reason'] = ($status === 'rejected') ? $reason : '';
                if ($status === 'approved') {
                    $land['is_lra_verified'] = !empty($land['epeb_no']) || !empty($land['title_number']) ? 1 : 0;
                } else {
                    $land['is_lra_verified'] = 0;
                }
                $found = true;
                break;
            }
        }

        if ($found) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Land verification status updated!'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Land submission not found.'
            ]);
        }
        exit;
    }

    if ($action === 'create_plot') {
        $land_id = isset($input['land_id']) ? intval($input['land_id']) : 0;
        $plot_number = isset($input['plot_number']) ? htmlspecialchars(trim($input['plot_number'])) : '';
        $area = isset($input['area']) ? floatval($input['area']) : 0.0;

        if (!$land_id || empty($plot_number) || !$area) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $crops = isset($input['crops']) ? (array)$input['crops'] : [];
        $crop = isset($input['crop']) ? htmlspecialchars(trim($input['crop'])) : (isset($crops[0]) ? htmlspecialchars(trim($crops[0])) : 'Tomato');
        $crop_icon = isset($input['crop_icon']) ? htmlspecialchars(trim($input['crop_icon'])) : 'tomato/tomato.svg';

        $newPlot = [
            'id' => count($_SESSION['mock_plots']) + 1,
            'land_id' => $land_id,
            'plot_number' => $plot_number,
            'area' => $area,
            'status' => 'available',
            'crop' => $crop,
            'crop_icon' => $crop_icon,
            'crops' => $crops
        ];

        $_SESSION['mock_plots'][] = $newPlot;

        echo json_encode([
            'status' => 'success',
            'message' => 'Plot partition created successfully!',
            'data' => $newPlot
        ]);
        exit;
    }

    if ($action === 'update_plot_crops') {
        $plot_id = isset($input['plot_id']) ? intval($input['plot_id']) : 0;
        $crops = isset($input['crops']) ? (array)$input['crops'] : [];
        $crop = isset($input['crop']) ? htmlspecialchars(trim($input['crop'])) : (isset($crops[0]) ? htmlspecialchars(trim($crops[0])) : '');
        $crop_icon = isset($input['crop_icon']) ? htmlspecialchars(trim($input['crop_icon'])) : '';

        if (!$plot_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Plot ID is required.']);
            exit;
        }

        $found = false;
        foreach ($_SESSION['mock_plots'] as &$plot) {
            if ($plot['id'] === $plot_id) {
                $plot['crops'] = $crops;
                if ($crop) $plot['crop'] = $crop;
                if ($crop_icon) $plot['crop_icon'] = $crop_icon;
                $found = true;
                break;
            }
        }

        if ($found) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Plot crop permissions updated successfully!'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Plot partition not found.']);
        }
        exit;
    }

    if ($action === 'delete_plot') {
        $plot_id = isset($input['plot_id']) ? intval($input['plot_id']) : 0;

        if (!$plot_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid plot ID.'
            ]);
            exit;
        }

        $found = false;
        foreach ($_SESSION['mock_plots'] as $k => $plot) {
            if ($plot['id'] === $plot_id) {
                unset($_SESSION['mock_plots'][$k]);
                $found = true;
                break;
            }
        }

        if ($found) {
            $_SESSION['mock_plots'] = array_values($_SESSION['mock_plots']);
            echo json_encode([
                'status' => 'success',
                'message' => 'Plot partition deleted successfully!'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Plot partition not found.'
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
