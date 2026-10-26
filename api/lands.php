<?php
header("Content-Type: application/json; charset=UTF-8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $properties = get_all_properties($pdo);
    $stalls = get_all_stalls($pdo);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'lands' => $properties,
            'properties' => $properties,
            'plots' => $stalls,
            'stalls' => $stalls
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

    if ($action === 'create_land' || $action === 'create_property') {
        $title = isset($input['title']) ? htmlspecialchars(trim($input['title'])) : '';
        $address = isset($input['address']) ? htmlspecialchars(trim($input['address'])) : '';
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : 14.5492;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : 120.9856;
        $area = isset($input['area']) ? floatval($input['area']) : (isset($input['total_area']) ? floatval($input['total_area']) : 0.0);
        $description = isset($input['description']) ? htmlspecialchars(trim($input['description'])) : '';
        $amenities = isset($input['amenities']) ? (array)$input['amenities'] : (isset($input['crops']) ? (array)$input['crops'] : ['Electricity', 'Water']);
        $permit_no = isset($input['permit_no']) ? htmlspecialchars(trim($input['permit_no'])) : (isset($input['title_number']) ? htmlspecialchars(trim($input['title_number'])) : '');
        $space_type = isset($input['space_type']) ? htmlspecialchars(trim($input['space_type'])) : (isset($input['property_type']) ? htmlspecialchars(trim($input['property_type'])) : 'food_park');
        $stall_count = max(1, intval($input['plot_count'] ?? ($input['stall_count'] ?? 4)));
        $rental_rate = isset($input['rental_rate']) ? floatval($input['rental_rate']) : 300.00;
        $rate_period = isset($input['rate_period']) ? htmlspecialchars(trim($input['rate_period'])) : 'daily';

        if (empty($title) || empty($address) || !$area) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields (title, address, and total area).'
            ]);
            exit;
        }

        $owner_id = $_SESSION['user_id'] ?? 2;
        $amenities_str = is_array($amenities) ? implode(', ', $amenities) : $amenities;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `properties` 
                (owner_id, title, space_type, permit_no, address, latitude, longitude, total_area, amenities, description, status)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $owner_id, $title, $space_type, $permit_no,
                $address, $latitude, $longitude, $area,
                $amenities_str, $description
            ]);
            $new_id = (int)$pdo->lastInsertId();

            // Auto-generate stalls
            $stall_area = round($area / $stall_count, 1);
            $stmtStall = $pdo->prepare("
                INSERT INTO `stalls` (property_id, stall_number, area, rental_rate, rate_period, status)
                VALUES (?, ?, ?, ?, ?, 'available')
            ");

            for ($i = 1; $i <= $stall_count; $i++) {
                $stmtStall->execute([
                    $new_id,
                    'Stall ' . chr(64 + ceil($i / 10)) . '-' . sprintf('%02d', $i),
                    $stall_area,
                    $rental_rate,
                    $rate_period
                ]);
            }

            $newProperty = [
                'id' => $new_id,
                'title' => $title,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'area' => $area,
                'total_area' => $area,
                'space_type' => $space_type,
                'property_type' => $space_type,
                'permit_no' => $permit_no,
                'status' => 'pending',
                'description' => $description,
                'amenities' => $amenities,
                'crops' => $amenities,
                'total_plots' => $stall_count,
                'occupied_plots' => 0
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Property registered successfully! Stalls have been initialized and sent for admin verification.',
                'data' => $newProperty
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

    if ($action === 'update_land' || $action === 'update_property') {
        $property_id = isset($input['land_id']) ? intval($input['land_id']) : (isset($input['property_id']) ? intval($input['property_id']) : 0);
        $title = isset($input['title']) ? htmlspecialchars(trim($input['title'])) : '';
        $address = isset($input['address']) ? htmlspecialchars(trim($input['address'])) : '';
        $description = isset($input['description']) ? htmlspecialchars(trim($input['description'])) : '';
        $area = isset($input['area']) ? floatval($input['area']) : (isset($input['total_area']) ? floatval($input['total_area']) : null);
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;
        $amenities = isset($input['amenities']) ? (array)$input['amenities'] : (isset($input['crops']) ? (array)$input['crops'] : null);

        if (!$property_id || empty($title) || empty($address)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        try {
            $amenities_str = $amenities !== null ? (is_array($amenities) ? implode(', ', $amenities) : $amenities) : null;
            $stmt = $pdo->prepare("
                UPDATE `properties`
                SET `title` = ?, `address` = ?, `description` = ?,
                    `total_area` = COALESCE(?, `total_area`),
                    `latitude` = COALESCE(?, `latitude`),
                    `longitude` = COALESCE(?, `longitude`),
                    `amenities` = COALESCE(?, `amenities`)
                WHERE `id` = ?
            ");
            $stmt->execute([
                $title, $address, $description,
                $area, $latitude, $longitude, $amenities_str,
                $property_id
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Property details updated successfully!'
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
                UPDATE `properties`
                SET `status` = ?, `rejection_reason` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$status, $reason, $id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Property status updated!'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'create_stall') {
        $property_id = isset($input['property_id']) ? intval($input['property_id']) : 0;
        $stall_number = isset($input['stall_number']) ? htmlspecialchars(trim($input['stall_number'])) : '';
        $area = isset($input['area']) ? floatval($input['area']) : 10.0;
        $rental_rate = isset($input['rental_rate']) ? floatval($input['rental_rate']) : 300.00;
        $rate_period = isset($input['rate_period']) ? htmlspecialchars(trim($input['rate_period'])) : 'daily';

        if (!$property_id || empty($stall_number)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Property ID and Stall Number are required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO `stalls` (property_id, stall_number, area, rental_rate, rate_period, status)
                VALUES (?, ?, ?, ?, ?, 'available')
            ");
            $stmt->execute([$property_id, $stall_number, $area, $rental_rate, $rate_period]);
            $stall_id = (int)$pdo->lastInsertId();

            echo json_encode([
                'status' => 'success',
                'message' => 'Stall added successfully!',
                'data' => ['id' => $stall_id, 'stall_number' => $stall_number]
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
