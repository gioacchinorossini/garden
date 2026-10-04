<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $lands = get_all_lands($pdo);
    $plots = get_all_plots($pdo);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'lands' => $lands,
            'plots' => $plots
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
        $crops = isset($input['crops']) ? (array)$input['crops'] : (isset($input['allowed_seeds']) ? (array)$input['allowed_seeds'] : ['Vegetables', 'Herbs']);
        $title_type = isset($input['title_type']) ? htmlspecialchars(trim($input['title_type'])) : '';
        $title_number = isset($input['title_number']) ? htmlspecialchars(trim($input['title_number'])) : '';
        $rod_name = isset($input['rod_name']) ? htmlspecialchars(trim($input['rod_name'])) : '';
        $epeb_type = '';
        $epeb_no = '';
        $title_doc = isset($input['title_document_path']) ? htmlspecialchars(trim($input['title_document_path'])) : '';
        $oct_doc = isset($input['oct_document_path']) ? htmlspecialchars(trim($input['oct_document_path'])) : $title_doc;
        $receipt_doc = '';
        $plot_count = max(1, intval($input['plot_count'] ?? 4));
        $has_3d = isset($input['has_3d_view']) || isset($input['enable_3d_view']) ? 1 : 0;
        $polygon = isset($input['polygon']) ? $input['polygon'] : (isset($input['lot_polygon']) ? $input['lot_polygon'] : '');
        if (is_array($polygon)) {
            $polygon = json_encode($polygon);
        }

        if (empty($title) || empty($address) || !$area) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        $owner_id = $_SESSION['user_id'] ?? 2;
        $landowner_name = $_SESSION['user_name'] ?? 'John Landowner';
        $seeds_json = json_encode(array_values($crops));

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `lands` 
                (owner_id, title, title_type, title_number, rod_name, epeb_type, epeb_no, title_document_path, oct_document_path, lra_receipt_path, is_lra_verified, address, latitude, longitude, area_sqm, status, description, allowed_seeds, plot_count, landowner_name, has_3d_view, polygon)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $owner_id, $title, $title_type, $title_number, $rod_name, $epeb_type, $epeb_no,
                $title_doc, $oct_doc, $receipt_doc,
                $address, $latitude, $longitude, $area, $description, $seeds_json, $plot_count, $landowner_name, $has_3d, $polygon
            ]);
            $new_id = (int)$pdo->lastInsertId();

            // Auto-create partition plots
            $plot_area = round($area / $plot_count, 1);
            $stmtPlot = $pdo->prepare("
                INSERT INTO `plots` (land_id, plot_number, area_sqm, status, crop, crop_icon, allowed_seeds, farmer_name)
                VALUES (?, ?, ?, 'available', ?, ?, ?, '')
            ");

            for ($i = 1; $i <= $plot_count; $i++) {
                $crop = !empty($crops) ? $crops[($i - 1) % count($crops)] : 'Vegetables';
                $stmtPlot->execute([
                    $new_id,
                    'Plot A-' . $i,
                    $plot_area,
                    $crop,
                    'tomato/tomato.svg',
                    $seeds_json
                ]);
            }

            $newLand = [
                'id' => $new_id,
                'title' => $title,
                'title_type' => $title_type,
                'title_number' => $title_number,
                'rod_name' => $rod_name,
                'epeb_type' => $epeb_type,
                'epeb_no' => $epeb_no,
                'title_document_path' => $title_doc,
                'oct_document_path' => $oct_doc,
                'lra_receipt_path' => $receipt_doc,
                'is_lra_verified' => 0,
                'landowner' => $landowner_name,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'area' => $area,
                'status' => 'pending',
                'reason' => '',
                'description' => $description,
                'crops' => $crops,
                'total_plots' => $plot_count,
                'occupied_plots' => 0,
                'has_3d_view' => $has_3d,
                'polygon' => $polygon
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Land registered successfully! It is now pending administrative verification.',
                'data' => $newLand
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
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
        $crops = isset($input['crops']) ? (array)$input['crops'] : null;

        if (!$land_id || empty($title) || empty($address)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        try {
            $seeds_json = $crops !== null ? json_encode(array_values($crops)) : null;
            $stmt = $pdo->prepare("
                UPDATE `lands`
                SET `title` = ?, `address` = ?, `description` = ?,
                    `area_sqm` = COALESCE(?, `area_sqm`),
                    `latitude` = COALESCE(?, `latitude`),
                    `longitude` = COALESCE(?, `longitude`),
                    `allowed_seeds` = COALESCE(?, `allowed_seeds`)
                WHERE `id` = ?
            ");
            $stmt->execute([
                $title, $address, $description,
                $area, $latitude, $longitude, $seeds_json,
                $land_id
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Land details updated successfully!'
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
        $reason = isset($input['reason']) ? htmlspecialchars(trim($input['reason'])) : '';

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
                UPDATE `lands`
                SET `status` = ?, `rejection_reason` = ?, `reason` = ?, `is_lra_verified` = 0
                WHERE `id` = ?
            ");
            $stmt->execute([$status, $reason, $reason, $id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Land status updated!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
        $seeds_json = json_encode(array_values($crops));

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `plots` (land_id, plot_number, area_sqm, status, crop, crop_icon, allowed_seeds, farmer_name)
                VALUES (?, ?, ?, 'available', ?, ?, ?, '')
            ");
            $stmt->execute([$land_id, $plot_number, $area, $crop, $crop_icon, $seeds_json]);
            $plot_id = (int)$pdo->lastInsertId();

            $newPlot = [
                'id' => $plot_id,
                'land_id' => $land_id,
                'plot_number' => $plot_number,
                'area' => $area,
                'status' => 'available',
                'crop' => $crop,
                'crop_icon' => $crop_icon,
                'crops' => $crops
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Plot partition created successfully!',
                'data' => $newPlot
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
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

        try {
            $seeds_json = json_encode(array_values($crops));
            $stmt = $pdo->prepare("
                UPDATE `plots`
                SET `allowed_seeds` = ?,
                    `crop` = CASE WHEN ? != '' THEN ? ELSE `crop` END,
                    `crop_icon` = CASE WHEN ? != '' THEN ? ELSE `crop_icon` END
                WHERE `id` = ?
            ");
            $stmt->execute([$seeds_json, $crop, $crop, $crop_icon, $crop_icon, $plot_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Plot crop permissions updated successfully!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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

        try {
            $stmt = $pdo->prepare("DELETE FROM `plots` WHERE `id` = ?");
            $stmt->execute([$plot_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Plot partition deleted successfully!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'delete_land') {
        $land_id = isset($input['land_id']) ? intval($input['land_id']) : 0;
        if (!$land_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid land ID.']);
            exit;
        }
        try {
            $pdo->prepare("DELETE FROM `plots` WHERE `land_id` = ?")->execute([$land_id]);
            $pdo->prepare("DELETE FROM `lands` WHERE `id` = ?")->execute([$land_id]);
            echo json_encode(['status' => 'success', 'message' => 'Land deleted successfully!']);
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
